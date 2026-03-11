<?php

declare(strict_types=1);

namespace App\Backend\Controller;

use App\Backend\Db\ShareDb;
use Magepattern\Component\HTTP\Request;
use Magepattern\Component\Tool\FormTool;

class ShareController extends BaseController
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

    /**
     * Affiche la liste des réseaux de partage
     */
    private function index(): void
    {
        $db = new ShareDb();

        $targetColumns = ['id_share', 'name', 'icon', 'is_active', 'order_share'];
        $rawScheme = $db->getTableScheme('mc_share_network');

        // Configuration des colonnes pour la vue (getScheme)
        $associations = [
            'id_share'    => ['title' => 'ID', 'type' => 'text', 'class' => 'text-center text-muted small px-2'],
            'name'        => ['title' => 'Réseau', 'type' => 'text', 'class' => 'fw-bold w-50'],
            'icon'        => ['title' => 'Icône (Bootstrap)', 'type' => 'text', 'class' => 'text-muted'],
            'is_active'   => ['title' => 'Statut', 'type' => 'bin', 'class' => 'text-center px-3', 'enum' => 'status_'],
            'order_share' => ['title' => 'Ordre', 'type' => 'text', 'class' => 'text-center px-3']
        ];

        $this->getScheme($rawScheme, $targetColumns, $associations);

        $page   = Request::isGet('page') ? (int)$_GET['page'] : 1;
        $limit  = Request::isGet('offset') ? (int)$_GET['offset'] : 25;
        $search = $_GET['search'] ?? [];

        $result = $db->fetchAllAdminNetworks($page, $limit, $search);

        if ($result !== false) {
            $this->getItems('share_list', $result['data'], true, $result['meta']);
        }

        $this->view->assign([
            'idcolumn'   => 'id_share',
            'hashtoken'  => $this->session->getToken(),
            'get_search' => $search,
            'sortable'   => true, // On autorise le tri pour l'ordre d'affichage
            'checkbox'   => true,
            'edit'       => true,
            'dlt'        => true
        ]);

        $this->view->display('share/index.tpl'); // Dossier "share" à créer dans vos templates admin
    }

    /**
     * Ajout d'un réseau
     */
    public function add(): void
    {
        if (Request::isMethod('POST')) {
            $this->processAdd();
            return;
        }

        $this->view->assign([
            'hashtoken' => $this->session->getToken()
        ]);

        $this->view->display('share/add.tpl');
    }

    private function processAdd(): void
    {
        $token = $_POST['hashtoken'] ?? '';
        if (!$this->session->validateToken($token)) {
            $this->jsonResponse(false, 'Session expirée.');
        }

        $db = new ShareDb();
        $data = [
            'name'        => FormTool::simpleClean($_POST['name'] ?? ''),
            'icon'        => FormTool::simpleClean($_POST['icon'] ?? ''),
            'url_share'   => $_POST['url_share'] ?? '', // Pas de simpleClean ici, car l'URL contient des caractères spéciaux (? & %)
            'is_active'   => (int)($_POST['is_active'] ?? 1),
            'order_share' => (int)($_POST['order_share'] ?? 0)
        ];

        if (empty($data['name']) || empty($data['url_share'])) {
            $this->jsonResponse(false, 'Le nom et l\'URL de partage sont obligatoires.');
        }

        $newId = $db->insertNetwork($data);

        if ($newId) {
            $this->jsonResponse(true, 'Le réseau a été ajouté.', ['type' => 'add', 'id' => $newId]);
        } else {
            $this->jsonResponse(false, 'Erreur lors de la création du réseau.');
        }
    }

    /**
     * Édition d'un réseau
     */
    public function edit(): void
    {
        $id = (int)($_REQUEST['edit'] ?? $_POST['id_share'] ?? 0);
        $db = new ShareDb();

        if (Request::isMethod('POST')) {
            $this->processEdit($db, $id);
            return;
        }

        $network = $db->fetchNetworkById($id);
        if (!$network) return;

        $this->view->assign([
            'network_data' => $network,
            'hashtoken'    => $this->session->getToken()
        ]);

        $this->view->display('share/edit.tpl');
    }

    private function processEdit(ShareDb $db, int $id): void
    {
        $token = $_POST['hashtoken'] ?? '';
        if (!$this->session->validateToken($token)) {
            $this->jsonResponse(false, 'Session expirée.');
        }

        $data = [
            'name'        => FormTool::simpleClean($_POST['name'] ?? ''),
            'icon'        => FormTool::simpleClean($_POST['icon'] ?? ''),
            'url_share'   => $_POST['url_share'] ?? '',
            'is_active'   => (int)($_POST['is_active'] ?? 0),
            'order_share' => (int)($_POST['order_share'] ?? 0)
        ];

        if (empty($data['name']) || empty($data['url_share'])) {
            $this->jsonResponse(false, 'Le nom et l\'URL de partage sont obligatoires.');
        }

        if ($db->updateNetwork($id, $data)) {
            $this->jsonResponse(true, 'Le réseau a été mis à jour.', ['type' => 'update']);
        } else {
            $this->jsonResponse(false, 'Erreur lors de la mise à jour.');
        }
    }

    /**
     * Suppression d'un ou plusieurs réseaux
     */
    public function delete(): void
    {
        $ids = $_POST['ids'] ?? [$_POST['id'] ?? null];
        $cleanIds = array_filter(array_map('intval', (array)$ids));

        if (!empty($cleanIds)) {
            $db = new ShareDb();

            if ($db->deleteNetwork($cleanIds)) {
                $this->jsonResponse(true, 'Réseau(x) supprimé(s).');
            }
        }
        $this->jsonResponse(false, 'Erreur lors de la suppression.');
    }
}