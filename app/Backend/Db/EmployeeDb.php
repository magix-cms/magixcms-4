<?php

declare(strict_types=1);

namespace App\Backend\Db;

use Magepattern\Component\Database\QueryBuilder;

class EmployeeDb extends BaseDb
{
    /**
     * Récupère la liste paginée des employés pour le tableau de bord
     */
    public function fetchAllEmployees(int $page = 1, int $limit = 25, array $search = []): array|false
    {
        $qb = new QueryBuilder();

        $qb->select([
            'e.id_admin',
            'e.firstname_admin',
            'e.lastname_admin',
            'e.email_admin',
            'e.active_admin',
            'r.role_name'
        ])
            ->from('mc_admin_employee', 'e')
            ->leftJoin('mc_admin_access_rel', 'rel', 'e.id_admin = rel.id_admin')
            ->leftJoin('mc_admin_role_user', 'r', 'rel.id_role = r.id_role');

        if (!empty($search['email_admin'])) {
            $qb->where('e.email_admin LIKE :search', ['search' => '%' . $search['email_admin'] . '%']);
        }

        if (!empty($search['lastname_admin'])) {
            $qb->where('e.lastname_admin LIKE :search2', ['search2' => '%' . $search['lastname_admin'] . '%']);
        }

        $qb->orderBy('e.id_admin', 'DESC');

        return $this->executePaginatedQuery($qb, $page, $limit);
    }

    /**
     * Récupère un employé par son ID
     */
    public function fetchEmployeeById(int $id): array|false
    {
        $qb = new \Magepattern\Component\Database\QueryBuilder();
        $qb->select([
            'e.*',
            'rel.id_role',
            'r.role_name'
        ])
            ->from('mc_admin_employee', 'e')
            ->leftJoin('mc_admin_access_rel', 'rel', 'e.id_admin = rel.id_admin')
            ->leftJoin('mc_admin_role_user', 'r', 'rel.id_role = r.id_role')
            ->where('e.id_admin = :id', ['id' => $id]);

        return $this->executeRow($qb);
    }

    /**
     * Vérifie si un e-mail existe déjà (pour éviter les doublons)
     */
    public function emailExists(string $email, int $excludeId = 0): bool
    {
        $qb = new QueryBuilder();
        $qb->select(['id_admin'])->from('mc_admin_employee')->where('email_admin = :email', ['email' => $email]);

        if ($excludeId > 0) {
            $qb->where('id_admin != :id', ['id' => $excludeId]);
        }

        return (bool)$this->executeRow($qb);
    }

    /**
     * Insère un nouvel employé
     */
    public function insertEmployee(array $data): int|false
    {
        $qb = new QueryBuilder();
        $qb->insert('mc_admin_employee', $data);

        if ($this->executeInsert($qb)) {
            return $this->getLastInsertId();
        }
        return false;
    }

    /**
     * Met à jour un employé
     */
    public function updateEmployee(int $id, array $data): bool
    {
        $qb = new QueryBuilder();
        $qb->update('mc_admin_employee', $data)->where('id_admin = :id', ['id' => $id]);
        return $this->executeUpdate($qb);
    }

    /**
     * Supprime un ou plusieurs employés
     */
    public function deleteEmployees(array $ids): bool
    {
        if (empty($ids)) return false;

        // On empêche de supprimer l'admin principal (ID 1 par sécurité absolue)
        $ids = array_filter($ids, fn($id) => (int)$id !== 1);
        if (empty($ids)) return false;

        // 1. Nettoyage des liaisons de rôles
        $qbRel = new QueryBuilder();
        $qbRel->delete('mc_admin_access_rel')->whereIn('id_admin', $ids);
        $this->executeDelete($qbRel);

        // 2. Nettoyage des sessions actives
        $qbSess = new QueryBuilder();
        $qbSess->delete('mc_admin_session')->whereIn('id_admin', $ids);
        $this->executeDelete($qbSess);

        // 3. Suppression de l'employé
        $qb = new QueryBuilder();
        $qb->delete('mc_admin_employee')->whereIn('id_admin', $ids);
        return $this->executeDelete($qb);
    }

    // ==========================================================
    // GESTION DES RÔLES
    // ==========================================================

    /**
     * Récupère tous les rôles disponibles
     */
    public function fetchAllRoles(): array
    {
        $qb = new QueryBuilder();
        $qb->select('*')->from('mc_admin_role_user')->orderBy('role_name', 'ASC');
        return $this->executeAll($qb) ?: [];
    }

    /**
     * Lie un employé à un rôle
     */
    public function syncEmployeeRole(int $idAdmin, int $idRole): bool
    {
        // 1. Nettoyage
        $qbDel = new QueryBuilder();
        $qbDel->delete('mc_admin_access_rel')->where('id_admin = :id', ['id' => $idAdmin]);
        $this->executeDelete($qbDel);

        // 2. Insertion
        if ($idRole > 0) {
            $qbIn = new QueryBuilder();
            $qbIn->insert('mc_admin_access_rel', [
                'id_admin' => $idAdmin,
                'id_role'  => $idRole
            ]);
            return $this->executeInsert($qbIn);
        }
        return true;
    }
    // ==========================================================
    // GESTION DES PERMISSIONS (RBAC)
    // ==========================================================

    /**
     * Vérifie si l'administrateur a accès à un module spécifique.
     * Retourne les permissions sous forme de tableau associatif.
     */
    public function checkModuleAccess(int $idAdmin, string $moduleName): array|false
    {
        $qb = new QueryBuilder();

        $qb->select([
            'a.view',
            'a.append',
            'a.edit',
            'a.del',
            'a.action'
        ])
            ->from('mc_admin_access', 'a')
            ->join('mc_module', 'm', 'a.id_module = m.id_module')
            ->join('mc_admin_access_rel', 'rel', 'a.id_role = rel.id_role')
            // CORRECTION ICI : On retire LOWER() qui fait planter le QueryBuilder
            ->where('rel.id_admin = :id_admin AND m.name = :module', [
                'id_admin' => $idAdmin,
                'module'   => $moduleName
            ]);

        return $this->executeRow($qb);
    }
}