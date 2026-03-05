<?php

declare(strict_types=1);

namespace App\Backend\Db;

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
                // On utilise le 'name' comme clé du tableau pour un accès direct dans Smarty
                $settings[$row['name']] = $row;
            }
        }
        return $settings;
    }

    /**
     * Met à jour la valeur d'une configuration spécifique
     */
    public function updateSetting(string $name, ?string $value): bool
    {
        $qb = new QueryBuilder();
        $qb->update('mc_setting', ['value' => $value])
            ->where('name = :name', ['name' => $name]);

        return $this->executeUpdate($qb);
    }
}