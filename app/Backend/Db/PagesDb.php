<?php

declare(strict_types=1);

namespace App\Backend\Db;

use Magepattern\Component\Database\QueryBuilder;
use Magepattern\Component\Database\Layer;
use Magepattern\Component\Tool\DateTool;

class PagesDb extends BaseDb
{
    /**
     * Récupère la liste des pages avec gestion complète de la recherche et pagination.
     *
     * @param int $page Le numéro de la page en cours
     * @param int $limit Le nombre de résultats par page
     * @param array $search Le tableau des filtres de recherche
     * @param int $idLang id de la langue
     * @return array|false Retourne ['data' => [...], 'meta' => [...]] ou false en cas d'erreur
     */
    public function fetchAllPages(int $page = 1, int $limit = 25, array $search = [], int $idLang = 1): array|false
    {
        $qb = new QueryBuilder();

        // 1. LE CŒUR DE LA REQUÊTE (On définit TOUT ici une seule fois)
        $qb->select([
            'p.id_pages',
            'c.name_pages',       // Le titre de la page
            'c.published_pages',
            'ca.name_pages AS parent_pages', // Le titre du parent
            'IFNULL(pi.default_img, 0) as default_img',
            'c.content_pages',
            'c.seo_title_pages',
            'c.seo_desc_pages',
            'p.menu_pages',
            'p.date_register'
        ])
            ->from('mc_cms_page', 'p')
            ->join('mc_cms_page_content', 'c', 'p.id_pages = c.id_pages')
            ->leftJoin('mc_cms_page_img', 'pi', 'p.id_pages = pi.id_pages AND pi.default_img = 1')
            ->join('mc_lang', 'lang', 'c.id_lang = lang.id_lang')
            // Jointure pour récupérer l'ID et le NOM du parent
            ->leftJoin('mc_cms_page', 'pa', 'p.id_parent = pa.id_pages')
            ->leftJoin('mc_cms_page_content', 'ca', 'pa.id_pages = ca.id_pages AND ca.id_lang = :id_lang')
            ->where('c.id_lang = :id_lang', ['id_lang' => $idLang]);

        // 2. GESTION DE LA RECHERCHE
        if (!empty($search)) {
            // J'ai supprimé le select() et les join() redondants qui écrasaient tes données
            $qb->orderBy('p.id_pages', 'DESC');

            $nbc = 1;
            foreach ($search as $key => $q) {
                if ($q !== '') {
                    $paramName = 'p' . $nbc;
                    $binds = [];
                    switch ($key) {
                        case 'id_pages':
                        case 'menu_pages':
                            $binds[$paramName] = $q;
                            $qb->where("p.{$key} = :{$paramName}", $binds);
                            break;
                        case 'published_pages':
                            $binds[$paramName] = $q;
                            $qb->where("c.{$key} = :{$paramName}", $binds);
                            break;
                        case 'name_pages':
                            $binds[$paramName] = '%' . $q . '%';
                            $qb->where("c.{$key} LIKE :{$paramName}", $binds);
                            break;
                        case 'parent_pages':
                            $binds[$paramName] = '%' . $q . '%';
                            $qb->where("ca.name_pages LIKE :{$paramName}", $binds);
                            break;
                        case 'date_register':
                            $formattedDate = DateTool::toSql((string)$q);
                            $binds[$paramName] = '%' . $formattedDate . '%';
                            $qb->where("p.{$key} LIKE :{$paramName}", $binds);
                            break;
                    }
                    $nbc++;
                }
            }
        } else {
            // 3. COMPORTEMENT PAR DÉFAUT (Si aucune recherche)
            // IMPORTANT : Suppression du WHERE id_parent IS NULL pour voir les 6 pages
            $qb->orderBy('p.id_parent', 'ASC') // Trie pour regrouper par parent
            ->orderBy('p.order_pages', 'ASC');
        }

        return $this->executePaginatedQuery($qb, $page, $limit);
    }
    /**
     * Récupère les pages enfants d'un parent spécifique
     */
    /**
     * Récupère toutes les sous-pages d'une page parente
     */
    public function fetchPagesByParent(int $parentId, int $idLang): array|false
    {
        $qb = new QueryBuilder();

        $qb->select([
            'p.id_pages',
            'c.name_pages',
            'c.published_pages',
            'ca.name_pages AS parent_pages', // On garde l'alias attendu par ton TPL
            'IFNULL(pi.default_img, 0) as default_img',
            'c.content_pages',
            'p.menu_pages',
            'p.date_register',
            'p.order_pages'
        ])
            ->from('mc_cms_page', 'p')
            ->join('mc_cms_page_content', 'c', 'p.id_pages = c.id_pages')
            ->leftJoin('mc_cms_page_img', 'pi', 'p.id_pages = pi.id_pages AND pi.default_img = 1')
            ->join('mc_lang', 'lang', 'c.id_lang = lang.id_lang')
            // Jointures pour récupérer le nom du parent
            ->leftJoin('mc_cms_page', 'pa', 'p.id_parent = pa.id_pages')
            ->leftJoin('mc_cms_page_content', 'ca', 'pa.id_pages = ca.id_pages AND ca.id_lang = :id_lang')
            // LES CONDITIONS STRICTES
            ->where('p.id_parent = :parent', ['parent' => $parentId])
            ->where('c.id_lang = :id_lang', ['id_lang' => $idLang])
            // Tri crucial pour le Drag & Drop
            ->orderBy('p.order_pages', 'ASC');

        // Pas de pagination, on exécute tout d'un coup
        return $this->executeAll($qb);
    }
    /**
     * Récupère une page et tous ses contenus associés (toutes langues)
     */
    public function fetchPageById(int $id): array|false
    {
        // 1. Infos structurelles de la page
        $qb = new QueryBuilder();
        $qb->select('*')->from('mc_cms_page')->where('id_pages = :id', ['id' => $id]);
        $page = $this->executeRow($qb);

        if (!$page) return false;

        // 2. Récupération de tous les contenus
        $qbContent = new QueryBuilder();
        $qbContent->select('*')->from('mc_cms_page_content')->where('id_pages = :id', ['id' => $id]);
        $contents = $this->executeAll($qbContent);

        // 3. Indexation par id_lang pour le template
        $page['content'] = [];
        if ($contents) {
            foreach ($contents as $c) {
                $page['content'][$c['id_lang']] = $c;
            }
        }

        return $page;
    }
    /**
     * Récupère la liste simplifiée de toutes les pages pour le menu déroulant (Select)
     */
    public function fetchAllPagesForSelect(int $idLang): array|false
    {
        $qb = new QueryBuilder();

        $qb->select([
            'p.id_pages',
            'p.id_parent AS parent_pages', // Alias pour le TPL
            'c.name_pages'
        ])
            ->from('mc_cms_page', 'p')
            // Jointure standard
            ->join('mc_cms_page_content', 'c', 'p.id_pages = c.id_pages')
            // Le paramètre PDO est passé proprement dans le WHERE
            ->where('c.id_lang = :id_lang', ['id_lang' => $idLang])
            ->orderBy('p.id_parent', 'ASC')
            ->orderBy('p.order_pages', 'ASC');

        // On exécute simplement le QueryBuilder
        return $this->executeAll($qb);
    }
    /**
     * Met à jour la position d'une page spécifique
     */
    /**
     * @param int $idPage
     * @param int $position
     * @return bool
     */
    public function updateOrderPages(int $idPage, int $position): bool
    {
        $qb = new QueryBuilder();
        $qb->update('mc_cms_page', ['order_pages' => $position])
            ->where('id_pages = :id', ['id' => $idPage]);

        return $this->executeUpdate($qb);
    }

