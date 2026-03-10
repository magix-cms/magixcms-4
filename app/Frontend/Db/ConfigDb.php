<?php

declare(strict_types=1);

namespace App\Frontend\Db;

use Magepattern\Component\Database\QueryBuilder;

class ConfigDb extends BaseDb
{
    /**
     * Récupère la configuration d'activation des modules
     */
    public function getGlobalConfig(): array
    {
        $qb = new QueryBuilder();
        $qb->select('*')->from('mc_config');
        $results = $this->executeAll($qb);

        $configs = [];
        if ($results) {
            foreach ($results as $row) {
                $configs[$row['attr_name']] = (int)$row['status'];
            }
        }
        return $configs;
    }
}