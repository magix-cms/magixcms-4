<?php

declare(strict_types=1);

namespace App\Frontend\Db;

use Magepattern\Component\Database\QueryBuilder;
use App\Component\Routing\UrlTool;

class MenuDb extends BaseDb
{
    public function debugQuery(QueryBuilder $qb): void
    {
        echo "<div style='background:#f8f9fa; border:1px solid #ddd; padding:15px; margin:10px 0; font-family:monospace; font-size:12px;'>";
        echo "<strong style='color:#d63384;'>[DEBUG QUERYBUILDER]</strong><br><br>";

        if (method_exists($qb, 'getSQL')) {
            echo "<strong>SQL :</strong> " . $qb->getSQL() . "<br>";
        } elseif (method_exists($qb, 'getQuery')) {
            echo "<strong>SQL :</strong> " . $qb->getQuery() . "<br>";
        } else {
            echo "<strong>Structure de l'objet :</strong><pre>";
            print_r($qb);
            echo "</pre>";
        }
        echo "</div>";
    }
    /**
     * Récupère tous les liens du menu (actifs uniquement) pour une langue donnée.
     * Construit et retourne un arbre hiérarchique prêt pour Smarty.
     */
    public function getFrontendTree(int $idLang, string $isoLang = 'fr'): array
    {
        $qb = new QueryBuilder();
        $urlTool = new \App\Component\Routing\UrlTool();

        // 1. Définition des colonnes (Correction de cat_c.url_cat)
        $selectCols = [
            'm.id_link',
            'm.id_parent',
            'm.mode_link',
            'm.type_link',
            'm.id_page',
            'mc.name_link',
            'mc.title_link',
            'mc.url_link as manual_url',
            'cms_c.url_pages as cms_url',
            'ab_c.url_about as about_url',
            'cat_c.url_cat as category_url' // <-- CORRECTION ICI
        ];

        // 2. Construction de la requête avec les bons noms de tables et clés primaires
        $qb->select(implode(', ', $selectCols))
            ->from('mc_menu', 'm')
            ->leftJoin('mc_menu_content', 'mc', 'm.id_link = mc.id_link AND mc.id_lang = ' . $idLang)
            ->leftJoin('mc_cms_page_content', 'cms_c', "m.type_link = 'pages' AND m.id_page = cms_c.id_pages AND cms_c.id_lang = " . $idLang)
            ->leftJoin('mc_about_content', 'ab_c', "m.type_link = 'about_page' AND m.id_page = ab_c.id_about AND ab_c.id_lang = " . $idLang)

            // <-- CORRECTION DE LA JOINTURE ICI (table mc_catalog_cat_content et id_cat)
            ->leftJoin('mc_catalog_cat_content', 'cat_c', "m.type_link = 'category' AND m.id_page = cat_c.id_cat AND cat_c.id_lang = " . $idLang)

            ->orderBy('m.id_parent, m.order_link');

        //$this->debugQuery($qb);
        $elements = $this->executeAll($qb) ?: [];

        // 3. Traitement dynamique des URLs
        foreach ($elements as &$el) {

            if (!empty($el['manual_url'])) {
                $el['url_link'] = $el['manual_url'];
                continue;
            }

            $type = $el['type_link'] ?? '';
            $idPage = (int)($el['id_page'] ?? 0);

            $dataUrl = [
                'iso' => $isoLang,
                'id'  => $idPage
            ];

            if ($type === 'pages' && !empty($el['cms_url'])) {
                $dataUrl['type'] = 'pages';
                $dataUrl['url']  = $el['cms_url'];
                $el['url_link']  = $urlTool->buildUrl($dataUrl);

            } elseif ($type === 'about_page' && !empty($el['about_url'])) {
                $dataUrl['type'] = 'about';
                $dataUrl['url']  = $el['about_url'];
                $el['url_link']  = $urlTool->buildUrl($dataUrl);

            } elseif ($type === 'category' && !empty($el['category_url'])) {
                $dataUrl['type'] = 'category';
                $dataUrl['url']  = $el['category_url'];
                $el['url_link']  = $urlTool->buildUrl($dataUrl);

                // 🟢 AVEZ-VOUS BIEN AJOUTÉ CECI ?
            } elseif ($type === 'catalog') {
                $dataUrl['type'] = 'catalog';
                $el['url_link']  = $urlTool->buildUrl($dataUrl);

                // 🟢 AJOUT : (Optionnel) Faites pareil si vous avez une page racine pour les News !
            } elseif ($type === 'news') {
                $dataUrl['type'] = 'news';
                $el['url_link']  = $urlTool->buildUrl($dataUrl);

            } else {
                $el['url_link'] = '#';
            }
        }

        return $this->buildGenericTree($elements, 'id_link');
    }

