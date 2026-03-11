<?php

declare(strict_types=1);

namespace App\Frontend\Db;

use Magepattern\Component\Database\QueryBuilder;

class CompanyDb extends BaseDb
{
    /**
     * Récupère toutes les infos de l'entreprise sous forme de tableau associatif.
     * Ex: $config['name'] = 'Mon Super Site'
     */
    public function getCompanyInfo(): array
    {
        $qb = new QueryBuilder();
        $qb->select(['name_info', 'value_info'])
            ->from('mc_company_info');

        $results = $this->executeAll($qb);
        $config = [];

        if ($results) {
            foreach ($results as $row) {
                // Transforme les lignes en tableau clé => valeur
                $config[$row['name_info']] = $row['value_info'];
            }
        }

        return $config;
    }
}