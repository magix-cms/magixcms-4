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

        // Note : Pas de cache SQL forcé ici car cette page n'est chargée qu'une fois
        // avant d'être mise en cache par Smarty de manière définitive.
        $res = $this->executeRow($qb);

        if ($res) {
            $overrides = HookManager::triggerFilter('extendNewsData', ['news' => $res]);
            return $overrides['news'] ?? $res;
        }

        return false;
    }

    /**
     * Récupère les images secondaires (galerie)
     */
    public function getNewsImages(int $idNews, int $idLang): array
    {
        $qb = new QueryBuilder();
        $qb->select([
            'i.name_img',
            'ic.alt_img',
            'ic.title_img'
        ])
            ->from('mc_news_img', 'i')
            ->leftJoin('mc_news_img_content', 'ic', 'i.id_img = ic.id_img AND ic.id_lang = ' . (int)$idLang)
            ->where('i.id_news = :id', ['id' => $idNews])
            ->where('i.default_img = 0')
            ->orderBy('i.order_img', 'ASC');

        return $this->executeAll($qb) ?: [];
    }

    /**
     * Récupère les tags liés à une actualité
     */
    public function getNewsTags(int $idNews, int $idLang): array
    {
        $qb = new QueryBuilder();
        $qb->select(['t.id_tag', 't.name_tag'])
            ->from('mc_news_tag_rel', 'tr')
            ->join('mc_news_tag', 't', 'tr.id_tag = t.id_tag AND t.id_lang = ' . (int)$idLang)
            ->where('tr.id_news = :id', ['id' => $idNews])
            ->orderBy('t.name_tag', 'ASC');

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
            ->groupBy('year, month')
            ->orderBy('year', 'DESC')
            ->orderBy('month', 'DESC');

        return $this->executeAll($qb) ?: [];
    }

    /**
     * Récupère la liste des actualités paginée avec cache SQL
     */
    /**
     * MOTEUR DE LISTING GÉNÉRIQUE (Root, Tags, Archives...)
     */
    public function getNewsList(int $idLang, array $filters = []): array
    {
        // 1. Initialisation du cache SQL natif
        $cache = $this->getSqlCache();

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

        // Filtrage par TAG
        if (!empty($filters['tag'])) {
            $qb->join('mc_news_tag_rel', 'tr', 'n.id_news = tr.id_news');
            $qb->where('tr.id_tag = :id_tag', ['id_tag' => $filters['tag']]);
        }
        // Filtrage par ANNÉE
        if (!empty($filters['year'])) {
            $qb->where('YEAR(n.date_publish) = :year', ['year' => $filters['year']]);
        }
        // Filtrage par MOIS
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

        // GESTION DE LA PAGINATION
        $currentPage = $filters['page'] ?? 1;
        $itemsPerPage = $filters['limit'] ?? 12;

        //  LA CORRECTION EST ICI :
        // On injecte la page et la limite dans les paramètres du hash pour créer
        // des fichiers de cache totalement distincts.
        $hashParams = $qb->getParams();
        $hashParams['hash_page'] = $currentPage;
        $hashParams['hash_limit'] = $itemsPerPage;

        // 2. Génération de la clé avec les nouveaux paramètres sécurisés
        $cacheKey = $cache->generateKey($qb->getSql(), $hashParams, 'news_list');
        $cachedData = $cache->get($cacheKey);

        if ($cachedData !== null) {
            return $cachedData;
        }

        // 3. Si pas en cache, on exécute la pagination
        $paginator = new PaginationTool((int)$itemsPerPage, (int)$currentPage);
        $paginationInfo = $paginator->paginate($qb);

        $finalData = [
            'items' => $this->executeAll($qb) ?: [],
            'pagination' => $paginationInfo
        ];

        // On enregistre dans le cache
        $cache->set($cacheKey, $finalData, 3600);

        return $finalData;
    }
    /**
     * Récupère la configuration et le contenu SEO de la page d'accueil des Actualités
     */
    public function getNewsHomeConfig(int $idLang): array
    {
        $cache = $this->getSqlCache();
        $qb = new QueryBuilder();

        //  CORRECTION : On utilise la même structure stricte que HomeDb
        $qb->select(['h.*', 'c.*'])
            ->from('mc_news_home', 'h')
            ->join('mc_news_home_content', 'c', 'h.id_news_home = c.id_news_home')
            ->where('c.id_lang = :lang AND c.published = 1', ['lang' => $idLang])
            ->limit(1);

        // Maintenant $qb->getParams() contient bien ['lang' => $idLang]
        $cacheKey = $cache->generateKey($qb->getSql(), $qb->getParams(), 'news_list');

        $data = $cache->get($cacheKey);

        if ($data === null) {
            // Le cache est vide, on interroge la BDD
            $data = $this->executeRow($qb);

            //  CORRECTION : On ne met en cache QUE si on a trouvé des données !
            // Cela évite de bloquer la page sur un cache "vide" si on oublie de publier dans le backend.
            if ($data !== false) {
                $cache->set($cacheKey, $data, 3600);
            } else {
                return []; // On retourne un tableau vide proprement sans l'enregistrer dans le cache
            }
        }

        return $data;
    }
    /**
     * Récupère uniquement les évènements pour le calendrier (Ultra-léger)
     */
    /**
     * Récupère uniquement les évènements pour le calendrier (Ultra-léger)
     */
    public function getCalendarEvents(int $idLang, int $year, int $month): array
    {
        $cache = $this->getSqlCache();
        $qb = new QueryBuilder();

        $qb->select([
            'n.id_news',
            'n.date_event_start AS date_start',
            'n.date_event_end AS date_end',
            'n.date_publish', //  AJOUT : Nécessaire pour construire l'URL
            'nc.name_news AS title',
            'nc.url_news AS slug'
        ])
            ->from('mc_news', 'n')
            ->join('mc_news_content', 'nc', 'n.id_news = nc.id_news AND nc.id_lang = ' . (int)$idLang)
            ->where('nc.published_news = 1')
            ->where('n.date_publish <= NOW()')
            ->where('n.date_event_start IS NOT NULL')
            ->where('YEAR(n.date_event_start) = :year', ['year' => $year])
            ->where('MONTH(n.date_event_start) = :month', ['month' => $month])
            ->orderBy('n.date_event_start', 'ASC');

        //  Astuce : Changez en "v3" pour forcer le vidage du cache
        $cacheKey = $cache->generateKey($qb->getSql(), $qb->getParams(), 'calendar_events_v3');
        $cachedData = $cache->get($cacheKey);

        if ($cachedData !== null) {
            return $cachedData;
        }

        $events = $this->executeAll($qb) ?: [];

        $cache->set($cacheKey, $events, 3600);

        return $events;
    }
}