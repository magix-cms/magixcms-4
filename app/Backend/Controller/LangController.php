<?php

declare(strict_types=1);

namespace App\Backend\Controller;

use App\Backend\Db\LangDb;
use Magepattern\Component\HTTP\Request;
use Magepattern\Component\Tool\FormTool;
use Magepattern\Component\Tool\LocalizationTool;

class LangController extends BaseController
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
     * Affiche la liste des langues
     */
    private function index(): void
    {
        $db = new LangDb();

        $targetColumns = ['id_lang', 'name_lang', 'iso_lang', 'active_lang', 'default_lang'];
        $rawScheme = $db->getTableScheme('mc_lang');

        $associations = [
            'id_lang'      => ['title' => 'ID', 'type' => 'text', 'class' => 'text-center text-muted small px-2'],
            'name_lang'    => ['title' => 'Langue', 'type' => 'text', 'class' => 'fw-bold w-50'],
            'iso_lang'     => ['title' => 'Code ISO', 'type' => 'text', 'class' => 'text-center font-monospace'],
            'active_lang'  => ['title' => 'Statut', 'type' => 'bin', 'class' => 'text-center px-3', 'enum' => 'status_'],
            'default_lang' => ['title' => 'Défaut', 'type' => 'bin', 'class' => 'text-center px-3']
        ];

        $this->getScheme($rawScheme, $targetColumns, $associations);

        $page   = Request::isGet('page') ? (int)$_GET['page'] : 1;
        $limit  = Request::isGet('offset') ? (int)$_GET['offset'] : 25;
        $search = $_GET['search'] ?? [];

        $result = $db->fetchAllAdminLanguages($page, $limit, $search);

        if ($result !== false) {
            $this->getItems('lang_list', $result['data'], true, $result['meta']);
        }

        $this->view->assign([
            'idcolumn'   => 'id_lang',
            'hashtoken'  => $this->session->getToken(),
            'get_search' => $search,
            'sortable'   => false,
            'checkbox'   => true,
            'edit'       => true,
            'dlt'        => true
        ]);

        $this->view->display('lang/index.tpl');
    }

    /**
     * Ajout d'une langue
     */
    public function add(): void
    {
        if (Request::isMethod('POST')) {
            $this->processAdd();
            return;
        }

        // CORRECTION : Utilisation de votre LocalizationTool
        $availableLanguages = LocalizationTool::getLanguages('fr');

        $this->view->assign([
            'available_languages' => $availableLanguages,
            'hashtoken'           => $this->session->getToken()
        ]);

        $this->view->display('lang/add.tpl');
    }

    private function processAdd(): void
    {
        $token = $_POST['hashtoken'] ?? '';
        if (!$this->session->validateToken($token)) {
            $this->jsonResponse(false, 'Session expirée.');
        }

        $db = new LangDb();
        $data = [
            'name_lang'    => FormTool::simpleClean($_POST['name_lang'] ?? ''),
            'iso_lang'     => strtolower(FormTool::simpleClean($_POST['iso_lang'] ?? '')),
            'active_lang'  => (int)($_POST['active_lang'] ?? 0),
            'default_lang' => (int)($_POST['default_lang'] ?? 0)
        ];

        if (empty($data['name_lang']) || empty($data['iso_lang'])) {
            $this->jsonResponse(false, 'Le nom et le code ISO sont obligatoires.');
        }

        if ($data['default_lang'] === 1) {
            $data['active_lang'] = 1;
        }

        $newId = $db->insertLanguage($data);

        if ($newId) {
            if ($data['default_lang'] === 1) {
                $db->updateDefaultLanguage($newId);
            }
            $this->jsonResponse(true, 'La langue a été ajoutée.', ['type' => 'add', 'id' => $newId]);
        } else {
            $this->jsonResponse(false, 'Erreur lors de la création de la langue.');
        }
    }

    /**
     * Édition d'une langue
     */
    public function edit(): void
    {
        $id = (int)($_REQUEST['edit'] ?? $_POST['id_lang'] ?? 0);
        $db = new LangDb();

        if (Request::isMethod('POST')) {
            $this->processEdit($db, $id);
            return;
        }

        $lang = $db->fetchLanguageById($id);
        if (!$lang) return;

        // CORRECTION : Utilisation de votre LocalizationTool
        $availableLanguages = LocalizationTool::getLanguages('fr');

        $this->view->assign([
            'lang_data'           => $lang,
            'available_languages' => $availableLanguages,
            'hashtoken'           => $this->session->getToken()
        ]);

        $this->view->display('lang/edit.tpl');
    }

    private function processEdit(LangDb $db, int $id): void
    {
        $token = $_POST['hashtoken'] ?? '';
        if (!$this->session->validateToken($token)) {
            $this->jsonResponse(false, 'Session expirée.');
        }

        $data = [
            'name_lang'    => FormTool::simpleClean($_POST['name_lang'] ?? ''),
            'iso_lang'     => strtolower(FormTool::simpleClean($_POST['iso_lang'] ?? '')),
            'active_lang'  => (int)($_POST['active_lang'] ?? 0),
            'default_lang' => (int)($_POST['default_lang'] ?? 0)
        ];

        if ($data['default_lang'] === 1) {
            $data['active_lang'] = 1;
            $db->updateDefaultLanguage($id);
        }

        if ($db->updateLanguage($id, $data)) {
            $this->jsonResponse(true, 'La langue a été mise à jour.', ['type' => 'update']);
        } else {
            $this->jsonResponse(false, 'Erreur lors de la mise à jour.');
        }
    }

    public function delete(): void
    {
        $ids = $_POST['ids'] ?? [$_POST['id'] ?? null];
        $cleanIds = array_filter(array_map('intval', (array)$ids));

        if (!empty($cleanIds)) {
            $db = new LangDb();
            $defaultLang = $db->getDefaultLanguage();
            if ($defaultLang && in_array((int)$defaultLang['id_lang'], $cleanIds)) {
                $this->jsonResponse(false, 'Vous ne pouvez pas supprimer la langue par défaut du système.');
            }

            if ($db->deleteLanguage($cleanIds)) {
                $this->jsonResponse(true, 'Langue(s) supprimée(s).');
            }
        }
        $this->jsonResponse(false, 'Erreur lors de la suppression.');
    }
}