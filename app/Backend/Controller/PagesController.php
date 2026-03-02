<?php

declare(strict_types=1);

namespace App\Backend\Controller;

use App\Backend\Db\PagesDb;
use Magepattern\Component\HTTP\Request;
use Magepattern\Component\Tool\FormTool;
use Magepattern\Component\HTTP\Url;
use Magepattern\Component\Tool\StringTool;

class PagesController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function run(): void
    {
        // --- 1. MINI-ROUTEUR D'ACTION ---
        $action = $_GET['action'] ?? null;
        if ($action && $action !== 'run' && method_exists($this, $action)) {
            $this->$action();
            return;
        }

        // --- 2. LOGIQUE DU LISTING ---
        $idLangue = (int)$this->defaultLang['id_lang'];
        $db = new PagesDb();

        // Colonnes cibles (uniquement les données réelles)
        $targetColumns = ['id_pages', 'parent_pages', 'name_pages', 'published_pages', 'date_register'];

        $rawScheme = array_merge(
            $db->getTableScheme('mc_cms_page'),
            $db->getTableScheme('mc_cms_page_content')
        );

        // Simulation de la colonne virtuelle pour le nom du parent
        $rawScheme[] = ['column' => 'parent_pages', 'type' => 'varchar(255)'];

        $associations = [
            'id_pages' => ['title' => 'id', 'type' => 'text', 'class' => 'text-center text-muted small px-2'],
            'parent_pages' => ['title' => 'parent', 'type' => 'text', 'class' => 'text-muted small text-nowrap'],
            'name_pages' => ['title' => 'name', 'type' => 'text', 'class' => 'w-50 fw-bold'],
            'published_pages' => ['title' => 'status', 'type' => 'bin', 'class' => 'text-center px-3', 'enum' => 'status_'],
            'date_register' => ['title' => 'date', 'type' => 'date', 'class' => 'text-center text-nowrap text-muted small']
        ];

        $this->getScheme($rawScheme, $targetColumns, $associations);

        $page   = Request::isGet('page') ? (int)$_GET['page'] : 1;
        $limit  = Request::isGet('offset') ? (int)$_GET['offset'] : 25;
        $search = $_GET['search'] ?? [];

        $result = $db->fetchAllPages($page, $limit, $search, $idLangue);

        if ($result !== false) {
            // Le 3ème paramètre 'true' active le $sortable dans getItems
            $this->getItems('pages', $result['data'], true, $result['meta']);
        }

        $token = $this->session->getToken();

        $this->view->assign([
            'idcolumn'   => 'id_pages',
            'hashtoken'  => $token,
            'url_token'  => urlencode($token),
            'get_search' => $search,
            'sortable'   => false,   // On force l'activation pour table-rows.tpl
            'checkbox'   => true,   // On force l'activation des checkboxes
            'edit'       => true,   // Active la colonne Actions (Edit)
            'dlt'        => true    // Active la colonne Actions (Delete)
        ]);

        $this->view->display('pages/index.tpl');
    }
    /**
     * Affiche le formulaire d'ajout et intercepte la création
     */
    public function add(): void
    {
        $db = new PagesDb();
        $idLangue = (int)$this->defaultLang['id_lang'];
        $activeLangs = $db->fetchLanguages(); // Hérité de BaseDb !

        // --- INTERCEPTION POST ---
        if (Request::isMethod('POST')) {
            $this->processAdd($db);
            return;
        }

        // On récupère uniquement la liste des pages pour le menu déroulant "Parent"
        $pagesSelect = $db->fetchAllPagesForSelect($idLangue);

        // Assignation à la vue
        $this->view->assign([
            'pagesSelect' => $pagesSelect,
            'langs'       => $activeLangs, // Très important pour afficher les onglets dans add.tpl
            'hashtoken'   => $this->session->getToken(),
            'url_token'   => urlencode($this->session->getToken())
        ]);

        $this->view->display('pages/add.tpl');
    }

    /**
     * Traite la création d'une nouvelle page (Structure puis Contenus)
     */
    private function processAdd(PagesDb $db): void
    {
        // 1. Sécurité
        $token = Request::isPost('hashtoken') ? $_POST['hashtoken'] : '';
        if (!$this->session->validateToken($token)) {
            $this->jsonResponse(false, 'Session expirée.', ['success' => false]);
        }

        // 2. Traitement de la structure (Création)
        $idParent = (int)($_POST['id_parent'] ?? 0);

        // On initialise les données obligatoires
        $mainData = [
            'menu_pages' => (int)($_POST['menu_pages'] ?? 0)
        ];

        // L'ASTUCE EST ICI : On n'ajoute 'id_parent' que si c'est un vrai ID (> 0)
        // S'il n'y est pas, MySQL mettra DEFAULT NULL sans faire d'erreur
        if ($idParent > 0) {
            $mainData['id_parent'] = $idParent;
        }

        // On insère et on récupère le nouvel ID !
        $newId = $db->insertPageStructure($mainData);

        if (!$newId) {
            $this->jsonResponse(false, 'Erreur lors de la création de la structure de la page.', ['success' => false]);
        }

        // 3. Traitement des contenus (Traductions)
        $success = true;
        $activeLangs = $db->fetchLanguages();

        if (isset($_POST['content']) && is_array($_POST['content'])) {
            foreach ($_POST['content'] as $idLang => $values) {

                if (!isset($activeLangs[(int)$idLang])) continue;

                // Nettoyage et génération du slug
                $url = trim($values['url_pages'] ?? '');
                if ($url === '') {
                    $url = Url::clean($values['name_pages'] ?? '');
                } else {
                    $url = Url::clean($url);
                }

                $data = [
                    'name_pages'       => FormTool::simpleClean($values['name_pages'] ?? ''),
                    'longname_pages'   => FormTool::simpleClean($values['longname_pages'] ?? ''),
                    'url_pages'        => $url,
                    'resume_pages'     => FormTool::simpleClean($values['resume_pages'] ?? ''),
                    'content_pages'    => $values['content_pages'] ?? '',
                    'link_label_pages' => FormTool::simpleClean($values['link_label_pages'] ?? ''),
                    'link_title_pages' => FormTool::simpleClean($values['link_title_pages'] ?? ''),
                    'seo_title_pages'  => FormTool::simpleClean($values['seo_title_pages'] ?? ''),
                    'seo_desc_pages'   => FormTool::simpleClean($values['seo_desc_pages'] ?? ''),
                    'published_pages'  => (int)($values['published_pages'] ?? 0),
                    'last_update'      => date('Y-m-d H:i:s')
                ];

                // On réutilise savePageContent !
                // S'il ne trouve pas le couple ($newId + $idLang), il fera automatiquement un INSERT
                if (!$db->savePageContent($newId, (int)$idLang, $data)) {
                    $success = false;
                }
            }
        }

        // 4. Réponse JSON finale
        if ($success) {
            // Si c'est un succès, le JS (add_form) va rediriger l'utilisateur vers le listing (index.php?controller=Pages)
            $this->jsonResponse(true, 'La page a été créée avec succès.', [
                'success' => true,
                'type'    => 'add',
                'id'      => $newId
            ]);
        } else {
            $this->jsonResponse(false, 'Page créée, mais erreur lors de l\'enregistrement des contenus.', ['success' => false]);
        }
    }
    /**
     * @return void
     * @throws \Smarty\Exception
     */
    /**
     * Affiche le formulaire d'édition et intercepte la sauvegarde
     */
    /**
     * Affiche le formulaire d'édition et intercepte la sauvegarde
     */
    public function edit(): void
    {
        $id = (int)($_REQUEST['edit'] ?? $_POST['id_pages'] ?? 0);
        $db = new PagesDb();

        $idLangue = (int)$this->defaultLang['id_lang'];

        // On récupère les langues actives pour les calculs d'URL
        $activeLangs = $db->fetchLanguages();

        // --- INTERCEPTION POST ---
        if (Request::isMethod('POST')) {
            $this->processSave($db, $id);
            return;
        }

        // 1. Chargement des données
        $pageData = $db->fetchPageById($id);
        if (!$pageData) {
            // Ici tu peux ajouter une redirection vers le listing si l'ID n'existe pas
            return;
        }

        $children = $db->fetchPagesByParent($id, $idLangue);
        $pagesSelect = $db->fetchAllPagesForSelect($idLangue);

        // 2. Préparation du schéma pour le listing des sous-pages (si nécessaire)
        $targetColumns = ['id_pages', 'parent_pages', 'name_pages', 'published_pages', 'date_register'];
        $rawScheme = array_merge(
            $db->getTableScheme('mc_cms_page'),
            $db->getTableScheme('mc_cms_page_content')
        );
        $rawScheme[] = ['column' => 'parent_pages', 'type' => 'varchar(255)'];

        $associations = [
            'id_pages' => ['title' => 'id', 'type' => 'text', 'class' => 'text-center text-muted small px-2'],
            'parent_pages' => ['title' => 'parent', 'type' => 'text', 'class' => 'text-muted small text-nowrap'],
            'name_pages' => ['title' => 'name', 'type' => 'text', 'class' => 'w-50 fw-bold'],
            'published_pages' => ['title' => 'status', 'type' => 'bin', 'class' => 'text-center px-3', 'enum' => 'status_'],
            'date_register' => ['title' => 'date', 'type' => 'date', 'class' => 'text-center text-nowrap text-muted small']
        ];

        $this->getScheme($rawScheme, $targetColumns, $associations);

        $subpagesData = [];
        if ($children !== false && !empty($children)) {
            $subpagesData = $this->getItems('subpages', $children, true);
        }

        // 3. Calcul des URLs publiques pour l'affichage initial dans le template
        $controller = StringTool::strtolower($_GET['controller'] ?? 'pages');
        foreach ($activeLangs as $langId => $iso) {
            $slug = $pageData['content'][$langId]['url_pages'] ?? '';
            // On injecte dynamiquement la clé public_url dans le tableau Smarty
            $pageData['content'][$langId]['public_url'] = '/' . $iso . '/' . $controller . '/' . $id . '-' . $slug . '/';
        }

        // 4. Assignation finale
        $this->view->assign([
            'page_data'   => $pageData,
            'subpages'    => $subpagesData ?: $children,
            'pagesSelect' => $pagesSelect,
            'idcolumn'    => 'id_pages',
            'hashtoken'   => $this->session->getToken(),
            'url_token'   => urlencode($this->session->getToken()),
            'sortable'    => true,
            'checkbox'    => true,
            'edit'        => true,
            'dlt'         => true
        ]);

        $this->view->display('pages/edit.tpl');
    }

    /**
     * Traite la sauvegarde des données (Structure + Contenu)
     */
    private function processSave(PagesDb $db, int $idPage): void
    {
        // 1. Sécurité
        $token = Request::isPost('hashtoken') ? $_POST['hashtoken'] : '';
        if (!$this->session->validateToken($token)) {
            $this->jsonResponse(false, 'Session expirée.', ['success' => false]);
        }

        // 2. Vérification de l'ID
        if ($idPage === 0) {
            $idPage = (int)($_POST['id_pages'] ?? 0);
        }
        if ($idPage === 0) {
            $this->jsonResponse(false, 'Erreur : Identifiant de page manquant.', ['success' => false]);
        }

        $success = true;
        $publicUrls = [];
        $activeLangs = $db->fetchLanguages();
        $controller = StringTool::strtolower($_GET['controller'] ?? 'pages');

        // 3. Traitement de la structure (mc_cms_page)
        $idParent = (int)($_POST['id_parent'] ?? 0);
        $finalParentId = ($idParent > 0 && $idParent !== $idPage) ? $idParent : null;

        $mainData = [
            'id_parent'  => $finalParentId,
            'menu_pages' => (int)($_POST['menu_pages'] ?? 0)
        ];

        if (!$db->updatePageStructure($idPage, $mainData)) {
            $success = false;
        }

        // 4. SÉCURITÉ : Vérifie que le tableau content existe bien
        if (!isset($_POST['content']) || !is_array($_POST['content'])) {
            $this->jsonResponse(false, 'Erreur : Aucune donnée de traduction reçue.', ['success' => false]);
        }

        // 5. Traitement des contenus
        foreach ($_POST['content'] as $idLang => $values) {

            // ON NE SKIP PLUS LA BOUCLE ! On utilise 'fr' en dernier recours si la langue n'est pas indexée.
            $iso = $activeLangs[(int)$idLang] ?? 'fr';

            // Nettoyage et génération du slug
            $url = trim($values['url_pages'] ?? '');
            if ($url === '') {
                $url = Url::clean($values['name_pages'] ?? '');
            } else {
                $url = Url::clean($url);
            }

            // Construction de l'URL publique
            $publicUrls[$idLang] = '/' . $iso . '/' . $controller . '/' . $idPage . '-' . $url . '/';

            $data = [
                'name_pages'       => FormTool::simpleClean($values['name_pages'] ?? ''),
                'longname_pages'   => FormTool::simpleClean($values['longname_pages'] ?? ''),
                'url_pages'        => $url,
                'resume_pages'     => FormTool::simpleClean($values['resume_pages'] ?? ''),
                'content_pages'    => $values['content_pages'] ?? '',
                'link_label_pages' => FormTool::simpleClean($values['link_label_pages'] ?? ''),
                'link_title_pages' => FormTool::simpleClean($values['link_title_pages'] ?? ''),
                'seo_title_pages'  => FormTool::simpleClean($values['seo_title_pages'] ?? ''),
                'seo_desc_pages'   => FormTool::simpleClean($values['seo_desc_pages'] ?? ''),
                'published_pages'  => (int)($values['published_pages'] ?? 0),
                'last_update'      => date('Y-m-d H:i:s')
            ];

            if (!$db->savePageContent($idPage, (int)$idLang, $data)) {
                $success = false;
            }
        }

        // 6. Réponse JSON
        if ($success) {
            $this->jsonResponse(true, 'La page a été mise à jour avec succès.', [
                'success'     => true,
                'type'        => 'update',
                'id'          => $idPage,
                'public_urls' => $publicUrls
            ]);
        } else {
            $this->jsonResponse(false, 'Erreur SQL lors de l\'enregistrement des contenus.', ['success' => false]);
        }
    }
    /**
     * Méthode appelée en AJAX lors du drag & drop
     */
    public function reorder(): void
    {
        if (ob_get_length()) ob_clean();

        $rawToken = $_GET['hashtoken'] ?? '';
        $token = str_replace(' ', '+', $rawToken);

        if (!$this->session->validateToken($token)) {
            // Changement ici : 'success' => false
            $this->sendJsonResponse(['success' => false, 'message' => 'Token invalide']);
        }

        $input = file_get_contents('php://input');
        $data = $this->json->decode($input);

        if (isset($data['order']) && is_array($data['order'])) {
            $db = new PagesDb();
            try {
                $position = 1;
                foreach ($data['order'] as $id) {
                    $db->updateOrderPages((int)$id, $position);
                    $position++;
                }

                // Changement ici : 'success' => true
                $this->sendJsonResponse(['success' => true, 'message' => 'Ordre mis à jour']);
            } catch (\Exception $e) {
                $this->sendJsonResponse(['success' => false, 'message' => $e->getMessage()]);
            }
        }

        $this->sendJsonResponse(['success' => false, 'message' => 'Données invalides']);
    }
    /**
     * Supprime une page via AJAX
     */
    public function delete(): void
    {
        if (ob_get_length()) ob_clean();

        $token = $_GET['hashtoken'] ?? '';
        if (!$this->session->validateToken(str_replace(' ', '+', $token))) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Token invalide']);
        }

        // On récupère soit un ID unique (bouton ligne), soit un tableau (bulk delete)
        $ids = $_POST['ids'] ?? [$_POST['id'] ?? null];

        // Nettoyage et filtrage pour n'avoir que des entiers valides
        $cleanIds = array_filter(array_map('intval', (array)$ids));

        if (!empty($cleanIds)) {
            $db = new PagesDb();
            if ($db->deletePages($cleanIds)) {
                $this->sendJsonResponse([
                    'success' => true,
                    'message' => count($cleanIds) > 1 ? 'Les pages ont été supprimées.' : 'La page a été supprimée.',
                    'ids' => $cleanIds // On renvoie les IDs supprimés pour que le JS les retire du DOM
                ]);
            }
        }

        $this->sendJsonResponse(['success' => false, 'message' => 'Aucune page sélectionnée.']);
    }
    /**
     * Envoie une réponse JSON et coupe l'exécution
     */
    private function sendJsonResponse(array $data): void
    {
        header('Content-Type: application/json');
        echo $this->json->encode($data);
        exit;
    }
}