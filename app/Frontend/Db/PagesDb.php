<?php

declare(strict_types=1);

namespace App\Frontend\Db;

use Magepattern\Component\Database\QueryBuilder;
use Magepattern\Component\Database\QueryHelper;
use App\Component\Hook\HookManager;

class PagesDb extends BaseDb
{
    /**
     * Récupère la page principale avec Override
     */
    public function getPagesPage(int $idPages, int $idLang): array|false
    {
        $cache = $this->getSqlCache();
        $qb = new QueryBuilder();

        $qb->select([
            'p.*',
            'c.*',
            'i.name_img',
            'ic.alt_img',
            'ic.title_img'
        ])
            ->from('mc_cms_page', 'p')
            ->join('mc_cms_page_content', 'c', 'p.id_pages = c.id_pages AND c.id_lang = ' . (int)$idLang)
            ->leftJoin('mc_cms_page_img', 'i', 'p.id_pages = i.id_pages AND i.default_img = 1')
            ->leftJoin('mc_cms_page_img_content', 'ic', 'i.id_img = ic.id_img AND ic.id_lang = ' . (int)$idLang)
            ->where('p.id_pages = :id', ['id' => $idPages])
            ->where('c.published_pages = 1');

        //  OVERRIDE : Un plugin peut ajouter des champs à la page (ex: p.is_restricted)
        $overrides = HookManager::triggerFilter('extendPagesData', []);
        if (!empty($overrides)) {
            foreach ($overrides as $pluginOverride) {
                if (isset($pluginOverride['extendQueryParams'])) {
                    QueryHelper::applyExtendParams($qb, $pluginOverride['extendQueryParams']);
                }
            }
        }

        //  CACHE SQL
        $cacheKey = $cache->generateKey($qb->getSql(), $qb->getParams(), 'pages');
        $cachedData = $cache->get($cacheKey);

        if ($cachedData !== null) {
            return $cachedData;
        }

        $res = $this->executeRow($qb);

        if ($res) {
            $cache->set($cacheKey, $res, 3600);
            return $res;
        }

        return false;
    }

    /**
     * Récupère la galerie d'images
     */
    public function getPagesImages(int $idPages, int $idLang): array
    {
        $cache = $this->getSqlCache();
        $qb = new QueryBuilder();

        $qb->select([
            'i.name_img',
            'ic.alt_img',
            'ic.title_img',
            'ic.caption_img'
        ])
            ->from('mc_cms_page_img', 'i')
            ->leftJoin('mc_cms_page_img_content', 'ic', 'i.id_img = ic.id_img AND ic.id_lang = ' . (int)$idLang)
            ->where('i.id_pages = :id', ['id' => $idPages])
            ->orderBy('i.order_img', 'ASC');

        $cacheKey = $cache->generateKey($qb->getSql(), $qb->getParams(), 'pages');
        $data = $cache->get($cacheKey);

        if ($data !== null) {
            return $data;
        }

        $res = $this->executeAll($qb) ?: [];
        $cache->set($cacheKey, $res, 3600);

        return $res;
    }

    /**
     * Récupère les pages enfants avec Override
     */
    /**
     * Récupère les pages enfants avec Override
     */
    public function getPagesChildren(int $parentId, int $idLang): array
    {
        $cache = $this->getSqlCache();
        $qb = new QueryBuilder();

        //  CORRECTION : On sélectionne TOUTES les colonnes (p.*, c.*)
        // pour que PagesPresenter ne génère pas de tableaux "null"
        $qb->select([
            'p.*',
            'c.*',
            'i.name_img',
            'ic.alt_img',
            'ic.title_img'
        ])
            ->from('mc_cms_page', 'p')
            ->join('mc_cms_page_content', 'c', 'p.id_pages = c.id_pages AND c.id_lang = ' . (int)$idLang)
            ->leftJoin('mc_cms_page_img', 'i', 'p.id_pages = i.id_pages AND i.default_img = 1')
            ->leftJoin('mc_cms_page_img_content', 'ic', 'i.id_img = ic.id_img AND ic.id_lang = ' . (int)$idLang)
            ->where('p.id_parent = :parent', ['parent' => $parentId])
            ->where('c.published_pages = 1')
            ->orderBy('p.order_pages', 'ASC');

        //  OVERRIDE
        $overrides = HookManager::triggerFilter('extendPagesList', []);
        if (!empty($overrides)) {
            foreach ($overrides as $pluginOverride) {
                if (isset($pluginOverride['extendQueryParams'])) {
                    QueryHelper::applyExtendParams($qb, $pluginOverride['extendQueryParams']);
                }
            }
        }

        $cacheKey = $cache->generateKey($qb->getSql(), $qb->getParams(), 'pages');
        $data = $cache->get($cacheKey);

        if ($data !== null) {
            return $data;
        }

        $res = $this->executeAll($qb) ?: [];
        $cache->set($cacheKey, $res, 3600);

        return $res;
    }

    /**
     * Récupère une liste de pages par leurs IDs (ex: pour le plugin MagixFeaturedPages)
     */
    public function getPagesByIds(array $pageIds, int $idLang): array
    {
        if (empty($pageIds)) {
            return [];
        }

        $cache = $this->getSqlCache();
        $qb = new QueryBuilder();

        $qb->select([
            'p.*',
            'c.*',
            'i.name_img',
            'ic.alt_img',
            'ic.title_img'
        ])
            ->from('mc_cms_page', 'p')
            ->join('mc_cms_page_content', 'c', 'p.id_pages = c.id_pages AND c.id_lang = ' . (int)$idLang)
            ->leftJoin('mc_cms_page_img', 'i', 'p.id_pages = i.id_pages AND i.default_img = 1')
            ->leftJoin('mc_cms_page_img_content', 'ic', 'i.id_img = ic.id_img AND ic.id_lang = ' . (int)$idLang)
            ->where('p.id_pages IN (' . implode(',', array_map('intval', $pageIds)) . ')')
            ->where('c.published_pages = 1');

        $qb->orderBy('FIELD(p.id_pages, ' . implode(',', array_map('intval', $pageIds)) . ')');

        $overrides = HookManager::triggerFilter('extendPagesList', []);
        if (!empty($overrides)) {
            foreach ($overrides as $pluginOverride) {
                if (isset($pluginOverride['extendQueryParams'])) {
                    QueryHelper::applyExtendParams($qb, $pluginOverride['extendQueryParams']);
                }
            }
        }

        $cacheKey = $cache->generateKey($qb->getSql(), $qb->getParams(), 'pages');
        $data = $cache->get($cacheKey);

        if ($data !== null) {
            return $data;
        }

        $res = $this->executeAll($qb) ?: [];
        $cache->set($cacheKey, $res, 3600);

        return $res;
    }
}