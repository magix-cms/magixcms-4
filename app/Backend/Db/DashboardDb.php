<?php

declare(strict_types=1);

namespace App\Backend\Db;

use Magepattern\Component\Database\QueryBuilder;

class DashboardDb extends BaseDb
{
    /**
     * Met à jour l'ordre des widgets pour un administrateur spécifique
     */
    public function updateWidgetsOrder(int $idAdmin, array $order): bool
    {
        // 1. On nettoie l'ancienne configuration pour repartir sur du propre
        $qbDel = new QueryBuilder();
        $qbDel->delete('mc_admin_dashboard')->where('id_admin = :id', ['id' => $idAdmin]);
        $this->executeDelete($qbDel);

        // 2. On insère le nouvel ordre
        foreach ($order as $widget) {
            $qbIn = new QueryBuilder();
            $qbIn->insert('mc_admin_dashboard', [
                'id_admin'    => $idAdmin,
                'widget_name' => $widget['name'], // ex: MagixGuestbook
                'position'    => (int)$widget['pos']
            ]);
            $this->executeInsert($qbIn);
        }

        return true;
    }

    /**
     * Récupère les préférences d'affichage de l'admin
     * Retourne un tableau indexé par le nom du widget pour un tri facile
     */
    public function fetchWidgetsOrder(int $idAdmin): array
    {
        $qb = new QueryBuilder();
        $qb->select('*')
            ->from('mc_admin_dashboard')
            ->where('id_admin = :id', ['id' => $idAdmin])
            ->orderBy('position', 'ASC');

        $rows = $this->executeAll($qb);
        $order = [];

        if ($rows) {
            foreach ($rows as $row) {
                $order[$row['widget_name']] = (int)$row['position'];
            }
        }
        return $order;
    }
    /**
     * Supprime un widget de tous les dashboards (utile lors d'une désinstallation)
     */
    public function removeWidgetGlobally(string $widgetName): bool
    {
        $qb = new QueryBuilder();
        $qb->delete('mc_admin_dashboard')->where('widget_name = :name', ['name' => $widgetName]);
        return $this->executeDelete($qb);
    }
}