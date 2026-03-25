<?php

declare(strict_types=1);

namespace App\Frontend\Db;

use Magepattern\Component\Database\QueryBuilder;
use Magepattern\Component\Database\QueryHelper;
use App\Component\Hook\HookManager; // 🟢 Import indispensable pour l'override

class PagesDb extends BaseDb
{
    /**
     * Récupère la page principale avec Override
     */
    public function getPagesPage(int $idPages, int $idLang): array|false
    {
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

        // 🟢 OVERRIDE : Un plugin peut ajouter des champs à la page (ex: p.is_restricted)
        $overrides = HookManager::triggerFilter('extendPagesData', []);
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
     * Récupère la galerie d'images
     */
    public function getPagesImages(int $idPages, int $idLang): array
    {
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

        return $this->executeAll($qb) ?: [];
    }

    /**
     * Récupère les pages enfants avec Override
     */
    public function getPagesChildren(int $parentId, int $idLang): array
    {
        $qb = new QueryBuilder();
        $qb->select([
            'p.id_pages',
            'p.id_parent',
            'c.name_pages',
            'c.resume_pages',
            'c.content_pages',
            'c.url_pages',
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

        // 🟢 OVERRIDE : Pour les listes de pages (widgets, menus enfants...)
        $overrides = HookManager::triggerFilter('extendPagesList', []);
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
     * Récupère une liste de pages par leurs IDs (ex: pour le plugin MagixFeaturedPages)
     * Conserve l'ordre du tableau d'IDs fourni.
     */
    public function getPagesByIds(array $pageIds, int $idLang): array
    {
        if (empty($pageIds)) {
            return [];
        }

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

        // 🟢 ASTUCE SQL : ORDER BY FIELD permet de respecter scrupuleusement l'ordre des IDs
        $qb->orderBy('FIELD(p.id_pages, ' . implode(',', array_map('intval', $pageIds)) . ')');

        // 🟢 OVERRIDE : On garde le même hook que pour les autres listes
        $overrides = HookManager::triggerFilter('extendPagesList', []);
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