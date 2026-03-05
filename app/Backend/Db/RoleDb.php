<?php

declare(strict_types=1);

namespace App\Backend\Db;

use Magepattern\Component\Database\QueryBuilder;

class RoleDb extends BaseDb
{
    /**
     * Récupère tous les rôles (pour le listing)
     */
    public function fetchAllRoles(): array|false
    {
        $qb = new QueryBuilder();
        $qb->select('*')->from('mc_admin_role_user')->orderBy('id_role', 'ASC');
        return $this->executeAll($qb);
    }

    /**
     * Récupère tous les modules disponibles dans le système
     */
    public function fetchAllModules(): array
    {
        $qb = new QueryBuilder();
        $qb->select('*')->from('mc_module')->orderBy('name', 'ASC');
        return $this->executeAll($qb) ?: [];
    }

    /**
     * Récupère la matrice des permissions pour un rôle spécifique
     * Indexé par id_module pour faciliter l'affichage
     */
    public function fetchPermissionsByRole(int $idRole): array
    {
        $qb = new QueryBuilder();
        $qb->select('*')->from('mc_admin_access')->where('id_role = :id', ['id' => $idRole]);

        $rows = $this->executeAll($qb);
        $permissions = [];

        if ($rows) {
            foreach ($rows as $row) {
                $permissions[(int)$row['id_module']] = $row;
            }
        }
        return $permissions;
    }

    /**
     * Enregistre la matrice complète pour un rôle
     */
    public function savePermissions(int $idRole, array $matrix): bool
    {
        // 1. On vide les anciens accès pour ce rôle
        $qbDel = new QueryBuilder();
        $qbDel->delete('mc_admin_access')->where('id_role = :id', ['id' => $idRole]);
        $this->executeDelete($qbDel);

        // 2. On insère les nouveaux accès
        foreach ($matrix as $idModule => $rights) {
            $qbIn = new QueryBuilder();
            $qbIn->insert('mc_admin_access', [
                'id_role'   => $idRole,
                'id_module' => (int)$idModule,
                'view'      => (int)($rights['view'] ?? 0),
                'append'    => (int)($rights['append'] ?? 0),
                'edit'      => (int)($rights['edit'] ?? 0),
                'del'       => (int)($rights['del'] ?? 0),
                'action'    => (int)($rights['action'] ?? 0)
            ]);
            $this->executeInsert($qbIn);
        }
        return true;
    }
    /**
     * Crée un nouveau rôle dans la base de données
     */
    public function insertRole(string $roleName): int|false
    {
        $qb = new QueryBuilder();
        $qb->insert('mc_admin_role_user', [
            'role_name' => $roleName
        ]);

        if ($this->executeInsert($qb)) {
            return $this->getLastInsertId();
        }
        return false;
    }
}