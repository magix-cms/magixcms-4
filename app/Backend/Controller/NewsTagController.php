<?php

declare(strict_types=1);

namespace App\Backend\Controller;

use App\Backend\Db\NewsTagDb;
use App\Backend\Db\LangDb;
use Magepattern\Component\HTTP\Request;
use Magepattern\Component\Tool\FormTool;

class NewsTagController extends BaseController
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
        $db = new NewsTagDb();

        $targetColumns = ['id_tag', 'name_tag', 'iso_lang'];

        // On récupère le schéma de base et on ajoute la colonne iso_lang (qui vient de la jointure)
        $rawScheme = $db->getTableScheme('mc_news_tag');
        $rawScheme[] = ['column' => 'iso_lang', 'type' => 'varchar(5)'];

        $associations = [
            'id_tag'   => ['title' => 'ID', 'type' => 'text', 'class' => 'text-center text-muted small px-2'],
            'name_tag' => ['title' => 'Nom du Tag', 'type' => 'text', 'class' => 'fw-bold w-50'],
            'iso_lang' => ['title' => 'Langue', 'type' => 'text', 'class' => 'text-center font-monospace text-uppercase']
        ];

        $this->getScheme($rawScheme, $targetColumns, $associations);

        $page   = Request::isGet('page') ? (int)$_GET['page'] : 1;
        $limit  = Request::isGet('offset') ? (int)$_GET['offset'] : 25;
        $search = $_GET['search'] ?? [];

        $result = $db->fetchAllTagsPaginated($page, $limit, $search);

        if ($result !== false) {
            $this->getItems('tag_list', $result['data'], true, $result['meta']);
        }

        $this->view->assign([
            'idcolumn'   => 'id_tag',
            'hashtoken'  => $this->session->getToken(),
            'get_search' => $search,
            'sortable'   => false,
            'checkbox'   => true,
            'edit'       => true,
            'dlt'        => true
        ]);

        $this->view->display('news_tag/index.tpl');
    }

    public function add(): void
    {
        if (Request::isMethod('POST')) {
            $this->processAdd();
            return;
        }

        // On récupère les langues pour le menu déroulant
        $langDb = new LangDb();

        $this->view->assign([
            'langs'     => $langDb->getFrontendLanguages(),
            'hashtoken' => $this->session->getToken()
        ]);

        $this->view->display('news_tag/add.tpl');
    }

    private function processAdd(): void
    {
        $token = $_POST['hashtoken'] ?? '';
        if (!$this->session->validateToken($token)) {
            $this->jsonResponse(false, 'Session expirée.');
        }

        $data = [
            'name_tag' => FormTool::simpleClean($_POST['name_tag'] ?? ''),
            'id_lang'  => (int)($_POST['id_lang'] ?? 0)
        ];

        if (empty($data['name_tag']) || $data['id_lang'] <= 0) {
            $this->jsonResponse(false, 'Veuillez remplir tous les champs.');
        }

        $db = new NewsTagDb();
        $newId = $db->insertTag($data);

        if ($newId) {
            $this->jsonResponse(true, 'Le tag a été ajouté.', ['type' => 'add', 'id' => $newId]);
        } else {
            $this->jsonResponse(false, 'Erreur lors de la création du tag.');
        }
    }

    public function edit(): void
    {
        $id = (int)($_REQUEST['edit'] ?? $_POST['id_tag'] ?? 0);
        $db = new NewsTagDb();

        if (Request::isMethod('POST')) {
            $this->processEdit($db, $id);
            return;
        }

        $tag = $db->fetchTagById($id);
        if (!$tag) return;

        $langDb = new LangDb();

        $this->view->assign([
            'tag_data'  => $tag,
            'langs'     => $langDb->getFrontendLanguages(),
            'hashtoken' => $this->session->getToken()
        ]);

        $this->view->display('news_tag/edit.tpl');
    }

    private function processEdit(NewsTagDb $db, int $id): void
    {
        $token = $_POST['hashtoken'] ?? '';
        if (!$this->session->validateToken($token)) {
            $this->jsonResponse(false, 'Session expirée.');
        }

        $data = [
            'name_tag' => FormTool::simpleClean($_POST['name_tag'] ?? ''),
            'id_lang'  => (int)($_POST['id_lang'] ?? 0)
        ];

        if (empty($data['name_tag']) || $data['id_lang'] <= 0) {
            $this->jsonResponse(false, 'Veuillez remplir tous les champs.');
        }

        if ($db->updateTag($id, $data)) {
            $this->jsonResponse(true, 'Le tag a été mis à jour.', ['type' => 'update']);
        } else {
            $this->jsonResponse(false, 'Erreur lors de la mise à jour.');
        }
    }

    public function delete(): void
    {
        $ids = $_POST['ids'] ?? [$_POST['id'] ?? null];
        $cleanIds = array_filter(array_map('intval', (array)$ids));

        if (!empty($cleanIds)) {
            if ((new NewsTagDb())->deleteTags($cleanIds)) {
                $this->jsonResponse(true, 'Tag(s) supprimé(s).');
            }
        }
        $this->jsonResponse(false, 'Erreur lors de la suppression.');
    }
}