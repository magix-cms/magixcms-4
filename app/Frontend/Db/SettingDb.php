<?php

declare(strict_types=1);

namespace App\Frontend\Db;

use Magepattern\Component\Database\QueryBuilder;

class SettingDb extends BaseDb
{
    /**
     * Récupère toutes les configurations et les indexe par la colonne `name`
     */
    public function fetchAllSettings(): array
    {
        $qb = new QueryBuilder();
        $qb->select('*')->from('mc_setting');
        $results = $this->executeAll($qb);

        $settings = [];
        if ($results) {
            foreach ($results as $row) {
                $settings[$row['name']] = $row;
            }
        }
        return $settings;
    }
    /**
     * Récupère l'URL du domaine marqué comme canonique
     */
    public function getCanonicalDomain(): ?string
    {
        $qb = new QueryBuilder();
        $qb->select(['url_domain'])
            ->from('mc_domain')
            ->where('canonical_domain = 1')
            ->limit(1);

        $result = $this->executeRow($qb);

        return $result ? $result['url_domain'] : null;
    }
}