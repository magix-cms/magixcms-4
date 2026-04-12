<?php

declare(strict_types=1);

namespace App\Frontend\Db;

use Magepattern\Component\Database\QueryBuilder;
use App\Frontend\Db\BaseDb;

class HomeDb extends BaseDb
{
    /**
     * Récupère le contenu de la page d'accueil pour une langue précise
     */
    public function getHomeDataByLang(int $idLang): array|false
    {
        // 1. On appelle l'outil de cache SQL natif du Frontend
        $cache = $this->getSqlCache();

        $qb = new QueryBuilder();
        $qb->select(['p.*', 'c.*'])
            ->from('mc_home_page', 'p')
            ->join('mc_home_page_content', 'c', 'p.id_page = c.id_page')
            ->where('c.id_lang = :lang AND c.published = 1', ['lang' => $idLang])
            ->limit(1);

        // 2. On génère une clé unique basée sur la requête, avec le tag "homepage"
        $cacheKey = $cache->generateKey($qb->getSql(), $qb->getParams(), 'homepage');

        // 3. On regarde si la donnée est déjà en cache
        $data = $cache->get($cacheKey);

        if ($data === null) {
            // Le cache est vide : on interroge la base de données
            $data = $this->executeRow($qb);

            // On sauvegarde le résultat en cache (par exemple pour 24h = 86400s)
            // On ne met en cache que si on a vraiment un tableau (pas false)
            if ($data !== false) {
                $cache->set($cacheKey, $data, 86400);
            }
        }

        return $data;
    }
}