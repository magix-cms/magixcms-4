<?php

declare(strict_types=1);

namespace App\Backend\Db;

use Magepattern\Component\Database\QueryBuilder;

class LayoutDb extends BaseDb
{
    public function getAllHooks(): array|false
    {
        $qb = new QueryBuilder();
        $qb->select(['*'])->from('mc_hook')->orderBy('name', 'ASC');
        return $this->executeAll($qb);
    }

    public function getItemsByHook(int $idHook): array|false
    {
        $qb = new QueryBuilder();
        $qb->select(['*'])
            ->from('mc_hook_item')
            ->where('id_hook = :id', ['id' => $idHook])
            ->orderBy('position', 'ASC');

        return $this->executeAll($qb);
    }

    private function updatePosition(int $idItem, int $pos, int $idHook = 0): bool
    {
        $qb = new QueryBuilder();
        $data = ['position' => $pos];

        // Si on a un ID de hook, on met à jour la zone en même temps
        if ($idHook > 0) {
            $data['id_hook'] = $idHook;
        }

        $qb->update('mc_hook_item', $data)
            ->where('id_item = :id', ['id' => $idItem]);

        return $this->executeUpdate($qb);
    }

    /**
     * @param array $ids Liste des IDs des widgets dans leur nouvel ordre
     * @param int $idHook ID de la zone cible
     */
    public function reorder(array $ids, int $idHook = 0): bool
    {
        // 1. Décalage temporaire pour éviter les conflits UNIQUE
        foreach ($ids as $id) {
            $idItem = (int)$id;
            if ($idItem <= 0) continue;

            // On le déplace temporairement, et on l'assigne tout de suite à sa nouvelle zone
            $this->updatePosition($idItem, 99000 + $idItem, $idHook);
        }

        // 2. Réindexation propre dans la nouvelle zone
        $pos = 1;
        $success = true;
        foreach ($ids as $id) {
            $idItem = (int)$id;
            if ($idItem <= 0) continue;

            if (!$this->updatePosition($idItem, $pos, $idHook)) {
                $success = false;
            }
            $pos++;
        }
        return $success;
    }

    public function addItem(int $idHook, string $moduleName): bool
    {
        $qbMax = new QueryBuilder();
        $qbMax->select(['MAX(position) as max_pos'])
            ->from('mc_hook_item')
            ->where('id_hook = :id', ['id' => $idHook]);

        $res = $this->executeRow($qbMax);
        $newPos = ($res['max_pos'] ?? 0) + 1;

        $qb = new QueryBuilder();
        $qb->insert('mc_hook_item', [
            'id_hook' => $idHook,
            'module_name' => $moduleName,
            'position' => $newPos,
            'active' => 1
        ]);
        return $this->executeInsert($qb);
    }

    public function toggleActive(int $idItem): bool
    {
        $qbCheck = new QueryBuilder();
        $qbCheck->select(['active'])->from('mc_hook_item')->where('id_item = :id', ['id' => $idItem]);
        $row = $this->executeRow($qbCheck);

        if ($row) {
            $qb = new QueryBuilder();
            $qb->update('mc_hook_item', ['active' => 1 - (int)$row['active']])
                ->where('id_item = :id', ['id' => $idItem]);
            return $this->executeUpdate($qb);
        }
        return false;
    }

    public function deleteItem(int $idItem): bool
    {
        $qb = new QueryBuilder();
        $qb->delete('mc_hook_item')->where('id_item = :id', ['id' => $idItem]);
        return $this->executeDelete($qb);
    }

    public function moveItem(int $idItem, string $direction): bool
    {
        // Nettoyage de la direction au cas où il y aurait un espace ('down ')
        $direction = trim($direction);

        $qbCurrent = new QueryBuilder();
        $qbCurrent->select(['id_hook', 'position'])->from('mc_hook_item')->where('id_item = :id', ['id' => $idItem]);
        $current = $this->executeRow($qbCurrent);
        if (!$current) return false;

        $idHook = (int)$current['id_hook'];
        $currentPos = (int)$current['position'];

        $qbNeighbor = new QueryBuilder();
        $qbNeighbor->select(['id_item', 'position'])->from('mc_hook_item')->where('id_hook = :id', ['id' => $idHook]);

        if ($direction === 'up') {
            $qbNeighbor->where('position < :pos', ['pos' => $currentPos])->orderBy('position', 'DESC');
        } else {
            $qbNeighbor->where('position > :pos', ['pos' => $currentPos])->orderBy('position', 'ASC');
        }

        $qbNeighbor->limit(1);
        $neighbor = $this->executeRow($qbNeighbor);

        if ($neighbor) {
            // Salle d'attente à 99999 (Compatible avec UNSIGNED)
            $this->updatePosition($idItem, 99999);
            $this->updatePosition((int)$neighbor['id_item'], $currentPos);
            $this->updatePosition($idItem, (int)$neighbor['position']);
            return true;
        }

        // Si tout échoue, on force une réindexation
        $this->forceReindex($idHook);
        return false;
    }

    private function forceReindex(int $idHook): void
    {
        $items = $this->getItemsByHook($idHook);
        if ($items) {
            $ids = array_column($items, 'id_item');
            $this->reorder($ids);
        }
    }
}