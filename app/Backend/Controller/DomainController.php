<?php

declare(strict_types=1);

namespace App\Backend\Controller;

use App\Backend\Db\DomainDb;
use App\Backend\Db\LangDb;
use Magepattern\Component\HTTP\Request;
use Magepattern\Component\Tool\FormTool;

class DomainController extends BaseController
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
     * Affiche la liste des domaines et la configuration des modules
     */
    private function index(): void
    {
        $db = new DomainDb();

        // 1. --- GESTION DU TABLEAU DES DOMAINES ---
        $targetColumns = ['id_domain', 'url_domain', 'default_domain', 'canonical_domain'];
        $rawScheme = $db->getTableScheme('mc_domain');

        $associations = [
            'id_domain'        => ['title' => 'ID', 'type' => 'text', 'class' => 'text-center text-muted small px-2'],
            'url_domain'       => ['title' => 'URL du Domaine', 'type' => 'text', 'class' => 'fw-bold w-50'],
            'default_domain'   => ['title' => 'Défaut', 'type' => 'bin', 'class' => 'text-center px-3'],
            'canonical_domain' => ['title' => 'Canonique', 'type' => 'bin', 'class' => 'text-center px-3']
        ];

        $this->getScheme($rawScheme, $targetColumns, $associations);

        $page   = Request::isGet('page') ? (int)$_GET['page'] : 1;
        $limit  = Request::isGet('offset') ? (int)$_GET['offset'] : 25;
        $search = $_GET['search'] ?? [];

        $result = $db->fetchAllDomains($page, $limit, $search);

        if ($result !== false) {
            $this->getItems('domain_list', $result['data'], true, $result['meta']);
        }

        // 2. --- GESTION DES MODULES (mc_config) ---
        $modulesConfig = $db->fetchModulesConfig();

        $this->view->assign([
            'idcolumn'      => 'id_domain',
            'modulesConfig' => $modulesConfig,
            'hashtoken'     => $this->session->getToken(),
            'url_token'     => urlencode($this->session->getToken()),
            'get_search'    => $search,
            'sortable'      => false,
            'checkbox'      => true,
            'edit'          => true,
            'dlt'           => true
        ]);

        $this->view->display('domain/index.tpl');
    }

    /**
     * Ajouter un domaine
     */
    public function add(): void
    {
        if (Request::isMethod('POST')) {
            $this->processAdd();
            return;
        }

        $this->view->assign(['hashtoken' => $this->session->getToken()]);
        $this->view->display('domain/add.tpl');
    }

    private function processAdd(): void
    {
        $token = $_POST['hashtoken'] ?? '';
        if (!$this->session->validateToken($token)) {
            $this->jsonResponse(false, 'Session expirée.');
        }

        $db = new DomainDb();
        $data = [
            'url_domain'       => FormTool::simpleClean($_POST['url_domain'] ?? ''),
            'default_domain'   => (int)($_POST['default_domain'] ?? 0),
            'canonical_domain' => (int)($_POST['canonical_domain'] ?? 0)
        ];

        if (empty($data['url_domain'])) {
            $this->jsonResponse(false, 'L\'URL du domaine est requise.');
        }

        $newId = $db->insertDomain($data);

        if ($newId) {
            $this->jsonResponse(true, 'Le domaine a été ajouté avec succès.', [
                'type' => 'add',
                'id'   => $newId
            ]);
        } else {
            $this->jsonResponse(false, 'Erreur lors de la création du domaine.');
        }
    }

    /**
     * Éditer un domaine existant
     */
    public function edit(): void
    {
        $id = (int)($_REQUEST['edit'] ?? $_POST['id_domain'] ?? 0);
        $db = new DomainDb();

        if (Request::isMethod('POST')) {
            $this->processEdit($db, $id);
            return;
        }

        $domain = $db->fetchDomainById($id);
        if (!$domain) return;

        // --- Utilisation de LangDb pour les langues ---
        $langDb = new LangDb();
        $allLangs = $langDb->fetchActiveLanguages();
        $domainLangs = $db->fetchDomainLanguages($id);

        $this->view->assign([
            'domain'       => $domain,
            'all_langs'    => $allLangs,
            'domain_langs' => $domainLangs,
            'hashtoken'    => $this->session->getToken()
        ]);

        $this->view->display('domain/edit.tpl');
    }

    private function processEdit(DomainDb $db, int $id): void
    {
        $token = $_POST['hashtoken'] ?? '';
        if (!$this->session->validateToken($token)) {
            $this->jsonResponse(false, 'Session expirée.');
        }

        $data = [
            'url_domain'       => FormTool::simpleClean($_POST['url_domain'] ?? ''),
            'tracking_domain'  => $_POST['tracking_domain'] ?? '',
            'default_domain'   => (int)($_POST['default_domain'] ?? 0),
            'canonical_domain' => (int)($_POST['canonical_domain'] ?? 0)
        ];

        if ($db->updateDomain($id, $data)) {
            $this->jsonResponse(true, 'Le domaine a été mis à jour.', ['type' => 'update']);
        } else {
            $this->jsonResponse(false, 'Erreur lors de la mise à jour.');
        }
    }

    /**
     * Suppression
     */
    public function delete(): void
    {
        $ids = $_POST['ids'] ?? [$_POST['id'] ?? null];
        $cleanIds = array_filter(array_map('intval', (array)$ids));

        if (!empty($cleanIds)) {
            if ((new DomainDb())->deleteDomain($cleanIds)) {
                $this->jsonResponse(true, 'Domaine(s) supprimé(s).');
            }
        }
        $this->jsonResponse(false, 'Erreur lors de la suppression.');
    }

    /**
     * Enregistrer l'activation/désactivation des modules (onglet Modules)
     */
    public function saveModules(): void
    {
        $token = $_POST['hashtoken'] ?? '';
        if (!$this->session->validateToken($token)) {
            $this->jsonResponse(false, 'Session expirée.');
        }

        $db = new DomainDb();
        $success = true;

        $modulesKeys = ['pages', 'news', 'catalog', 'about'];
        $postedModules = $_POST['modules'] ?? [];

        foreach ($modulesKeys as $module) {
            $status = isset($postedModules[$module]) ? 1 : 0;
            if (!$db->updateModuleConfig($module, $status)) {
                $success = false;
            }
        }

        if ($success) {
            $this->jsonResponse(true, 'L\'état des modules a été mis à jour.', ['type' => 'update']);
        } else {
            $this->jsonResponse(false, 'Erreur lors de la mise à jour des modules.');
        }
    }

    /**
     * Enregistre les langues associées au domaine (Onglet 3)
     */
    public function saveDomainLanguages(): void
    {
        $token = $_POST['hashtoken'] ?? '';
        if (!$this->session->validateToken($token)) {
            $this->jsonResponse(false, 'Session expirée.');
        }

        $idDomain = (int)($_POST['id_domain'] ?? 0);
        $selectedLangs = $_POST['langs'] ?? [];
        $defaultLang = (int)($_POST['default_lang'] ?? 0);

        if ($idDomain <= 0) {
            $this->jsonResponse(false, 'Domaine invalide.');
        }

        if (!empty($selectedLangs) && !in_array($defaultLang, $selectedLangs)) {
            $defaultLang = (int)reset($selectedLangs);
        }

        $db = new DomainDb();
        if ($db->syncDomainLanguages($idDomain, $selectedLangs, $defaultLang)) {
            $this->jsonResponse(true, 'Les langues du domaine ont été mises à jour.', ['type' => 'update']);
        } else {
            $this->jsonResponse(false, 'Erreur lors de la mise à jour des langues.');
        }
    }
}