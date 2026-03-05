<?php

declare(strict_types=1);

namespace App\Backend\Db;

use Magepattern\Component\Database\QueryBuilder;

class ConfigDb extends BaseDb
{
    /**
     * Récupère toute la configuration globale sous forme de tableau associatif [clé => valeur]
     */
    public function getGlobalConfig(): array
    {
        $qb = new QueryBuilder();
        // Adaptez les noms des colonnes si votre table mc_config est légèrement différente
        $qb->select(['attr_name', 'status'])->from('mc_config');

        $rows = $this->executeAll($qb);
        $config = [];

        if ($rows) {
            foreach ($rows as $row) {
                // On cast le statut en entier (1 ou 0)
                $val = is_numeric($row['status']) ? (int)$row['status'] : $row['status'];
                // On utilise attr_name comme clé du tableau
                $config[$row['attr_name']] = $val;
            }
        }

        return $config;
    }
}