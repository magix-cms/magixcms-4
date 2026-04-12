<?php

declare(strict_types=1);

namespace App\Frontend\Db;

use Magepattern\Component\Database\QueryBuilder;

class CatalogDb extends BaseDb
{
    /**
     * Récupère les textes et le SEO de la page d'accueil du catalogue
     */
    public function getHomePage(int $idLang): array|false
    {
        $cache = $this->getSqlCache();
        $qb = new QueryBuilder();

        $qb->select(['h.*', 'hc.*'])
            ->from('mc_catalog_home', 'h')
            ->join('mc_catalog_home_content', 'hc', 'h.id_catalog_home = hc.id_catalog_home')
            ->where('hc.id_lang = :lang', ['lang' => $idLang])
            ->where('hc.published = 1')
            ->limit(1); // Il ne devrait y avoir qu'une seule page d'accueil active

        $cacheKey = $cache->generateKey($qb->getSql(), $qb->getParams(), 'catalog');
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
     * Récupère les catégories de premier niveau (Les grands rayons)
     */
    public function getRootCategories(int $idLang): array
    {
        $cache = $this->getSqlCache();
        $qb = new QueryBuilder();

        $qb->select([
            'c.id_cat', 'c.id_parent',
            'cc.name_cat', 'cc.resume_cat', 'cc.content_cat', 'cc.url_cat',
            'i.name_img', 'ic.alt_img', 'ic.title_img'
        ])
            ->from('mc_catalog_cat', 'c')
            ->join('mc_catalog_cat_content', 'cc', 'c.id_cat = cc.id_cat')
            ->leftJoin('mc_catalog_cat_img', 'i', 'c.id_cat = i.id_cat AND i.default_img = 1')
            // 🟢 CORRECTION : Utilisation de la concaténation stricte d'entier pour la jointure
            ->leftJoin('mc_catalog_cat_img_content', 'ic', 'i.id_img = ic.id_img AND ic.id_lang = ' . (int)$idLang)
            ->where('(c.id_parent IS NULL OR c.id_parent = 0)')
            ->where('cc.id_lang = :lang', ['lang' => $idLang])
            ->where('cc.published_cat = 1')
            ->orderBy('c.order_cat', 'ASC');

        $cacheKey = $cache->generateKey($qb->getSql(), $qb->getParams(), 'catalog');
        $cachedData = $cache->get($cacheKey);

        if ($cachedData !== null) {
            return $cachedData;
        }

        $res = $this->executeAll($qb) ?: [];
        $cache->set($cacheKey, $res, 3600);

        return $res;
    }
}