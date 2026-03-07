<?php

declare(strict_types=1);

namespace App\Backend\Db;

use Magepattern\Component\Database\QueryBuilder;
use Magepattern\Component\Tool\DateTool;

class ProductDb extends BaseDb
{
    /**
     * Récupère la liste des produits avec recherche et pagination.
     * Intègre la catégorie par défaut pour affichage et construction de l'URL canonique.
     */
    public function fetchAllProducts(int $page = 1, int $limit = 25, array $search = [], int $idLang = 1): array|false
    {
        $qb = new QueryBuilder();

        $qb->select([
            'p.id_product',
            'p.reference_p',
            'p.ean_p',
            'p.price_p',
            'p.availability_p',
            'c.name_p',
            'c.url_p',
            'c.published_p',
            'cat_c.name_cat AS default_category_name', // <- Retour à la propreté absolue : renverra NULL si pas de catégorie
            'cat_c.url_cat AS default_category_url',
            'cat_rel.id_cat AS default_category_id',
            'IFNULL(pi.default_img, 0) as default_img',
            'p.date_register'
        ])
            ->from('mc_catalog_product', 'p')
            ->join('mc_catalog_product_content', 'c', 'p.id_product = c.id_product')
            ->leftJoin('mc_catalog_product_img', 'pi', 'p.id_product = pi.id_product AND pi.default_img = 1')
            ->leftJoin('mc_catalog', 'cat_rel', 'p.id_product = cat_rel.id_product AND cat_rel.default_c = 1')
            // La jointure utilise l'ID langue de la table c, ce qui évite le bug de paramètre PDO
            ->leftJoin('mc_catalog_cat_content', 'cat_c', 'cat_rel.id_cat = cat_c.id_cat AND cat_c.id_lang = c.id_lang')
            ->where('c.id_lang = :id_lang', ['id_lang' => $idLang]);

        if (!empty($search)) {
            $qb->orderBy('p.id_product', 'DESC');
            $nbc = 1;
            foreach ($search as $key => $q) {
                if ($q !== '') {
                    $paramName = 'p' . $nbc;
                    $binds = [];
                    switch ($key) {
                        case 'id_product':
                        case 'published_p':
                        case 'price_p':
                            $binds[$paramName] = $q;
                            $prefix = ($key === 'published_p') ? 'c.' : 'p.';
                            $qb->where("{$prefix}{$key} = :{$paramName}", $binds);
                            break;
                        case 'reference_p':
                        case 'ean_p':
                        case 'availability_p':
                            $binds[$paramName] = '%' . $q . '%';
                            $qb->where("p.{$key} LIKE :{$paramName}", $binds);
                            break;
                        case 'name_p':
                            $binds[$paramName] = '%' . $q . '%';
                            $qb->where("c.{$key} LIKE :{$paramName}", $binds);
                            break;
                        case 'default_category_name':
                            $binds[$paramName] = '%' . $q . '%';
                            $qb->where("cat_c.name_cat LIKE :{$paramName}", $binds);
                            break;
                        case 'date_register':
                            $formattedDate = class_exists(DateTool::class) ? DateTool::toSql((string)$q) : $q;
                            $binds[$paramName] = '%' . $formattedDate . '%';
                            $qb->where("p.{$key} LIKE :{$paramName}", $binds);
                            break;
                    }
                    $nbc++;
                }
            }
        } else {
            $qb->orderBy('p.id_product', 'DESC');
        }

        return $this->executePaginatedQuery($qb, $page, $limit);
    }

    /**
     * Récupère un produit, tous ses contenus multilingues, ET ses catégories associées.
     */
    public function fetchProductById(int $id): array|false
    {
        // 1. Structure du produit
        $qb = new QueryBuilder();
        $qb->select('*')->from('mc_catalog_product')->where('id_product = :id', ['id' => $id]);
        $product = $this->executeRow($qb);

        if (!$product) return false;

        // 2. Traductions
        $qbContent = new QueryBuilder();
        $qbContent->select('*')->from('mc_catalog_product_content')->where('id_product = :id', ['id' => $id]);
        $contents = $this->executeAll($qbContent);

        $product['content'] = [];
        if ($contents) {
            foreach ($contents as $c) {
                $product['content'][$c['id_lang']] = $c;
            }
        }

        // 3. Catégories associées (Table de liaison)
        $qbCategories = new QueryBuilder();
        $qbCategories->select(['id_cat', 'default_c'])
            ->from('mc_catalog')
            ->where('id_product = :id', ['id' => $id]);
        $categoriesRel = $this->executeAll($qbCategories);

        $product['categories'] = [];
        $product['default_category_id'] = 0;

        if ($categoriesRel) {
            foreach ($categoriesRel as $rel) {
                $product['categories'][] = $rel['id_cat'];
                if ($rel['default_c'] == 1) {
                    $product['default_category_id'] = $rel['id_cat'];
                }
            }
        }

        return $product;
    }

