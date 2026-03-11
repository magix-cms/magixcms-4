<?php

declare(strict_types=1);

namespace App\Backend\Db;

use Magepattern\Component\Database\QueryBuilder;

class ShareDb extends BaseDb
{
    /**
     * Récupère la liste paginée des réseaux pour le tableau de bord
     */
    public function fetchAllAdminNetworks(int $page = 1, int $limit = 25, array $search = []): array|false
    {
        $qb = new QueryBuilder();
        $qb->select('*')->from('mc_share_network');

        // Tri par défaut : Ordre défini, puis par nom
        $qb->orderBy('order_share', 'ASC')
            ->orderBy('name', 'ASC');

        return $this->executePaginatedQuery($qb, $page, $limit);
    }

    /**
     * Récupère un réseau par son ID
     */
    public function fetchNetworkById(int $id): array|false
    {
        $qb = new QueryBuilder();
        $qb->select('*')
            ->from('mc_share_network')
            ->where('id_share = :id', ['id' => $id]);

        return $this->executeRow($qb);
    }

    /**
     * Ajoute un nouveau réseau
     */
    public function insertNetwork(array $data): int|false
    {
        $qb = new QueryBuilder();
        $qb->insert('mc_share_network', $data);

        if ($this->executeInsert($qb)) {
            return $this->getLastInsertId();
        }
        return false;
    }

    /**
     * Met à jour un réseau existant
     */
    public function updateNetwork(int $id, array $data): bool
    {
        $qb = new QueryBuilder();
        $qb->update('mc_share_network', $data)
            ->where('id_share = :id', ['id' => $id]);

        return $this->executeUpdate($qb);
    }

    /**
     * Supprime un ou plusieurs réseaux
     */
    public function deleteNetwork(array $ids): bool
    {
        if (empty($ids)) return false;

        $qb = new QueryBuilder();
        $qb->delete('mc_share_network')
            ->whereIn('id_share', $ids);

        return $this->executeDelete($qb);
    }
}