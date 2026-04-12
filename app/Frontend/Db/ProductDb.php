<?php

declare(strict_types=1);

namespace App\Frontend\Db;

use Magepattern\Component\Database\QueryBuilder;
use Magepattern\Component\Database\QueryHelper;
use App\Component\Hook\HookManager;
use Magepattern\Component\Tool\PaginationTool;

class ProductDb extends BaseDb
{
    /**
     * Récupère la fiche complète d'un produit spécifique avec Override (extendProductData)
     */
    public function getProductPage(int $idProduct, int $idLang): array|false
    {
        $cache = $this->getSqlCache();
        $qb = new QueryBuilder();

        $qb->select([
            'p.*',
            'pc.*',
            'def_cat.id_cat AS default_id_cat',
            'def_cat_c.url_cat AS default_url_cat',
            'def_cat_c.name_cat',
            'i.name_img',
            'ic.alt_img',
            'ic.title_img'
        ])
            ->from('mc_catalog_product', 'p')
            ->join('mc_catalog_product_content', 'pc', 'p.id_product = pc.id_product AND pc.id_lang = ' . (int)$idLang)
            ->leftJoin('mc_catalog', 'def_link', 'p.id_product = def_link.id_product AND def_link.default_c = 1')
            ->leftJoin('mc_catalog_cat', 'def_cat', 'def_link.id_cat = def_cat.id_cat')
            ->leftJoin('mc_catalog_cat_content', 'def_cat_c', 'def_cat.id_cat = def_cat_c.id_cat AND def_cat_c.id_lang = ' . (int)$idLang)
            ->leftJoin('mc_catalog_product_img', 'i', 'p.id_product = i.id_product AND i.default_img = 1')
            ->leftJoin('mc_catalog_product_img_content', 'ic', 'i.id_img = ic.id_img AND ic.id_lang = ' . (int)$idLang)
            ->where('p.id_product = :id', ['id' => $idProduct])
            ->where('pc.published_p = 1');

        $overrides = HookManager::triggerFilter('extendProductData', []);
        if (!empty($overrides)) {
            foreach ($overrides as $pluginOverride) {
                if (isset($pluginOverride['extendQueryParams'])) {
                    QueryHelper::applyExtendParams($qb, $pluginOverride['extendQueryParams']);
                }
            }
        }

        $cacheKey = $cache->generateKey($qb->getSql(), $qb->getParams(), 'product');
        $cachedData = $cache->get($cacheKey);

        if ($cachedData !== null) {
            return $cachedData;
        }

        $res = $this->executeRow($qb);

        if ($res !== false) {
            $cache->set($cacheKey, $res, 3600);
        }

        return $res;
    }

