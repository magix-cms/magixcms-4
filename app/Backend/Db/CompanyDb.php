<?php

declare(strict_types=1);

namespace App\Backend\Db;

use Magepattern\Component\Database\QueryBuilder;

class CompanyDb extends BaseDb
{
    /**
     * Récupère toutes les infos sous forme de tableau associatif [ 'email' => '...', 'phone' => '...' ]
     * Idéal pour injecter directement dans Smarty
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
                $config[$row['name_info']] = $row['value_info'];
            }
        }

        return $config;
    }

    /**
     * Met à jour une valeur spécifique
     */
    public function updateInfo(string $key, ?string $value): bool
    {
        $qb = new QueryBuilder();
        $qb->update('mc_company_info', ['value_info' => $value])
            ->where('name_info = :name', ['name' => $key]);

        return $this->executeUpdate($qb);
    }

    /**
     * Met à jour tout un tableau de configuration d'un coup
     */
    public function updateAllInfos(array $data): void
    {
        foreach ($data as $key => $value) {
            // On nettoie un peu si nécessaire
            $cleanValue = trim((string)$value);
            $this->updateInfo($key, $cleanValue);
        }
    }
}