    /**
     * Récupère les pages enfants d'une page spécifique pour créer le sous-menu
     */
    public function getSubPages(int $idParentPage, int $idLang): array
    {
        $qb = new QueryBuilder();

        // On renomme les colonnes pour que Smarty les lise comme des liens normaux
        $qb->select('p.id_pages, p.id_parent, c.name_pages AS name_link, c.name_pages AS title_link, c.url_pages AS url_link')
            ->from('mc_cms_page', 'p')
            ->leftJoin('mc_cms_page_content', 'c', 'p.id_pages = c.id_pages AND c.id_lang = ' . $idLang)
            ->where('p.id_parent = ' . $idParentPage)
            ->orderBy('p.id_parent, p.id_pages'); // Adaptez avec p.order_pages si vous avez un tri

        $elements = $this->executeAll($qb) ?: [];

        // On utilise l'ID de la page comme clé primaire
        return $this->buildGenericTree($elements, 'id_pages');
    }
    /**
     * Récupère les pages enfants d'une page About spécifique pour créer le sous-menu
     */
    public function getSubAbout(int $idParentAbout, int $idLang): array
    {
        $qb = new QueryBuilder();

        // On sélectionne url_about explicitement pour que le BaseController puisse construire le lien
        $qb->select('a.id_about, a.id_parent, c.name_about AS name_link, c.name_about AS title_link, c.url_about')
            ->from('mc_about', 'a')
            ->leftJoin('mc_about_content', 'c', 'a.id_about = c.id_about AND c.id_lang = ' . (int)$idLang)
            ->where('a.id_parent = ' . (int)$idParentAbout)
            ->orderBy('a.id_parent, a.order_about'); // Adapté selon votre tri (order_about ou id_about)

        $elements = $this->executeAll($qb) ?: [];

        // On utilise l'ID de la page about comme clé
        return $this->buildGenericTree($elements, 'id_about');
    }
    /**
     * Récupère les sous-catégories d'une catégorie spécifique pour le menu
     */
    public function getSubCategories(int $idParentCat, int $idLang): array
    {
        $qb = new QueryBuilder();

        $qb->select('c.id_cat, c.id_parent, cc.name_cat AS name_link, cc.name_cat AS title_link, cc.url_cat')
            ->from('mc_catalog_cat', 'c')
            ->leftJoin('mc_catalog_cat_content', 'cc', 'c.id_cat = cc.id_cat AND cc.id_lang = ' . (int)$idLang)
            ->where('c.id_parent = ' . (int)$idParentCat)
            ->orderBy('c.id_parent, c.order_cat');

        $elements = $this->executeAll($qb) ?: [];

        return $this->buildGenericTree($elements, 'id_cat');
    }
    /**
     * Transforme un tableau plat en un tableau multidimensionnel (Arbre).
     * Les enfants sont placés dans une clé 'subdata' et les éléments de premier niveau dans 'root'.
     */
    private function buildGenericTree(array $elements, string $idKey): array
    {
        if (empty($elements)) return [];

        $indexed = [];
        $root = [];

        foreach ($elements as $el) {
            $id = (int)($el[$idKey] ?? 0);
            $el['subdata'] = [];
            $indexed[$id] = $el;
        }

        foreach ($indexed as $id => &$node) {
            $parentId = (int)($node['id_parent'] ?? 0);

            // Si un parent n'est pas dans le dataset (ou vaut 0), l'item est ré-attaché au root
            if ($parentId === 0 || !isset($indexed[$parentId])) {
                $root[] = &$node;
            } else {
                $indexed[$parentId]['subdata'][] = &$node;
            }
        }

        return $root;
    }
}