    /**
     * Supprime un ou plusieurs produits (Nettoie aussi les liaisons grâce au ON DELETE CASCADE de la BDD)
     */
    public function deleteProducts(array $ids): bool
    {
        if (empty($ids)) return false;

        $qb = new QueryBuilder();
        $qb->delete('mc_catalog_product')->whereIn('id_product', $ids);
        $res1 = $this->executeDelete($qb);

        $qbContent = new QueryBuilder();
        $qbContent->delete('mc_catalog_product_content')->whereIn('id_product', $ids);
        $res2 = $this->executeDelete($qbContent);

        return $res1 && $res2;
    }

    /**
     * Met à jour la structure (Prix, Ref, Dimensions...)
     */
    public function updateProductStructure(int $idProduct, array $data): bool
    {
        $qb = new QueryBuilder();
        $qb->update('mc_catalog_product', $data)
            ->where('id_product = :id', ['id' => $idProduct]);

        return $this->executeUpdate($qb);
    }

    /**
     * Sauvegarde le contenu multilingue
     */
    public function saveProductContent(int $idProduct, int $idLang, array $data): bool
    {
        $qbCheck = new QueryBuilder();
        $qbCheck->select(['id_product'])
            ->from('mc_catalog_product_content')
            ->where('id_product = :p AND id_lang = :l', ['p' => $idProduct, 'l' => $idLang]);

        $exists = $this->executeRow($qbCheck);

        $qb = new QueryBuilder();
        if ($exists) {
            $qb->update('mc_catalog_product_content', $data)
                ->where('id_product = :p AND id_lang = :l', ['p' => $idProduct, 'l' => $idLang]);
            return $this->executeUpdate($qb);
        } else {
            $data['id_product'] = $idProduct;
            $data['id_lang']    = $idLang;
            $qb->insert('mc_catalog_product_content', $data);
            return $this->executeInsert($qb);
        }
    }

    /**
     * Insère un nouveau produit
     */
    public function insertProductStructure(array $data): int|false
    {
        $qb = new QueryBuilder();
        $qb->insert('mc_catalog_product', $data);

        if ($this->executeInsert($qb)) {
            return $this->getLastInsertId();
        }
        return false;
    }

    /**
     * Met à jour les liaisons de catégories pour un produit
     */
    public function saveProductCategories(int $idProduct, array $categoryIds, int $defaultCategoryId = 0): bool
    {
        // 1. On vide les anciennes liaisons pour repartir au propre
        $qbDelete = new QueryBuilder();
        $qbDelete->delete('mc_catalog')->where('id_product = :id', ['id' => $idProduct]);
        $this->executeDelete($qbDelete);

        if (empty($categoryIds)) {
            return true; // Le produit n'est plus rattaché à aucune catégorie
        }

        // 2. Si la catégorie par défaut n'est pas dans la liste des cochées, on force la première cochée
        if (!in_array($defaultCategoryId, $categoryIds) && count($categoryIds) > 0) {
            $defaultCategoryId = $categoryIds[0];
        }

        $success = true;

        // 3. On insère les nouvelles liaisons
        foreach ($categoryIds as $idCat) {
            $isDefault = ($idCat == $defaultCategoryId) ? 1 : 0;

            $qbInsert = new QueryBuilder();
            $qbInsert->insert('mc_catalog', [
                'id_product' => $idProduct,
                'id_cat'     => $idCat,
                'default_c'  => $isDefault,
                'order_p'    => 0 // L'ordre dans la catégorie pourrait être géré plus tard
            ]);

            if (!$this->executeInsert($qbInsert)) {
                $success = false;
            }
        }

        return $success;
    }

    // ==========================================
    // GESTION DES IMAGES (LA GALERIE)
    // ==========================================

    public function insertImage(int $productId, string $filename): bool
    {
        $qbCount = new QueryBuilder();
        $qbCount->select(['COUNT(id_img) as total'])
            ->from('mc_catalog_product_img')
            ->where('id_product = :id', ['id' => $productId]);

        $countResult = $this->executeRow($qbCount);
        $order = (int)($countResult['total'] ?? 0);
        $isDefault = ($order === 0) ? 1 : 0;

        $qbInsert = new QueryBuilder();
        $qbInsert->insert('mc_catalog_product_img', [
            'id_product'  => $productId,
            'name_img'    => $filename,
            'order_img'   => $order,
            'default_img' => $isDefault
        ]);

        return $this->executeInsert($qbInsert);
    }

    public function reorderImages(array $imageIds): bool
    {
        $success = true;
        foreach ($imageIds as $index => $id) {
            $qb = new QueryBuilder();
            $qb->update('mc_catalog_product_img', ['order_img' => $index])
                ->where('id_img = :id_img', ['id_img' => (int)$id]);

            if (!$this->executeUpdate($qb)) {
                $success = false;
            }
        }
        return $success;
    }

    public function setDefaultImage(int $productId, int $imageId): bool
    {
        $qbReset = new QueryBuilder();
        $qbReset->update('mc_catalog_product_img', ['default_img' => 0])
            ->where('id_product = :id_product', ['id_product' => $productId]);
        $this->executeUpdate($qbReset);

        $qbSet = new QueryBuilder();
        $qbSet->update('mc_catalog_product_img', ['default_img' => 1])
            ->where('id_img = :id_img', ['id_img' => $imageId]);

        return $this->executeUpdate($qbSet);
    }