    /**
     * MOTEUR GÉNÉRIQUE DE LISTING PRODUITS avec Override (extendProductList)
     */
    public function getProductList(int $idLang, array $filters = []): array
    {
        $cache = $this->getSqlCache();
        $qb = new QueryBuilder();

        $qb->select([
            'p.*',
            'pc.*',
            'def_cat.id_cat AS default_id_cat',
            'def_cat_c.url_cat AS default_url_cat',
            'def_cat_c.name_cat',
            'i.name_img',
            'ic.alt_img',
            'ic.title_img'
        ])
            ->from('mc_catalog_product', 'p')
            ->join('mc_catalog_product_content', 'pc', 'p.id_product = pc.id_product AND pc.id_lang = ' . (int)$idLang)
            ->leftJoin('mc_catalog', 'def_link', 'p.id_product = def_link.id_product AND def_link.default_c = 1')
            ->leftJoin('mc_catalog_cat', 'def_cat', 'def_link.id_cat = def_cat.id_cat')
            ->leftJoin('mc_catalog_cat_content', 'def_cat_c', 'def_cat.id_cat = def_cat_c.id_cat AND def_cat_c.id_lang = ' . (int)$idLang)
            ->leftJoin('mc_catalog_product_img', 'i', 'p.id_product = i.id_product AND i.default_img = 1')
            ->leftJoin('mc_catalog_product_img_content', 'ic', 'i.id_img = ic.id_img AND ic.id_lang = ' . (int)$idLang)
            ->where('pc.published_p = 1');

        if (!empty($filters['id_cat'])) {
            $qb->join('mc_catalog', 'cat_rel', 'p.id_product = cat_rel.id_product');
            $qb->where('cat_rel.id_cat = :id_cat', ['id_cat' => $filters['id_cat']]);
        }

        $overrides = HookManager::triggerFilter('extendProductList', []);
        if (!empty($overrides)) {
            foreach ($overrides as $pluginOverride) {
                if (isset($pluginOverride['extendQueryParams'])) {
                    QueryHelper::applyExtendParams($qb, $pluginOverride['extendQueryParams']);
                }
            }
        }

        if (!empty($filters['order_by'])) {
            $qb->orderBy($filters['order_by'], $filters['order_dir'] ?? 'ASC');
        } else {
            $qb->orderBy('p.id_product', 'DESC');
        }

        if (!empty($filters['limit'])) {
            if (isset($filters['offset'])) {
                $qb->limit((int)$filters['limit'], (int)$filters['offset']);
            } else {
                $qb->limit((int)$filters['limit']);
            }
        }

        // 🟢 Paramètres spécifiques pour le Hash du Cache
        $hashParams = $qb->getParams();
        $hashParams['hash_limit'] = $filters['limit'] ?? 0;
        $hashParams['hash_offset'] = $filters['offset'] ?? 0;

        $cacheKey = $cache->generateKey($qb->getSql(), $hashParams, 'product');
        $cachedData = $cache->get($cacheKey);

        if ($cachedData !== null) {
            return $cachedData;
        }

        $res = $this->executeAll($qb) ?: [];
        $cache->set($cacheKey, $res, 3600);

        return $res;
    }

    /**
     * @param int $idLang
     * @param array $filters
     * @return array
     */
    public function getPaginatedProductList(int $idLang, array $filters = []): array
    {
        $cache = $this->getSqlCache();
        $qb = new QueryBuilder();

        $qb->select([
            'p.*', 'pc.*',
            'def_cat.id_cat AS default_id_cat', 'def_cat_c.url_cat AS default_url_cat', 'def_cat_c.name_cat',
            'i.name_img', 'ic.alt_img', 'ic.title_img'
        ])
            ->from('mc_catalog_product', 'p')
            ->join('mc_catalog_product_content', 'pc', 'p.id_product = pc.id_product AND pc.id_lang = ' . (int)$idLang)
            ->leftJoin('mc_catalog', 'def_link', 'p.id_product = def_link.id_product AND def_link.default_c = 1')
            ->leftJoin('mc_catalog_cat', 'def_cat', 'def_link.id_cat = def_cat.id_cat')
            ->leftJoin('mc_catalog_cat_content', 'def_cat_c', 'def_cat.id_cat = def_cat_c.id_cat AND def_cat_c.id_lang = ' . (int)$idLang)
            ->leftJoin('mc_catalog_product_img', 'i', 'p.id_product = i.id_product AND i.default_img = 1')
            ->leftJoin('mc_catalog_product_img_content', 'ic', 'i.id_img = ic.id_img AND ic.id_lang = ' . (int)$idLang)
            ->where('pc.published_p = 1');

        if (!empty($filters['id_cat'])) {
            $qb->join('mc_catalog', 'cat_rel', 'p.id_product = cat_rel.id_product');
            $qb->where('cat_rel.id_cat = :id_cat', ['id_cat' => $filters['id_cat']]);
        }

        $overrides = HookManager::triggerFilter('extendProductList', []);
        if (!empty($overrides)) {
            foreach ($overrides as $pluginOverride) {
                if (isset($pluginOverride['extendQueryParams'])) {
                    QueryHelper::applyExtendParams($qb, $pluginOverride['extendQueryParams']);
                }
            }
        }

        $qb->orderBy($filters['order_by'] ?? 'p.id_product', $filters['order_dir'] ?? 'DESC');

        $page  = max(1, (int)($filters['page'] ?? 1));
        $limit = max(1, (int)($filters['limit'] ?? 20));

        // 🟢 SÉCURITÉ CACHE : Injecter la page et limite
        $hashParams = $qb->getParams();
        $hashParams['hash_page'] = $page;
        $hashParams['hash_limit'] = $limit;

        $cacheKey = $cache->generateKey($qb->getSql(), $hashParams, 'product');
        $cachedData = $cache->get($cacheKey);

        if ($cachedData !== null) {
            return $cachedData;
        }

        $paginationTool = new PaginationTool($limit, $page);
        $paginationData = $paginationTool->paginate($qb);

        $items = $this->executeAll($qb) ?: [];

        $finalData = [
            'items'      => $items,
            'pagination' => $paginationData
        ];

        $cache->set($cacheKey, $finalData, 3600);

        return $finalData;
    }

