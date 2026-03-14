<?php

declare(strict_types=1);

namespace App\Backend\Db;

use Magepattern\Component\Database\QueryBuilder;

class ImageConfigDb extends BaseDb
{
    /**
     * Récupère toutes les configurations, triées par module et taille
     */
    /**
     * Récupère toutes les configurations, triées par module et taille
     */
    public function getAllConfigs(): array
    {
        $qb = new QueryBuilder();
        $qb->select('*')
            ->from('mc_config_img')
            // 🟢 CORRECTION : On utilise la syntaxe standard du QueryBuilder
            ->orderBy('module_img', 'ASC');

        // Note : Si votre QueryBuilder supporte le chaînage, vous pouvez faire :
        // ->orderBy('module_img', 'ASC')
        // ->orderBy('attribute_img', 'ASC')

        return $this->executeAll($qb) ?: [];
    }

    /**
     * Récupère une configuration spécifique pour l'édition
     */
    public function getConfigById(int $id): array|false
    {
        $qb = new QueryBuilder();
        $qb->select('*')
            ->from('mc_config_img')
            ->where('id_config_img = :id', ['id' => $id]);

        return $this->executeRow($qb);
    }

    /**
     * Sauvegarde ou met à jour une configuration
     */
    public function saveConfig(array $data, int $id = 0): bool
    {
        $qb = new QueryBuilder();

        if ($id > 0) {
            $qb->update('mc_config_img', $data)
                ->where('id_config_img = ' . $id);
            return $this->executeUpdate($qb);
        } else {
            $qb->insert('mc_config_img', $data);
            return $this->executeInsert($qb);
        }
    }

    /**
     * Supprime une configuration
     */
    public function deleteConfig(int $id): bool
    {
        $qb = new QueryBuilder();
        $qb->delete('mc_config_img')
            ->where('id_config_img = ' . $id);

        return $this->executeDelete($qb);
    }
}