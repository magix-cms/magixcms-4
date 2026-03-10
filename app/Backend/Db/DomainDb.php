<?php

declare(strict_types=1);

namespace App\Backend\Db;

use Magepattern\Component\Database\QueryBuilder;

class DomainDb extends BaseDb
{
    /**
     * Récupère la liste paginée des domaines (pour le tableau générique)
     */
    public function fetchAllDomains(int $page = 1, int $limit = 25, array $search = []): array|false
    {
        $qb = new QueryBuilder();
        $qb->select('*')->from('mc_domain');

        // Note: pas de tri 'order' spécifique car pas de drag&drop ici, on trie par ID
        $qb->orderBy('id_domain', 'DESC');

        return $this->executePaginatedQuery($qb, $page, $limit);
    }

    /**
     * Récupère un domaine par son ID
     */
    public function fetchDomainById(int $id): array|false
    {
        $qb = new QueryBuilder();
        $qb->select('*')->from('mc_domain')->where('id_domain = :id', ['id' => $id]);
        return $this->executeRow($qb);
    }

    /**
     * Insertion d'un nouveau domaine
     */
    public function insertDomain(array $data): int|false
    {
        $qb = new QueryBuilder();
        $qb->insert('mc_domain', $data);
        if ($this->executeInsert($qb)) {
            return $this->getLastInsertId();
        }
        return false;
    }

    /**
     * Mise à jour d'un domaine
     */
    public function updateDomain(int $id, array $data): bool
    {
        $qb = new QueryBuilder();
        $qb->update('mc_domain', $data)->where('id_domain = :id', ['id' => $id]);
        return $this->executeUpdate($qb);
    }

    /**
     * Suppression d'un ou plusieurs domaines
     */
    public function deleteDomain(array $ids): bool
    {
        if (empty($ids)) return false;

        // On supprime d'abord les liaisons de langues pour éviter les orphelins
        $qbLangs = new QueryBuilder();
        $qbLangs->delete('mc_domain_language')->whereIn('id_domain', $ids);
        $this->executeDelete($qbLangs);

        $qb = new QueryBuilder();
        $qb->delete('mc_domain')->whereIn('id_domain', $ids);
        return $this->executeDelete($qb);
    }

    // --- MODULES CONFIG (mc_config) ---

    /**
     * Récupère l'état d'activation des modules sous forme de tableau clé => valeur
     */
    public function fetchModulesConfig(): array
    {
        $qb = new QueryBuilder();
        $qb->select('*')->from('mc_config');
        $results = $this->executeAll($qb);

        $configs = [];
        if ($results) {
            foreach ($results as $row) {
                $configs[$row['attr_name']] = (int)$row['status'];
            }
        }
        return $configs;
    }

    /**
     * Met à jour le statut d'un module
     */
    public function updateModuleConfig(string $attrName, int $status): bool
    {
        $qb = new QueryBuilder();
        $qb->update('mc_config', ['status' => $status])->where('attr_name = :attr', ['attr' => $attrName]);
        return $this->executeUpdate($qb);
    }

    // --- GESTION DES LANGUES DU DOMAINE (mc_domain_language) ---

    /**
     * Récupère les langues associées à un domaine précis
     */
    public function fetchDomainLanguages(int $idDomain): array
    {
        $qb = new QueryBuilder();
        $qb->select('*')->from('mc_domain_language')->where('id_domain = :id', ['id' => $idDomain]);
        $results = $this->executeAll($qb);

        $assoc = [];
        if ($results) {
            foreach ($results as $row) {
                // Indexé par id_lang pour faciliter l'affichage dans Smarty
                $assoc[$row['id_lang']] = $row;
            }
        }
        return $assoc;
    }

    /**
     * Synchronise les langues d'un domaine (Supprime les anciennes, insère les nouvelles)
     */
    public function syncDomainLanguages(int $idDomain, array $selectedLangs, int $defaultLangId): bool
    {
        // 1. Nettoyage des anciennes liaisons
        $qbDel = new QueryBuilder();
        $qbDel->delete('mc_domain_language')->where('id_domain = :id', ['id' => $idDomain]);
        $this->executeDelete($qbDel);

        if (empty($selectedLangs)) return true;

        $success = true;

        // 2. Insertion des nouvelles liaisons
        foreach ($selectedLangs as $idLang) {
            $isDefault = ((int)$idLang === $defaultLangId) ? 1 : 0;

            $qbIn = new QueryBuilder();
            $qbIn->insert('mc_domain_language', [
                'id_domain'    => $idDomain,
                'id_lang'      => (int)$idLang,
                'default_lang' => $isDefault
            ]);

            if (!$this->executeInsert($qbIn)) {
                $success = false;
            }
        }

        return $success;
    }
    // ==========================================
    // MÉTHODES SITEMAP (URL & IMAGES)
    // ==========================================