    /**
     * Récupère une liste de produits par IDs avec Override (extendProductList)
     */
    public function getProductsByIds(array $ids, int $idLang): array
    {
        if (empty($ids)) return [];

        $cache = $this->getSqlCache();
        $qb = new QueryBuilder();
        $idsString = implode(',', array_map('intval', $ids));

        $qb->select([
            'p.*',
            'pc.*',
            'def_cat.id_cat AS default_id_cat',
            'def_cat_c.url_cat AS default_url_cat',
            'def_cat_c.name_cat',
            'i.name_img',
            'ic.alt_img',
            'ic.title_img'
        ])
            ->from('mc_catalog_product', 'p')
            ->join('mc_catalog_product_content', 'pc', 'p.id_product = pc.id_product AND pc.id_lang = ' . (int)$idLang)
            ->leftJoin('mc_catalog', 'def_link', 'p.id_product = def_link.id_product AND def_link.default_c = 1')
            ->leftJoin('mc_catalog_cat', 'def_cat', 'def_link.id_cat = def_cat.id_cat')
            ->leftJoin('mc_catalog_cat_content', 'def_cat_c', 'def_cat.id_cat = def_cat_c.id_cat AND def_cat_c.id_lang = ' . (int)$idLang)
            ->leftJoin('mc_catalog_product_img', 'i', 'p.id_product = i.id_product AND i.default_img = 1')
            ->leftJoin('mc_catalog_product_img_content', 'ic', 'i.id_img = ic.id_img AND ic.id_lang = ' . (int)$idLang)
            ->where("p.id_product IN ({$idsString})")
            ->where('pc.published_p = 1');

        $overrides = HookManager::triggerFilter('extendProductList', []);
        if (!empty($overrides)) {
            foreach ($overrides as $pluginOverride) {
                if (isset($pluginOverride['extendQueryParams'])) {
                    QueryHelper::applyExtendParams($qb, $pluginOverride['extendQueryParams']);
                }
            }
        }

        $cacheKey = $cache->generateKey($qb->getSql(), $qb->getParams(), 'product');
        $cachedData = $cache->get($cacheKey);

        if ($cachedData !== null) {
            return $cachedData;
        }

        $results = $this->executeAll($qb) ?: [];

        $orderedResults = [];
        $indexedResults = array_column($results, null, 'id_product');

        foreach ($ids as $id) {
            if (isset($indexedResults[$id])) {
                $orderedResults[] = $indexedResults[$id];
            }
        }

        $cache->set($cacheKey, $orderedResults, 3600);

        return $orderedResults;
    }

    /**
     * Récupère la galerie d'images complète du produit
     */
    public function getProductImages(int $idProduct, int $idLang): array
    {
        $cache = $this->getSqlCache();
        $qb = new QueryBuilder();

        $qb->select(['i.name_img', 'i.default_img', 'ic.alt_img', 'ic.title_img', 'ic.caption_img'])
            ->from('mc_catalog_product_img', 'i')
            ->leftJoin('mc_catalog_product_img_content', 'ic', 'i.id_img = ic.id_img AND ic.id_lang = ' . (int)$idLang)
            ->where('i.id_product = :id', ['id' => $idProduct])
            ->orderBy('i.order_img', 'ASC');

        $cacheKey = $cache->generateKey($qb->getSql(), $qb->getParams(), 'product');
        $cachedData = $cache->get($cacheKey);

        if ($cachedData !== null) {
            return $cachedData;
        }

        $res = $this->executeAll($qb) ?: [];
        $cache->set($cacheKey, $res, 3600);

        return $res;
    }
}