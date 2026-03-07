<?php

declare(strict_types=1);

namespace App\Backend\Db;

use Magepattern\Component\Database\QueryBuilder;
use Magepattern\Component\Database\Layer;
use Magepattern\Component\Tool\DateTool;

class CategoryDb extends BaseDb
{
    /**
     * Récupère la liste des catégories avec gestion complète de la recherche et pagination.
     *
     * @param int $page Le numéro de la page en cours
     * @param int $limit Le nombre de résultats par page
     * @param array $search Le tableau des filtres de recherche
     * @param int $idLang id de la langue
     * @return array|false Retourne ['data' => [...], 'meta' => [...]] ou false en cas d'erreur
     */
    public function fetchAllCategories(int $page = 1, int $limit = 25, array $search = [], int $idLang = 1): array|false
    {
        $qb = new QueryBuilder();

        // 1. LE CŒUR DE LA REQUÊTE
        $qb->select([
            'p.id_cat',
            'c.name_cat',
            'c.published_cat',
            'ca.name_cat AS parent_cat',
            'IFNULL(pi.default_img, 0) as default_img',
            'c.content_cat',
            'c.seo_title_cat',
            'c.seo_desc_cat',
            'p.menu_cat',
            'p.date_register'
        ])
            ->from('mc_catalog_cat', 'p')
            ->join('mc_catalog_cat_content', 'c', 'p.id_cat = c.id_cat')
            ->leftJoin('mc_catalog_cat_img', 'pi', 'p.id_cat = pi.id_cat AND pi.default_img = 1')
            ->join('mc_lang', 'lang', 'c.id_lang = lang.id_lang')
            ->leftJoin('mc_catalog_cat', 'pa', 'p.id_parent = pa.id_cat')
            ->leftJoin('mc_catalog_cat_content', 'ca', 'pa.id_cat = ca.id_cat AND ca.id_lang = :id_lang')
            ->where('c.id_lang = :id_lang', ['id_lang' => $idLang]);

        // 2. GESTION DE LA RECHERCHE
        if (!empty($search)) {
            $qb->orderBy('p.id_cat', 'DESC');

            $nbc = 1;
            foreach ($search as $key => $q) {
                if ($q !== '') {
                    $paramName = 'p' . $nbc;
                    $binds = [];
                    switch ($key) {
                        case 'id_cat':
                        case 'menu_cat':
                            $binds[$paramName] = $q;
                            $qb->where("p.{$key} = :{$paramName}", $binds);
                            break;
                        case 'published_cat':
                            $binds[$paramName] = $q;
                            $qb->where("c.{$key} = :{$paramName}", $binds);
                            break;
                        case 'name_cat':
                            $binds[$paramName] = '%' . $q . '%';
                            $qb->where("c.{$key} LIKE :{$paramName}", $binds);
                            break;
                        case 'parent_cat':
                            $binds[$paramName] = '%' . $q . '%';
                            $qb->where("ca.name_cat LIKE :{$paramName}", $binds);
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
            // 3. COMPORTEMENT PAR DÉFAUT
            $qb->orderBy('p.id_parent', 'ASC')
                ->orderBy('p.order_cat', 'ASC');
        }

        return $this->executePaginatedQuery($qb, $page, $limit);
    }

    /**
     * Récupère toutes les sous-catégories d'une catégorie parente
     */
    public function fetchCategoriesByParent(int $parentId, int $idLang): array|false
    {
        $qb = new QueryBuilder();

        $qb->select([
            'p.id_cat',
            'c.name_cat',
            'c.published_cat',
            'ca.name_cat AS parent_cat',
            'IFNULL(pi.default_img, 0) as default_img',
            'c.content_cat',
            'p.menu_cat',
            'p.date_register',
            'p.order_cat'
        ])
            ->from('mc_catalog_cat', 'p')
            ->join('mc_catalog_cat_content', 'c', 'p.id_cat = c.id_cat')
            ->leftJoin('mc_catalog_cat_img', 'pi', 'p.id_cat = pi.id_cat AND pi.default_img = 1')
            ->join('mc_lang', 'lang', 'c.id_lang = lang.id_lang')
            ->leftJoin('mc_catalog_cat', 'pa', 'p.id_parent = pa.id_cat')
            ->leftJoin('mc_catalog_cat_content', 'ca', 'pa.id_cat = ca.id_cat AND ca.id_lang = :id_lang')
            ->where('p.id_parent = :parent', ['parent' => $parentId])
            ->where('c.id_lang = :id_lang', ['id_lang' => $idLang])
            ->orderBy('p.order_cat', 'ASC');

        return $this->executeAll($qb);
    }

    /**
     * Récupère une catégorie et tous ses contenus associés (toutes langues)
     */
    public function fetchCategoryById(int $id): array|false
    {
        $qb = new QueryBuilder();
        $qb->select('*')->from('mc_catalog_cat')->where('id_cat = :id', ['id' => $id]);
        $category = $this->executeRow($qb);

        if (!$category) return false;

        $qbContent = new QueryBuilder();
        $qbContent->select('*')->from('mc_catalog_cat_content')->where('id_cat = :id', ['id' => $id]);
        $contents = $this->executeAll($qbContent);

        $category['content'] = [];
        if ($contents) {
            foreach ($contents as $c) {
                $category['content'][$c['id_lang']] = $c;
            }
        }

        return $category;
    }

    /**
     * Récupère la liste simplifiée de toutes les catégories pour le menu déroulant (Select)
     */
    public function fetchAllCategoriesForSelect(int $idLang): array|false
    {
        $qb = new QueryBuilder();

        $qb->select([
            'p.id_cat',
            'p.id_parent AS parent_cat',
            'c.name_cat'
        ])
            ->from('mc_catalog_cat', 'p')
            ->join('mc_catalog_cat_content', 'c', 'p.id_cat = c.id_cat')
            ->where('c.id_lang = :id_lang', ['id_lang' => $idLang])
            ->orderBy('p.id_parent', 'ASC')
            ->orderBy('p.order_cat', 'ASC');

        return $this->executeAll($qb);
    }

    /**
     * Met à jour la position d'une catégorie spécifique
     */
    public function updateOrderCategories(int $idCat, int $position): bool
    {
        $qb = new QueryBuilder();
        $qb->update('mc_catalog_cat', ['order_cat' => $position])
            ->where('id_cat = :id', ['id' => $idCat]);

        return $this->executeUpdate($qb);
    }

    /**
     * Supprime une ou plusieurs catégories
     */
    public function deleteCategories(array $ids): bool
    {
        if (empty($ids)) return false;

        $qb = new QueryBuilder();
        $qb->delete('mc_catalog_cat')->whereIn('id_cat', $ids);
        $res1 = $this->executeDelete($qb);

        $qbContent = new QueryBuilder();
        $qbContent->delete('mc_catalog_cat_content')->whereIn('id_cat', $ids);
        $res2 = $this->executeDelete($qbContent);

        return $res1 && $res2;
    }

    /**
     * Met à jour la structure (mc_catalog_cat)
     */
    public function updateCategoryStructure(int $idCat, array $data): bool
    {
        $qb = new QueryBuilder();
        $qb->update('mc_catalog_cat', $data)
            ->where('id_cat = :id', ['id' => $idCat]);

        return $this->executeUpdate($qb);
    }

    /**
     * Sauvegarde le contenu (mc_catalog_cat_content)
     */
    public function saveCategoryContent(int $idCat, int $idLang, array $data): bool
    {
        $qbCheck = new QueryBuilder();
        $qbCheck->select(['id_cat'])
            ->from('mc_catalog_cat_content')
            ->where('id_cat = :c AND id_lang = :l', ['c' => $idCat, 'l' => $idLang]);

        $exists = $this->executeRow($qbCheck);

        $qb = new QueryBuilder();
        if ($exists) {
            $qb->update('mc_catalog_cat_content', $data)
                ->where('id_cat = :c AND id_lang = :l', [
                    'c' => $idCat,
                    'l' => $idLang
                ]);
            return $this->executeUpdate($qb);
        } else {
            $data['id_cat'] = $idCat;
            $data['id_lang'] = $idLang;
            $qb->insert('mc_catalog_cat_content', $data);
            return $this->executeInsert($qb);
        }
    }

    /**
     * Insère une nouvelle structure de catégorie et retourne son ID
     */
    public function insertCategoryStructure(array $data): int|false
    {
        $qb = new QueryBuilder();
        $qb->insert('mc_catalog_cat', $data);

        if ($this->executeInsert($qb)) {
            return $this->getLastInsertId();
        }

        return false;
    }

    // ==========================================
    // GESTION DES IMAGES (LA GALERIE)
    // ==========================================

    /**
     * Insère une nouvelle image et la définit par défaut si c'est la première
     */
    public function insertImage(int $catId, string $filename): bool
    {
        $qbCount = new QueryBuilder();
        $qbCount->select(['COUNT(id_img) as total'])
            ->from('mc_catalog_cat_img')
            ->where('id_cat = :id', ['id' => $catId]);

        $countResult = $this->executeRow($qbCount);
        $order = (int)($countResult['total'] ?? 0);
        $isDefault = ($order === 0) ? 1 : 0;

        $qbInsert = new QueryBuilder();
        $qbInsert->insert('mc_catalog_cat_img', [
            'id_cat'      => $catId,
            'name_img'    => $filename,
            'order_img'   => $order,
            'default_img' => $isDefault
        ]);

        return $this->executeInsert($qbInsert);
    }

    /**
     * Met à jour l'ordre des images (Drag & Drop)
     */
    public function reorderImages(array $imageIds): bool
    {
        $success = true;
        foreach ($imageIds as $index => $id) {
            $qb = new QueryBuilder();
            $qb->update('mc_catalog_cat_img', ['order_img' => $index])
                ->where('id_img = :id_img', ['id_img' => (int)$id]);

            if (!$this->executeUpdate($qb)) {
                $success = false;
            }
        }
        return $success;
    }

    /**
     * Définit une image comme image par défaut pour une catégorie
     */
    public function setDefaultImage(int $catId, int $imageId): bool
    {
        // 1. On remet toutes les images de la catégorie à 0
        $qbReset = new QueryBuilder();
        $qbReset->update('mc_catalog_cat_img', ['default_img' => 0])
            ->where('id_cat = :id_cat', ['id_cat' => $catId]);
        $this->executeUpdate($qbReset);

        // 2. On passe la nouvelle image à 1
        $qbSet = new QueryBuilder();
        $qbSet->update('mc_catalog_cat_img', ['default_img' => 1])
            ->where('id_img = :id_img', ['id_img' => $imageId]);

        return $this->executeUpdate($qbSet);
    }

    /**
     * Récupère le plus grand ID pour le suffixe lors de l'upload
     */
    public function getLastImageId(int $catId): int
    {
        $qb = new QueryBuilder();
        $qb->select(['MAX(id_img) as max_id'])
            ->from('mc_catalog_cat_img')
            ->where('id_cat = :id', ['id' => $catId]);

        $result = $this->executeRow($qb);
        return (int)($result['max_id'] ?? 0);
    }

    /**
     * Récupère toutes les images d'une catégorie triées par ordre
     */
    public function fetchImagesByCategory(int $catId): array
    {
        $qb = new QueryBuilder();
        $qb->select(['*'])
            ->from('mc_catalog_cat_img')
            ->where('id_cat = :id', ['id' => $catId])
            ->orderBy('order_img', 'ASC');

        $result = $this->executeAll($qb);
        return $result ?: [];
    }

    /**
     * Supprime une image de la base de données (et retourne ses infos pour suppression physique)
     */
    public function deleteImage(int $imageId): array|false
    {
        $qbSelect = new QueryBuilder();
        $qbSelect->select(['*'])->from('mc_catalog_cat_img')->where('id_img = :id', ['id' => $imageId]);
        $img = $this->executeRow($qbSelect);

        if ($img) {
            $qbDel = new QueryBuilder();
            $qbDel->delete('mc_catalog_cat_img')->where('id_img = :id', ['id' => $imageId]);
            if ($this->executeDelete($qbDel)) {
                return $img;
            }
        }
        return false;
    }

    /**
     * Récupère les métadonnées d'une image pour toutes les langues
     */
    public function fetchImageMeta(int $idImg): array
    {
        $qb = new QueryBuilder();
        $qb->select('*')->from('mc_catalog_cat_img_content')->where('id_img = :id', ['id' => $idImg]);
        $results = $this->executeAll($qb);

        $meta = [];
        if ($results) {
            foreach ($results as $row) {
                $meta[$row['id_lang']] = $row;
            }
        }
        return $meta;
    }

    /**
     * Sauvegarde ou met à jour les métadonnées d'une image
     */
    public function saveImageMeta(int $idImg, int $idLang, array $data): bool
    {
        $table = 'mc_catalog_cat_img_content';

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
}