    /**
     * Récupère les données (URLs + Images) pour le sitemap selon le module et la langue
     */
    /**
     * Récupère les données (URLs + Images) pour le sitemap selon le module et la langue
     */
    public function getSitemapData(string $module, int $idLang): array
    {
        $qb = new QueryBuilder();

        switch ($module) {
            case 'pages':
                $qb->select(['p.id_pages as id', 'c.url_pages as url', 'p.date_register as date', 'c.name_pages as title'])
                    ->from('mc_cms_page', 'p')
                    ->join('mc_cms_page_content', 'c', 'p.id_pages = c.id_pages')
                    ->where('c.id_lang = ' . $idLang . ' AND c.published_pages = 1 AND c.url_pages != "" AND c.name_pages != ""');
                break;

            case 'news':
                // On utilise date_publish pour l'affichage de l'URL
                $qb->select(['n.id_news as id', 'c.url_news as url', 'n.date_publish as date', 'c.name_news as title'])
                    ->from('mc_news', 'n')
                    ->join('mc_news_content', 'c', 'n.id_news = c.id_news')
                    ->where('c.id_lang = ' . $idLang . ' AND c.published_news = 1 AND c.url_news != "" AND c.name_news != ""');
                break;

            case 'catalog_cat':
                $qb->select(['c.id_cat as id', 'cc.url_cat as url', 'c.date_register as date', 'cc.name_cat as title'])
                    ->from('mc_catalog_cat', 'c')
                    ->join('mc_catalog_cat_content', 'cc', 'c.id_cat = cc.id_cat')
                    ->where('cc.id_lang = ' . $idLang . ' AND cc.published_cat = 1 AND cc.url_cat != "" AND cc.name_cat != ""');
                break;

            case 'catalog_pro':
                $qb->select([
                    'p.id_product as id',
                    'c.url_p as url_pro',
                    'cat_c.url_cat as url_cat',
                    'cat_rel.id_cat as default_category_id',
                    'p.date_register as date',
                    'c.name_p as title'
                ])
                    ->from('mc_catalog_product', 'p')
                    ->join('mc_catalog_product_content', 'c', 'p.id_product = c.id_product')
                    ->leftJoin('mc_catalog', 'cat_rel', 'p.id_product = cat_rel.id_product AND cat_rel.default_c = 1')
                    ->leftJoin('mc_catalog_cat_content', 'cat_c', 'cat_rel.id_cat = cat_c.id_cat AND cat_c.id_lang = c.id_lang')
                    ->where('c.id_lang = ' . $idLang . ' AND c.published_p = 1 AND c.url_p != "" AND c.name_p != ""');
                break;

            default:
                return [];
        }

        $items = $this->executeAll($qb) ?: [];

        foreach ($items as &$item) {
            $item['images'] = $this->fetchImagesForModule($module, (int)$item['id'], $idLang);
        }

        return $items;
    }

    /**
     * Extraction des images spécifiques au module avec leurs métadonnées
     */
    private function fetchImagesForModule(string $module, int $idTarget, int $idLang): array
    {
        $qb = new QueryBuilder();

        // --- IMAGES DES PRODUITS ---
        if ($module === 'catalog_pro') {
            $qb->select(['i.name_img as loc', 'ic.title_img as title', 'ic.alt_img as caption'])
                ->from('mc_catalog_product_img', 'i')
                ->leftJoin('mc_catalog_product_img_content', 'ic', 'i.id_img = ic.id_img AND ic.id_lang = ' . $idLang)
                ->where('i.id_product = :id', ['id' => $idTarget])
                ->orderBy('i.order_img', 'ASC');

            return $this->executeAll($qb) ?: [];
        }

        // --- IMAGES DES CATÉGORIES (Catalogue) ---
        if ($module === 'catalog_cat') {
            $qb->select(['i.name_img as loc', 'ic.title_img as title', 'ic.alt_img as caption'])
                ->from('mc_catalog_cat_img', 'i')
                ->leftJoin('mc_catalog_cat_img_content', 'ic', 'i.id_img = ic.id_img AND ic.id_lang = ' . $idLang)
                ->where('i.id_cat = :id', ['id' => $idTarget])
                ->orderBy('i.order_img', 'ASC');

            return $this->executeAll($qb) ?: [];
        }

        // --- IMAGES DES ACTUALITÉS ---
        if ($module === 'news') {
            $qb->select(['i.name_img as loc', 'ic.title_img as title', 'ic.alt_img as caption'])
                ->from('mc_news_img', 'i')
                ->leftJoin('mc_news_img_content', 'ic', 'i.id_img = ic.id_img AND ic.id_lang = ' . $idLang)
                ->where('i.id_news = :id', ['id' => $idTarget])
                ->orderBy('i.order_img', 'ASC');

            return $this->executeAll($qb) ?: [];
        }

        // --- IMAGES DES PAGES CMS ---
        if ($module === 'pages') {
            $qb->select(['i.name_img as loc', 'ic.title_img as title', 'ic.alt_img as caption'])
                ->from('mc_cms_page_img', 'i')
                ->leftJoin('mc_cms_page_img_content', 'ic', 'i.id_img = ic.id_img AND ic.id_lang = ' . $idLang)
                ->where('i.id_pages = :id', ['id' => $idTarget])
                ->orderBy('i.order_img', 'ASC');

            return $this->executeAll($qb) ?: [];
        }

        return [];
    }
}