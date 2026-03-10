<?php

declare(strict_types=1);

namespace App\Backend\Controller;

use App\Backend\Db\LayoutDb;
use Magepattern\Component\HTTP\Request;
use Magepattern\Component\Tool\FormTool;
use Magepattern\Component\Tool\SmartyTool;

class LayoutController extends BaseController
{
    protected LayoutDb $layoutDb;

    public function __construct()
    {
        parent::__construct();
        $this->layoutDb = new LayoutDb();
    }

    public function run(): void
    {
        $action = $_GET['action'] ?? 'index';
        if (method_exists($this, $action)) {
            $this->$action();
        } else {
            $this->index();
        }
    }

    public function index(): void
    {
        $view = SmartyTool::getInstance('admin');
        $hooks = $this->layoutDb->getAllHooks() ?: [];
        $fullLayout = [];

        foreach ($hooks as $hook) {
            $fullLayout[] = [
                'info' => $hook,
                'items' => $this->layoutDb->getItemsByHook((int)$hook['id_hook']) ?: []
            ];
        }

        $view->assign([
            'layout'    => $fullLayout,
            'hashtoken' => $this->session->getToken()
        ]);

        $view->display('layout/index.tpl');
    }

    public function add(): void
    {
        if (!Request::isMethod('POST')) $this->jsonResponse(false, 'Méthode non autorisée.');

        $token = $_POST['hashtoken'] ?? '';
        if (!$this->session->validateToken($token)) $this->jsonResponse(false, 'Jeton invalide.');

        $idHook = (int)($_POST['id_hook'] ?? 0);
        $moduleName = FormTool::simpleClean($_POST['module_name'] ?? '');

        if ($idHook > 0 && !empty($moduleName)) {
            if ($this->layoutDb->addItem($idHook, $moduleName)) {
                $this->jsonResponse(true, 'Widget greffé avec succès.');
            }
        }
        $this->jsonResponse(false, 'Erreur lors de l\'ajout.');
    }

    public function delete(): void
    {
        $token = $_GET['hashtoken'] ?? '';
        if (!$this->session->validateToken($token)) $this->jsonResponse(false, 'Jeton invalide.');

        $id = (int)($_GET['id'] ?? 0);
        if ($id > 0 && $this->layoutDb->deleteItem($id)) {
            $this->jsonResponse(true, 'Widget retiré.');
        }
        $this->jsonResponse(false, 'Erreur de suppression.');
    }

    public function toggle(): void
    {
        $token = $_GET['hashtoken'] ?? '';
        if (!$this->session->validateToken($token)) $this->jsonResponse(false, 'Jeton invalide.');

        $id = (int)($_GET['id'] ?? 0);
        if ($id > 0 && $this->layoutDb->toggleActive($id)) {
            $this->jsonResponse(true, 'Statut mis à jour.');
        }
        $this->jsonResponse(false, 'Erreur de mise à jour.');
    }

    public function move(): void
    {
        $token = $_GET['hashtoken'] ?? '';
        if (!$this->session->validateToken($token)) {
            $this->jsonResponse(false, 'Jeton invalide.');
        }

        $id = (int)($_GET['id'] ?? 0);
        $dir = $_GET['direction'] ?? '';

        if ($id > 0 && in_array($dir, ['up', 'down'])) {
            if ($this->layoutDb->moveItem($id, $dir)) {
                $this->jsonResponse(true, "Déplacement $dir effectué !");
            } else {
                // MODIFICATION ICI : On renvoie "true" pour que la page se recharge en silence,
                // sans afficher d'alerte d'erreur frustrante.
                $this->jsonResponse(true, "L'élément est déjà à cette extrémité.");
            }
        }
        $this->jsonResponse(false, "Action impossible.");
    }

    public function sort(): void
    {
        $token = $_POST['hashtoken'] ?? '';
        if (!$this->session->validateToken($token)) {
            $this->jsonResponse(false, 'Jeton invalide.');
        }

        $idHook = (int)($_POST['id_hook'] ?? 0);
        $ids = $_POST['order'] ?? [];

        if ($idHook > 0 && is_array($ids) && !empty($ids)) {
            // NOUVEAU : On passe l'ID de la zone à reorder pour qu'il mette à jour le hook
            if ($this->layoutDb->reorder($ids, $idHook)) {
                $this->jsonResponse(true, "L'ordre et les zones ont été mis à jour.");
            }
        }

        $this->jsonResponse(false, "Données invalides ou aucun changement.");
    }

}