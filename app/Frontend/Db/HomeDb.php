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
        $qb = new QueryBuilder();
        $qb->select(['p.*', 'c.*'])
            ->from('mc_home_page', 'p')
            ->join('mc_home_page_content', 'c', 'p.id_page = c.id_page')
            // 🟢 AJOUT CRUCIAL : "AND c.published = 1"
            ->where('c.id_lang = :lang AND c.published = 1', ['lang' => $idLang])
            ->limit(1);

        return $this->executeRow($qb);
    }
}