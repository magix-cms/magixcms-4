<?php

declare(strict_types=1);

namespace App\Frontend\Db;

use Magepattern\Component\Database\QueryBuilder;
use Magepattern\Component\Database\QueryHelper;
use App\Component\Hook\HookManager;

class NewsDb extends BaseDb
{
    /**
     * Récupère UNE seule actualité complète (avec Override)
     */
    public function getNewsPage(int $idNews, int $idLang): array|false
    {
        $qb = new QueryBuilder();
        $qb->select([
            'n.*',
            'nc.*',
            'i.name_img',
            'ic.alt_img',
            'ic.title_img'
        ])
            ->from('mc_news', 'n')
            ->join('mc_news_content', 'nc', 'n.id_news = nc.id_news AND nc.id_lang = ' . (int)$idLang)
            ->leftJoin('mc_news_img', 'i', 'n.id_news = i.id_news AND i.default_img = 1')
            ->leftJoin('mc_news_img_content', 'ic', 'i.id_img = ic.id_img AND ic.id_lang = ' . (int)$idLang)
            ->where('n.id_news = :id', ['id' => $idNews])
            ->where('nc.published_news = 1')
            ->where('n.date_publish <= NOW()');

        $overrides = HookManager::triggerFilter('extendNewsData', []);
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
     * MOTEUR DE LISTING GÉNÉRIQUE (Root, Tags, Archives...)
     */
    public function getNewsList(int $idLang, array $filters = []): array
    {
        $qb = new QueryBuilder();
        $qb->select([
            'n.*',
            'nc.*',
            'i.name_img',
            'ic.alt_img',
            'ic.title_img'
        ])
            ->from('mc_news', 'n')
            ->join('mc_news_content', 'nc', 'n.id_news = nc.id_news AND nc.id_lang = ' . (int)$idLang)
            ->leftJoin('mc_news_img', 'i', 'n.id_news = i.id_news AND i.default_img = 1')
            ->leftJoin('mc_news_img_content', 'ic', 'i.id_img = ic.id_img AND ic.id_lang = ' . (int)$idLang)
            ->where('nc.published_news = 1')
            ->where('n.date_publish <= NOW()');

        // 🟢 FILTRES DYNAMIQUES
        // 1. Filtrage par TAG
        if (!empty($filters['id_tag'])) {
            $qb->join('mc_news_tag_rel', 'tr', 'n.id_news = tr.id_news');
            $qb->where('tr.id_tag = :id_tag', ['id_tag' => $filters['id_tag']]);
        }

        // 2. Filtrage par ANNÉE
        if (!empty($filters['year'])) {
            $qb->where('YEAR(n.date_publish) = :year', ['year' => $filters['year']]);
        }

        // 3. Filtrage par MOIS
        if (!empty($filters['month'])) {
            $qb->where('MONTH(n.date_publish) = :month', ['month' => $filters['month']]);
        }

        // 🟢 OVERRIDE (Filtre ouvert pour les plugins)
        $overrides = HookManager::triggerFilter('extendNewsList', []);
        if (!empty($overrides)) {
            foreach ($overrides as $pluginOverride) {
                if (isset($pluginOverride['extendQueryParams'])) {
                    QueryHelper::applyExtendParams($qb, $pluginOverride['extendQueryParams']);
                }
            }
        }

        // Tri et Limite
        $qb->orderBy('n.date_publish', 'DESC');

        if (!empty($filters['limit'])) {
            $offset = $filters['offset'] ?? 0;
            $qb->limit((int)$filters['limit'], (int)$offset);
        }

        return $this->executeAll($qb) ?: [];
    }

    /**
     * Récupère les tags associés à une news précise
     */
    public function getNewsTags(int $idNews, int $idLang): array
    {
        $qb = new QueryBuilder();
        $qb->select(['t.id_tag', 't.name_tag'])
            ->from('mc_news_tag', 't')
            ->join('mc_news_tag_rel', 'tr', 't.id_tag = tr.id_tag')
            ->where('tr.id_news = :id', ['id' => $idNews])
            ->where('t.id_lang = :lang', ['lang' => $idLang]);

        return $this->executeAll($qb) ?: [];
    }

    public function getNewsImages(int $idNews, int $idLang): array
    {
        $qb = new QueryBuilder();
        $qb->select(['i.name_img', 'ic.alt_img', 'ic.title_img', 'ic.caption_img'])
            ->from('mc_news_img', 'i')
            ->leftJoin('mc_news_img_content', 'ic', 'i.id_img = ic.id_img AND ic.id_lang = ' . (int)$idLang)
            ->where('i.id_news = :id', ['id' => $idNews])
            ->orderBy('i.order_img', 'ASC');

        return $this->executeAll($qb) ?: [];
    }
}