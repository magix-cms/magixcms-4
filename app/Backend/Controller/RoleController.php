<?php

declare(strict_types=1);

namespace App\Backend\Controller;

use App\Backend\Db\RoleDb;
use Magepattern\Component\HTTP\Request;
use Magepattern\Component\Tool\FormTool;

class RoleController extends BaseController
{
    public function run(): void
    {
        $action = $_GET['action'] ?? null;
        if ($action && method_exists($this, $action)) {
            $this->$action();
            return;
        }
        $this->index();
    }

    private function index(): void
    {
        $db = new RoleDb();
        $this->view->assign([
            'role_list' => $db->fetchAllRoles(),
            'hashtoken' => $this->session->getToken()
        ]);
        $this->view->display('role/index.tpl');
    }

    public function edit(): void
    {
        $idRole = (int)($_REQUEST['edit'] ?? 0);
        $db = new RoleDb();

        if (Request::isMethod('POST')) {
            $this->processSave($db, $idRole);
            return;
        }

        $this->view->assign([
            'id_role'     => $idRole,
            'modules'     => $db->fetchAllModules(),
            'permissions' => $db->fetchPermissionsByRole($idRole),
            'hashtoken'   => $this->session->getToken()
        ]);
        $this->view->display('role/edit.tpl');
    }

    private function processSave(RoleDb $db, int $idRole): void
    {
        $token = $_POST['hashtoken'] ?? '';
        if (!$this->session->validateToken($token)) {
            $this->jsonResponse(false, 'Session expirée.');
        }

        // On récupère la matrice envoyée (format: permissions[id_module][view])
        $matrix = $_POST['permissions'] ?? [];

        if ($db->savePermissions($idRole, $matrix)) {
            $this->jsonResponse(true, 'Permissions mises à jour.');
        } else {
            $this->jsonResponse(false, 'Erreur lors de l\'enregistrement.');
        }
    }
    public function add(): void
    {
        if (Request::isMethod('POST')) {
            $this->processAdd();
            return;
        }

        $db = new RoleDb();

        $this->view->assign([
            'modules'   => $db->fetchAllModules(),
            'hashtoken' => $this->session->getToken()
        ]);

        $this->view->display('role/add.tpl');
    }

    private function processAdd(): void
    {
        $token = $_POST['hashtoken'] ?? '';
        if (!$this->session->validateToken($token)) {
            $this->jsonResponse(false, 'Session expirée.');
        }

        $roleName = FormTool::simpleClean($_POST['role_name'] ?? '');

        if (empty($roleName)) {
            $this->jsonResponse(false, 'Veuillez indiquer un nom pour ce rôle.');
        }

        $db = new RoleDb();
        $newId = $db->insertRole($roleName);

        if ($newId) {
            // On enregistre les permissions cochées (s'il y en a)
            $matrix = $_POST['permissions'] ?? [];
            if (!empty($matrix)) {
                $db->savePermissions($newId, $matrix);
            }

            $this->jsonResponse(true, 'Le rôle a été créé avec succès.', ['type' => 'add', 'id' => $newId]);
        } else {
            $this->jsonResponse(false, 'Erreur lors de la création du rôle.');
        }
    }
}