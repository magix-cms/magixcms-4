<?php

declare(strict_types=1);

namespace App\Frontend\Db;

use Magepattern\Component\Database\QueryBuilder;
use Magepattern\Component\Database\QueryHelper;
use App\Component\Hook\HookManager;
use Magepattern\Component\Tool\PaginationTool;

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

        // OVERRIDE
        $overrides = HookManager::triggerFilter('extendNewsList', []);
        if (!empty($overrides)) {
            foreach ($overrides as $pluginOverride) {
                if (isset($pluginOverride['extendQueryParams'])) {
                    QueryHelper::applyExtendParams($qb, $pluginOverride['extendQueryParams']);
                }
            }
        }

        // Tri
        $qb->orderBy('n.date_publish', 'DESC');

        // 🟢 GESTION DE LA PAGINATION
        $currentPage = $filters['page'] ?? 1;
        // On récupère la limite depuis la config globale (mc_config.news_limit) ou on met 12 par défaut
        $itemsPerPage = $filters['limit'] ?? 12;

        $paginator = new PaginationTool((int)$itemsPerPage, (int)$currentPage);

        // La méthode paginate() modifie le $qb (ajoute LIMIT/OFFSET) et retourne les infos
        $paginationInfo = $paginator->paginate($qb);

        return [
            'items' => $this->executeAll($qb) ?: [],
            'pagination' => $paginationInfo
        ];
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
    /**
     * Récupère tous les tags actifs pour alimenter le filtre
     */
    public function getAllTags(int $idLang): array
    {
        $qb = new QueryBuilder();
        $qb->select(['id_tag', 'name_tag'])
            ->from('mc_news_tag')
            ->where('id_lang = :lang', ['lang' => $idLang])
            ->orderBy('name_tag', 'ASC');

        return $this->executeAll($qb) ?: [];
    }

    /**
     * Récupère le nom d'un tag spécifique (utile pour le titre SEO)
     */
    public function getTagName(int $idTag, int $idLang): string
    {
        $qb = new QueryBuilder();
        $qb->select(['name_tag'])
            ->from('mc_news_tag')
            ->where('id_tag = :id', ['id' => $idTag])
            ->where('id_lang = :lang', ['lang' => $idLang]);

        $res = $this->executeRow($qb);
        return $res ? $res['name_tag'] : '';
    }

    /**
     * Récupère les années et mois où il y a des publications
     */
    public function getArchives(): array
    {
        $qb = new QueryBuilder();
        $qb->select([
            'YEAR(date_publish) AS year',
            'MONTH(date_publish) AS month',
            'COUNT(id_news) AS count_news'
        ])
            ->from('mc_news')
            ->where('date_publish <= NOW()')
            // GroupBy génère l'arbre des archives sans doublons
            ->groupBy('YEAR(date_publish), MONTH(date_publish)')
            ->orderBy('year', 'DESC')
            ->orderBy('month', 'DESC');

        return $this->executeAll($qb) ?: [];
    }
}