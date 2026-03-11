<?php

declare(strict_types=1);

namespace App\Frontend\Db;

use Magepattern\Component\Database\QueryBuilder;

class ShareDb extends BaseDb
{
    /**
     * Récupère tous les réseaux de partage actifs pour le frontend
     */
    public function getActiveNetworks(): array
    {
        $qb = new QueryBuilder();
        $qb->select(['name', 'url_share', 'icon'])
            ->from('mc_share_network')
            ->where('is_active = 1')
            ->orderBy('order_share', 'ASC');

        return $this->executeAll($qb) ?: [];
    }
}