    public function getLastImageId(int $productId): int
    {
        $qb = new QueryBuilder();
        $qb->select(['MAX(id_img) as max_id'])
            ->from('mc_catalog_product_img')
            ->where('id_product = :id', ['id' => $productId]);

        $result = $this->executeRow($qb);
        return (int)($result['max_id'] ?? 0);
    }

    public function fetchImagesByProduct(int $productId): array
    {
        $qb = new QueryBuilder();
        $qb->select(['*'])
            ->from('mc_catalog_product_img')
            ->where('id_product = :id', ['id' => $productId])
            ->orderBy('order_img', 'ASC');

        $result = $this->executeAll($qb);
        return $result ?: [];
    }

    public function deleteImage(int $imageId): array|false
    {
        $qbSelect = new QueryBuilder();
        $qbSelect->select(['*'])->from('mc_catalog_product_img')->where('id_img = :id', ['id' => $imageId]);
        $img = $this->executeRow($qbSelect);

        if ($img) {
            $qbDel = new QueryBuilder();
            $qbDel->delete('mc_catalog_product_img')->where('id_img = :id', ['id' => $imageId]);
            if ($this->executeDelete($qbDel)) {
                return $img;
            }
        }
        return false;
    }

    public function fetchImageMeta(int $idImg): array
    {
        $qb = new QueryBuilder();
        $qb->select('*')->from('mc_catalog_product_img_content')->where('id_img = :id', ['id' => $idImg]);
        $results = $this->executeAll($qb);

        $meta = [];
        if ($results) {
            foreach ($results as $row) {
                $meta[$row['id_lang']] = $row;
            }
        }
        return $meta;
    }

    public function saveImageMeta(int $idImg, int $idLang, array $data): bool
    {
        $table = 'mc_catalog_product_img_content';

        $qbCheck = new QueryBuilder();
        $qbCheck->select(['id_img'])->from($table)
            ->where('id_img = :img AND id_lang = :lang', ['img' => $idImg, 'lang' => $idLang]);

        $exists = $this->executeRow($qbCheck);
        $qb = new QueryBuilder();

        if ($exists) {
            $qb->update($table, $data)
                ->where('id_img = :img AND id_lang = :lang', ['img' => $idImg, 'lang' => $idLang]);
            return $this->executeUpdate($qb);
        } else {
            $data['id_img']  = $idImg;
            $data['id_lang'] = $idLang;
            $qb->insert($table, $data);
            return $this->executeInsert($qb);
        }
    }
    /**
     * Récupère tous les produits associés à une catégorie spécifique
     */
    public function fetchProductsByCategory(int $idCat, int $idLang): array|false
    {
        $qb = new QueryBuilder();
        $qb->select([
            'p.id_product',
            'p.reference_p',
            'c.name_p',
            'p.price_p',
            'c.published_p',
            'cat_rel.order_p'
        ])
            ->from('mc_catalog_product', 'p')
            ->join('mc_catalog', 'cat_rel', 'p.id_product = cat_rel.id_product')
            ->join('mc_catalog_product_content', 'c', 'p.id_product = c.id_product')
            ->where('cat_rel.id_cat = :id_cat', ['id_cat' => $idCat])
            ->where('c.id_lang = :id_lang', ['id_lang' => $idLang])
            // Tri naturel par l'ordre défini dans la table de liaison
            ->orderBy('cat_rel.order_p', 'ASC');

        return $this->executeAll($qb);
    }

    /**
     * Supprime uniquement la liaison (Unlink) entre un produit et une catégorie
     */
    public function unlinkProductsFromCategory(int $idCat, array $productIds): bool
    {
        if (empty($productIds)) return false;

        $qb = new QueryBuilder();
        $qb->delete('mc_catalog')
            ->where('id_cat = :id_cat', ['id_cat' => $idCat])
            ->whereIn('id_product', $productIds);

        return $this->executeDelete($qb);
    }

    /**
     * Met à jour l'ordre de tri des produits au sein d'une catégorie
     */
    public function reorderProductsInCategory(int $idCat, array $productIds): bool
    {
        $success = true;
        foreach ($productIds as $index => $idProduct) {
            $qb = new QueryBuilder();
            $qb->update('mc_catalog', ['order_p' => $index])
                ->where('id_cat = :id_cat AND id_product = :id_prod', [
                    'id_cat'  => $idCat,
                    'id_prod' => (int)$idProduct
                ]);

            if (!$this->executeUpdate($qb)) {
                $success = false;
            }
        }
        return $success;
    }

    /**
     * @return int
     */
    public function countProducts(): int
    {
        $qb = new QueryBuilder();
        $qb->select('COUNT(*) as total')->from('mc_catalog_product');

        $result = $this->executeRow($qb);
        return $result ? (int)$result['total'] : 0;
    }
}