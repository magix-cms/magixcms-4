<?php

declare(strict_types=1);

namespace App\Frontend\Db;

use Magepattern\Component\Database\QueryBuilder;
use Magepattern\Component\Database\QueryHelper;
use App\Component\Hook\HookManager;

class CategoryDb extends BaseDb
{
    /**
     * Récupère la catégorie courante (fiche complète) avec Override
     */
    public function getCategoryPage(int $idCat, int $idLang): array|false
    {
        $qb = new QueryBuilder();
        $qb->select(['c.*', 'cc.*', 'i.name_img', 'ic.alt_img', 'ic.title_img'])
            ->from('mc_catalog_cat', 'c')
            ->join('mc_catalog_cat_content', 'cc', 'c.id_cat = cc.id_cat AND cc.id_lang = ' . (int)$idLang)
            ->leftJoin('mc_catalog_cat_img', 'i', 'c.id_cat = i.id_cat AND i.default_img = 1')
            ->leftJoin('mc_catalog_cat_img_content', 'ic', 'i.id_img = ic.id_img AND ic.id_lang = ' . (int)$idLang)
            ->where('c.id_cat = :id', ['id' => $idCat])
            ->where('cc.published_cat = 1');

        // 🟢 OVERRIDE : Un plugin peut ajouter des champs (ex: c.is_mega_menu, cc.custom_badge)
        $overrides = HookManager::triggerFilter('extendCategoryData', []);
        if (!empty($overrides)) {
            foreach ($overrides as $pluginOverride) {
                if (isset($pluginOverride['extendQueryParams'])) {
                    QueryHelper::applyExtendParams($qb, $pluginOverride['extendQueryParams']);
                }
            }
        }

        return $this->executeRow($qb);
    }

    /**
     * Récupère la galerie d'images de la catégorie
     */
    public function getCategoryImages(int $idCat, int $idLang): array
    {
        $qb = new QueryBuilder();
        $qb->select(['i.name_img', 'ic.alt_img', 'ic.title_img', 'ic.caption_img'])
            ->from('mc_catalog_cat_img', 'i')
            ->leftJoin('mc_catalog_cat_img_content', 'ic', 'i.id_img = ic.id_img AND ic.id_lang = ' . (int)$idLang)
            ->where('i.id_cat = :id', ['id' => $idCat])
            ->orderBy('i.order_img', 'ASC');

        return $this->executeAll($qb) ?: [];
    }

    /**
     * Récupère les sous-catégories avec Override
     */
    public function getCategoryChildren(int $parentId, int $idLang): array
    {
        $qb = new QueryBuilder();
        $qb->select(['c.id_cat', 'c.id_parent', 'cc.name_cat', 'cc.resume_cat', 'cc.content_cat', 'cc.url_cat', 'i.name_img', 'ic.alt_img', 'ic.title_img'])
            ->from('mc_catalog_cat', 'c')
            ->join('mc_catalog_cat_content', 'cc', 'c.id_cat = cc.id_cat AND cc.id_lang = ' . (int)$idLang)
            ->leftJoin('mc_catalog_cat_img', 'i', 'c.id_cat = i.id_cat AND i.default_img = 1')
            ->leftJoin('mc_catalog_cat_img_content', 'ic', 'i.id_img = ic.id_img AND ic.id_lang = ' . (int)$idLang)
            ->where('c.id_parent = :parent', ['parent' => $parentId])
            ->where('cc.published_cat = 1')
            ->orderBy('c.order_cat', 'ASC');

        // 🟢 OVERRIDE : Même système pour les listes de catégories
        $overrides = HookManager::triggerFilter('extendCategoryList', []);
        if (!empty($overrides)) {
            foreach ($overrides as $pluginOverride) {
                if (isset($pluginOverride['extendQueryParams'])) {
                    QueryHelper::applyExtendParams($qb, $pluginOverride['extendQueryParams']);
                }
            }
        }

        return $this->executeAll($qb) ?: [];
    }
    /**
     * Récupère une liste de catégories par leurs IDs (ex: pour MagixFeaturedCategory)
     * Conserve l'ordre du tableau d'IDs fourni.
     */
    public function getCategoriesByIds(array $catIds, int $idLang): array
    {
        if (empty($catIds)) {
            return [];
        }

        $qb = new QueryBuilder();
        $qb->select(['c.*', 'cc.*', 'i.name_img', 'ic.alt_img', 'ic.title_img'])
            ->from('mc_catalog_cat', 'c')
            ->join('mc_catalog_cat_content', 'cc', 'c.id_cat = cc.id_cat AND cc.id_lang = ' . (int)$idLang)
            ->leftJoin('mc_catalog_cat_img', 'i', 'c.id_cat = i.id_cat AND i.default_img = 1')
            ->leftJoin('mc_catalog_cat_img_content', 'ic', 'i.id_img = ic.id_img AND ic.id_lang = ' . (int)$idLang)
            ->where('c.id_cat IN (' . implode(',', array_map('intval', $catIds)) . ')')
            ->where('cc.published_cat = 1');

        // Conservation de l'ordre exact
        $qb->orderBy('FIELD(c.id_cat, ' . implode(',', array_map('intval', $catIds)) . ')');

        $overrides = HookManager::triggerFilter('extendCategoryList', []);
        if (!empty($overrides)) {
            foreach ($overrides as $pluginOverride) {
                if (isset($pluginOverride['extendQueryParams'])) {
                    QueryHelper::applyExtendParams($qb, $pluginOverride['extendQueryParams']);
                }
            }
        }

        return $this->executeAll($qb) ?: [];
    }
}