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