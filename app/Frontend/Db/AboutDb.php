<?php

declare(strict_types=1);

namespace App\Frontend\Db;

use Magepattern\Component\Database\QueryBuilder;
use Magepattern\Component\Database\QueryHelper;
use App\Component\Hook\HookManager;

class AboutDb extends BaseDb
{
    /**
     * Récupère une page "About" spécifique avec son image par défaut
     */
    public function getAboutPage(int $id, int $idLang): ?array
    {
        $cache = $this->getSqlCache();
        $qb = new QueryBuilder();

        $qb->select('a.*, ac.*, img.name_img, imgc.alt_img, imgc.title_img, imgc.caption_img')
            ->from('mc_about', 'a')
            // Concaténation stricte d'un entier (sécurisé) pour garder la souplesse du LEFT JOIN
            ->leftJoin('mc_about_content', 'ac', 'a.id_about = ac.id_about AND ac.id_lang = ' . (int)$idLang)
            ->leftJoin('mc_about_img', 'img', 'a.id_about = img.id_about AND img.default_img = 1')
            ->leftJoin('mc_about_img_content', 'imgc', 'img.id_img = imgc.id_img AND imgc.id_lang = ' . (int)$idLang)
            ->where('a.id_about = :id AND ac.published_about = 1', ['id' => $id]);

        // 🟢 OVERRIDE : Un plugin peut ajouter des champs AVANT la mise en cache
        $overrides = HookManager::triggerFilter('extendAboutData', []);
        if (!empty($overrides)) {
            foreach ($overrides as $pluginOverride) {
                if (isset($pluginOverride['extendQueryParams'])) {
                    QueryHelper::applyExtendParams($qb, $pluginOverride['extendQueryParams']);
                }
            }
        }

        // 🟢 CACHE SQL : Clé unique avec le tag 'about'
        $cacheKey = $cache->generateKey($qb->getSql(), $qb->getParams(), 'about');
        $cachedData = $cache->get($cacheKey);

        if ($cachedData !== null) {
            return $cachedData;
        }

        $res = $this->executeRow($qb);

        if ($res) {
            $cache->set($cacheKey, $res, 3600);
            return $res;
        }

        return null;
    }

    /**
     * Récupère toutes les images d'une page (Galerie)
     */
    public function getAboutImages(int $idAbout, int $idLang): array
    {
        $cache = $this->getSqlCache();
        $qb = new QueryBuilder();

        $qb->select('img.*, imgc.*')
            ->from('mc_about_img', 'img')
            ->leftJoin('mc_about_img_content', 'imgc', 'img.id_img = imgc.id_img AND imgc.id_lang = ' . (int)$idLang)
            ->where('img.id_about = :id', ['id' => $idAbout])
            ->orderBy('img.order_img', 'ASC');

        $cacheKey = $cache->generateKey($qb->getSql(), $qb->getParams(), 'about');
        $data = $cache->get($cacheKey);

        if ($data !== null) {
            return $data;
        }

        $res = $this->executeAll($qb) ?: [];
        $cache->set($cacheKey, $res, 3600);

        return $res;
    }

    /**
     * Récupère les pages "About" enfants d'un parent spécifique
     */
    public function getAboutChildren(int $idParent, int $idLang): array
    {
        $cache = $this->getSqlCache();
        $qb = new QueryBuilder();

        $qb->select('a.*, ac.*, img.name_img, imgc.alt_img, imgc.title_img')
            ->from('mc_about', 'a')
            ->leftJoin('mc_about_content', 'ac', 'a.id_about = ac.id_about AND ac.id_lang = ' . (int)$idLang)
            ->leftJoin('mc_about_img', 'img', 'a.id_about = img.id_about AND img.default_img = 1')
            ->leftJoin('mc_about_img_content', 'imgc', 'img.id_img = imgc.id_img AND imgc.id_lang = ' . (int)$idLang)
            ->where('a.id_parent = :id AND ac.published_about = 1', ['id' => $idParent])
            ->orderBy('a.order_about', 'ASC');

        $overrides = HookManager::triggerFilter('extendAboutList', []);
        if (!empty($overrides)) {
            foreach ($overrides as $pluginOverride) {
                if (isset($pluginOverride['extendQueryParams'])) {
                    QueryHelper::applyExtendParams($qb, $pluginOverride['extendQueryParams']);
                }
            }
        }

        $cacheKey = $cache->generateKey($qb->getSql(), $qb->getParams(), 'about');
        $data = $cache->get($cacheKey);

        if ($data !== null) {
            return $data;
        }

        $res = $this->executeAll($qb) ?: [];
        $cache->set($cacheKey, $res, 3600);

        return $res;
    }
}