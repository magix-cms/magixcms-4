<?php

declare(strict_types=1);

namespace App\Backend\Db;

use Magepattern\Component\Database\QueryBuilder;

class HolderDb extends BaseDb
{
    /**
     * Récupère toutes les configurations de tailles d'images
     */
    public function getAllImageConfigs(): array
    {
        $qb = new QueryBuilder();
        $qb->select('*')->from('mc_config_img');

        return $this->executeAll($qb) ?: [];
    }

    /**
     * Récupère la couleur de fond et le pourcentage du logo
     */
    public function getHolderSettings(): array
    {
        $qb = new QueryBuilder();
        $qb->select('name, value')
            ->from('mc_setting')
            ->where("name IN ('holder_bgcolor', 'logo_percent')");

        $results = $this->executeAll($qb);

        $settings = [
            'holder_bgcolor' => '#ffffff',
            'logo_percent'   => 50
        ];

        if ($results) {
            foreach ($results as $row) {
                $settings[$row['name']] = $row['value'];
            }
        }

        return $settings;
    }

    /**
     * 🟢 NOUVEAU : Récupère le nom du fichier du logo actif
     */
    public function getActiveLogo(): ?string
    {
        $qb = new QueryBuilder();
        $qb->select('img_logo')
            ->from('mc_logo')
            ->where('active_logo = 1');

        $result = $this->executeRow($qb);

        return $result['img_logo'] ?? null;
    }
}