    /**
     * @param array $ids
     * @return bool
     */
    public function deletePages(array $ids): bool
    {
        if (empty($ids)) return false;

        // 1. Suppression dans la table principale
        $qb = new QueryBuilder();
        $qb->delete('mc_cms_page')->whereIn('id_pages', $ids);
        $res1 = $this->executeDelete($qb);

        // 2. Suppression dans la table de contenu (multilingue)
        $qbContent = new QueryBuilder();
        $qbContent->delete('mc_cms_page_content')->whereIn('id_pages', $ids);
        $res2 = $this->executeDelete($qbContent);

        return $res1 && $res2;
    }
    /**
     * Met à jour la structure (mc_cms_page)
     */
    /**
     * @param int $idPage
     * @param array $data
     * @return bool
     */
    public function updatePageStructure(int $idPage, array $data): bool
    {
        $qb = new QueryBuilder();
        $qb->update('mc_cms_page', $data)
            ->where('id_pages = :id', ['id' => $idPage]);

        return $this->executeUpdate($qb);
    }

    /**
     * Sauvegarde le contenu (mc_cms_page_content)
     */
    /**
     * @param int $idPage
     * @param int $idLang
     * @param array $data
     * @return bool
     */
    public function savePageContent(int $idPage, int $idLang, array $data): bool
    {
        // 1. On vérifie l'existence de la ligne pour cette langue
        $qbCheck = new QueryBuilder();
        $qbCheck->select(['id_pages'])
            ->from('mc_cms_page_content')
            ->where('id_pages = :p AND id_lang = :l', ['p' => $idPage, 'l' => $idLang]);

        $exists = $this->executeRow($qbCheck);

        $qb = new QueryBuilder();
        if ($exists) {
            // UPDATE : On cible par le couple (id_pages + id_lang)
            $qb->update('mc_cms_page_content', $data)
                ->where('id_pages = :p AND id_lang = :l', [
                    'p' => $idPage,
                    'l' => $idLang
                ]);
            return $this->executeUpdate($qb);
        } else {
            // INSERT : Nouvelle traduction
            $data['id_pages'] = $idPage;
            $data['id_lang'] = $idLang;
            $qb->insert('mc_cms_page_content', $data);
            return $this->executeInsert($qb);
        }
    }
    /**
     * Insère une nouvelle structure de page et retourne son ID
     */
    /**
     * @param array $data
     * @return int|false
     */
    public function insertPageStructure(array $data): int|false
    {
        $qb = new QueryBuilder();
        $qb->insert('mc_cms_page', $data);

        // Si l'insertion réussit, BaseDb appellera Layer,
        // qui utilisera la MEME connexion pour te renvoyer le vrai ID !
        if ($this->executeInsert($qb)) {
            return $this->getLastInsertId();
        }

        return false;
    }
    /**
     * Insère une nouvelle image et la définit par défaut si c'est la première
     */
    public function insertImage(int $pageId, string $filename): bool
    {
        $qbCount = new QueryBuilder();
        $qbCount->select(['COUNT(id_img) as total'])
            ->from('mc_cms_page_img')
            ->where('id_pages = :id', ['id' => $pageId]);

        $countResult = $this->executeRow($qbCount);
        $order = (int)($countResult['total'] ?? 0);
        $isDefault = ($order === 0) ? 1 : 0;

        $qbInsert = new QueryBuilder();
        $qbInsert->insert('mc_cms_page_img', [
            'id_pages'    => $pageId,
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
            $qb->update('mc_cms_page_img', ['order_img' => $index])
                ->where('id_img = :id_img', ['id_img' => (int)$id]);

            if (!$this->executeUpdate($qb)) {
                $success = false;
            }
        }
        return $success;
    }

    /**
     * Définit une image comme image par défaut pour une page
     */
    public function setDefaultImage(int $pageId, int $imageId): bool
    {
        // 1. On remet toutes les images de la page à 0
        $qbReset = new QueryBuilder();
        $qbReset->update('mc_cms_page_img', ['default_img' => 0])
            ->where('id_pages = :id_pages', ['id_pages' => $pageId]);
        $this->executeUpdate($qbReset);

        // 2. On passe la nouvelle image à 1
        $qbSet = new QueryBuilder();
        $qbSet->update('mc_cms_page_img', ['default_img' => 1])
            ->where('id_img = :id_img', ['id_img' => $imageId]);

        return $this->executeUpdate($qbSet);
    }

    /**
     * Récupère le plus grand ID pour le suffixe lors de l'upload
     */
    public function getLastImageId(int $pageId): int
    {
        $qb = new QueryBuilder();
        $qb->select(['MAX(id_img) as max_id'])
            ->from('mc_cms_page_img')
            ->where('id_pages = :id', ['id' => $pageId]);

        $result = $this->executeRow($qb);
        return (int)($result['max_id'] ?? 0);
    }
    /**
     * Récupère toutes les images d'une page triées par ordre
     */
    public function fetchImagesByPage(int $pageId): array
    {
        $qb = new QueryBuilder();
        $qb->select(['*'])
            ->from('mc_cms_page_img')
            ->where('id_pages = :id', ['id' => $pageId])
            ->orderBy('order_img', 'ASC');

        $result = $this->executeAll($qb);
        return $result ?: [];
    }

    /**
     * Supprime une image de la base de données (et retourne ses infos pour suppression physique)
     */
    public function deleteImage(int $imageId): array|false
    {
        // 1. On récupère les infos de l'image avant de la supprimer
        $qbSelect = new QueryBuilder();
        $qbSelect->select(['*'])->from('mc_cms_page_img')->where('id_img = :id', ['id' => $imageId]);
        $img = $this->executeRow($qbSelect);

        if ($img) {
            // 2. On supprime l'entrée en BDD
            $qbDel = new QueryBuilder();
            $qbDel->delete('mc_cms_page_img')->where('id_img = :id', ['id' => $imageId]);
            if ($this->executeDelete($qbDel)) {
                return $img; // On retourne l'image pour pouvoir effacer les fichiers physiques
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
        // Remplacer 'mc_cms_page_img_content' par 'mc_about_img_content' dans AboutDb
        $qb->select('*')->from('mc_cms_page_img_content')->where('id_img = :id', ['id' => $idImg]);
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
        // Remplacer 'mc_cms_page_img_content' par 'mc_about_img_content' dans AboutDb
        $table = 'mc_cms_page_img_content';

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
     * Compte le nombre total de pages dans la table mc_cms_page
     */
    public function countActivePages(): int
    {
        $qb = new QueryBuilder();
        $qb->select('COUNT(*) as total')->from('mc_cms_page');

        $result = $this->executeRow($qb);

        return $result ? (int)$result['total'] : 0;
    }
    /**
     * Récupère une liste allégée des pages (avec leur slug) pour la popup TinyMCE
     */
    public function getPagesForTinymce(int $idLang): array
    {
        $qb = new QueryBuilder();
        $qb->select([
            'p.id_pages',
            'p.id_parent',
            'c.name_pages',
            'c.url_pages'
        ])
            ->from('mc_cms_page', 'p')
            ->join('mc_cms_page_content', 'c', 'p.id_pages = c.id_pages')
            ->where('c.id_lang = :id_lang', ['id_lang' => $idLang])
            ->orderBy('p.id_parent', 'ASC')
            ->orderBy('p.order_pages', 'ASC');

        $result = $this->executeAll($qb);
        return $result ?: [];
    }
    /**
     * Utilitaire privé pour formater la date avant de l'envoyer dans la requête LIKE.
     * À remplacer si tu as un DateTool dans Magepattern.
     */
    /*private function formatDateForDb(string $date): string
    {
        // Si le format d'entrée est "dd/mm/yyyy"
        $d = \DateTime::createFromFormat('d/m/Y', $date);
        return $d ? $d->format('Y-m-d') : $date;
    }*/
}