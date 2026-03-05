<?php

declare(strict_types=1);

namespace App\Backend\Controller;

use App\Backend\Db\EmployeeDb;
use Magepattern\Component\HTTP\Request;
use Magepattern\Component\Tool\FormTool;
// --- IMPORT DES OUTILS DE SÉCURITÉ ---
use Magepattern\Component\Security\PasswordTool;
use Magepattern\Component\Security\RSATool;

class EmployeeController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

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
        $db = new EmployeeDb();

        $targetColumns = ['id_admin', 'firstname_admin', 'lastname_admin', 'email_admin', 'role_name', 'active_admin'];

        $rawScheme = $db->getTableScheme('mc_admin_employee');
        $rawScheme[] = ['column' => 'role_name', 'type' => 'varchar(50)'];

        $associations = [
            'id_admin'        => ['title' => 'ID', 'type' => 'text', 'class' => 'text-center text-muted small px-2'],
            'firstname_admin' => ['title' => 'Prénom', 'type' => 'text', 'class' => 'fw-bold'],
            'lastname_admin'  => ['title' => 'Nom', 'type' => 'text', 'class' => 'fw-bold'],
            'email_admin'     => ['title' => 'E-mail', 'type' => 'text', 'class' => 'text-muted small'],
            'role_name'       => ['title' => 'Rôle', 'type' => 'text', 'class' => 'text-center text-uppercase small font-monospace'],
            'active_admin'    => ['title' => 'Actif', 'type' => 'bin', 'class' => 'text-center px-3', 'enum' => 'status_']
        ];

        $this->getScheme($rawScheme, $targetColumns, $associations);

        $page   = Request::isGet('page') ? (int)$_GET['page'] : 1;
        $limit  = Request::isGet('offset') ? (int)$_GET['offset'] : 25;
        $search = $_GET['search'] ?? [];

        $result = $db->fetchAllEmployees($page, $limit, $search);

        if ($result !== false) {
            $this->getItems('employee_list', $result['data'], true, $result['meta']);
        }

        $this->view->assign([
            'idcolumn'   => 'id_admin',
            'hashtoken'  => $this->session->getToken(),
            'get_search' => $search,
            'sortable'   => false,
            'checkbox'   => true,
            'edit'       => true,
            'dlt'        => true
        ]);

        $this->view->display('employee/index.tpl');
    }

    public function add(): void
    {
        if (Request::isMethod('POST')) {
            $this->processAdd();
            return;
        }

        $db = new EmployeeDb();
        $this->view->assign([
            'roles'     => $db->fetchAllRoles(),
            'hashtoken' => $this->session->getToken()
        ]);

        $this->view->display('employee/add.tpl');
    }

    private function processAdd(): void
    {
        $token = $_POST['hashtoken'] ?? '';
        if (!$this->session->validateToken($token)) {
            $this->jsonResponse(false, 'Session expirée.');
        }

        $email = FormTool::simpleClean($_POST['email_admin'] ?? '');
        $db = new EmployeeDb();

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->jsonResponse(false, 'L\'adresse e-mail est invalide.');
        }

        if ($db->emailExists($email)) {
            $this->jsonResponse(false, 'Cet e-mail est déjà utilisé par un autre employé.');
        }

        $password = $_POST['passwd_admin'] ?? '';
        if (empty($password)) {
            $this->jsonResponse(false, 'Le mot de passe est obligatoire.');
        }

        // Vérification de la robustesse avec l'outil de sécurité
        if (!PasswordTool::checkStrength($password, 6)) {
            $this->jsonResponse(false, 'Le mot de passe doit contenir au moins 6 caractères (incluant majuscule, minuscule, chiffre et caractère spécial).');
        }

        $data = [
            'keyuniqid_admin' => RSATool::uniqID(32),
            'title_admin'     => in_array($_POST['title_admin'] ?? 'm', ['m', 'w']) ? $_POST['title_admin'] : 'm',
            'firstname_admin' => FormTool::simpleClean($_POST['firstname_admin'] ?? ''),
            'lastname_admin'  => FormTool::simpleClean($_POST['lastname_admin'] ?? ''),
            'pseudo_admin'    => FormTool::simpleClean($_POST['pseudo_admin'] ?? ''),
            'email_admin'     => $email,
            'phone_admin'     => FormTool::simpleClean($_POST['phone_admin'] ?? ''),
            'address_admin'   => FormTool::simpleClean($_POST['address_admin'] ?? ''),
            'postcode_admin'  => FormTool::simpleClean($_POST['postcode_admin'] ?? ''),
            'city_admin'      => FormTool::simpleClean($_POST['city_admin'] ?? ''),
            'country_admin'   => FormTool::simpleClean($_POST['country_admin'] ?? ''),
            'passwd_admin'    => PasswordTool::hash($password),
            'active_admin'    => (int)($_POST['active_admin'] ?? 0)
        ];

        $newId = $db->insertEmployee($data);

        if ($newId) {
            $idRole = (int)($_POST['id_role'] ?? 0);
            $db->syncEmployeeRole($newId, $idRole);

            $this->jsonResponse(true, 'Employé créé avec succès.', ['type' => 'add', 'id' => $newId]);
        } else {
            $this->jsonResponse(false, 'Erreur lors de la création de l\'employé.');
        }
    }

    public function edit(): void
    {
        $id = (int)($_REQUEST['edit'] ?? $_POST['id_admin'] ?? 0);
        $db = new EmployeeDb();

        if (Request::isMethod('POST')) {
            $this->processEdit($db, $id);
            return;
        }

        $employee = $db->fetchEmployeeById($id);
        if (!$employee) return;

        $this->view->assign([
            'employee'  => $employee,
            'roles'     => $db->fetchAllRoles(),
            'hashtoken' => $this->session->getToken()
        ]);

        $this->view->display('employee/edit.tpl');
    }

    private function processEdit(EmployeeDb $db, int $id): void
    {
        $token = $_POST['hashtoken'] ?? '';
        if (!$this->session->validateToken($token)) {
            $this->jsonResponse(false, 'Session expirée.');
        }

        $email = FormTool::simpleClean($_POST['email_admin'] ?? '');

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->jsonResponse(false, 'L\'adresse e-mail est invalide.');
        }

        if ($db->emailExists($email, $id)) {
            $this->jsonResponse(false, 'Cet e-mail est déjà utilisé par un autre employé.');
        }

        // On NE MET PAS 'keyuniqid_admin' ici pour ne pas casser la session en cours
        $data = [
            'title_admin'     => in_array($_POST['title_admin'] ?? 'm', ['m', 'w']) ? $_POST['title_admin'] : 'm',
            'firstname_admin' => FormTool::simpleClean($_POST['firstname_admin'] ?? ''),
            'lastname_admin'  => FormTool::simpleClean($_POST['lastname_admin'] ?? ''),
            'pseudo_admin'    => FormTool::simpleClean($_POST['pseudo_admin'] ?? ''),
            'email_admin'     => $email,
            'phone_admin'     => FormTool::simpleClean($_POST['phone_admin'] ?? ''),
            'address_admin'   => FormTool::simpleClean($_POST['address_admin'] ?? ''),
            'postcode_admin'  => FormTool::simpleClean($_POST['postcode_admin'] ?? ''),
            'city_admin'      => FormTool::simpleClean($_POST['city_admin'] ?? ''),
            'country_admin'   => FormTool::simpleClean($_POST['country_admin'] ?? ''),
            'active_admin'    => (int)($_POST['active_admin'] ?? 0)
        ];

        // Modification du mot de passe UNIQUEMENT s'il est renseigné dans le formulaire
        $password = $_POST['passwd_admin'] ?? '';
        if (!empty($password)) {
            // Vérification de la robustesse
            if (!PasswordTool::checkStrength($password, 6)) {
                $this->jsonResponse(false, 'Le nouveau mot de passe est trop faible (inclure majuscule, minuscule, chiffre et caractère spécial).');
            }
            // Utilisation de PasswordTool pour le hachage
            $data['passwd_admin'] = PasswordTool::hash($password);
            $data['last_change_admin'] = date('Y-m-d H:i:s');
        }

        if ($db->updateEmployee($id, $data)) {
            $idRole = (int)($_POST['id_role'] ?? 0);
            $db->syncEmployeeRole($id, $idRole);

            $this->jsonResponse(true, 'Employé mis à jour avec succès.', ['type' => 'update']);
        } else {
            $this->jsonResponse(false, 'Erreur lors de la mise à jour.');
        }
    }

    public function delete(): void
    {
        $ids = $_POST['ids'] ?? [$_POST['id'] ?? null];
        $cleanIds = array_filter(array_map('intval', (array)$ids));

        if (!empty($cleanIds)) {
            if (in_array(1, $cleanIds)) {
                $this->jsonResponse(false, 'Impossible de supprimer le super-administrateur principal (ID 1).');
            }

            if ((new EmployeeDb())->deleteEmployees($cleanIds)) {
                $this->jsonResponse(true, 'Employé(s) supprimé(s).');
            }
        }
        $this->jsonResponse(false, 'Erreur lors de la suppression.');
    }
}