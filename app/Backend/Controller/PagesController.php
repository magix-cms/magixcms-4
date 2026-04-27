<?php

declare(strict_types=1);

namespace App\Backend\Controller;

use App\Backend\Db\PagesDb;
use App\Component\Db\ConfigDb;
use App\Component\Routing\UrlTool;
use App\Component\File\UploadTool;
use App\Component\File\ImageTool;
use Magepattern\Component\HTTP\Request;
use Magepattern\Component\Tool\FormTool;
use Magepattern\Component\HTTP\Url;
use Magepattern\Component\Tool\StringTool;
use Magepattern\Component\File\FileTool;
use App\Backend\Db\RevisionsDb;
use App\Component\Cache\CacheManager; // 🟢 Import du gestionnaire de cache

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

        if ($action === 'tinymcePopup') {
            $this->tinymcePopup();
            return;
        }

        if ($action && $action !== 'run' && method_exists($this, $action)) {
            $this->$action();
            return;
        }

        // --- 2. LOGIQUE DU LISTING ---
        $idLangue = (int)$this->defaultLang['id_lang'];
        $db = new PagesDb();

        $targetColumns = ['id_pages', 'parent_pages', 'name_pages', 'published_pages', 'date_register'];

        $rawScheme = array_merge(
            $db->getTableScheme('mc_cms_page'),
            $db->getTableScheme('mc_cms_page_content')
        );

        $rawScheme[] = ['column' => 'parent_pages', 'type' => 'varchar(255)'];

        $associations = [
            'id_pages' => ['title' => 'id', 'type' => 'text', 'class' => 'text-center text-muted small px-4'],
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

        $meta = [];

        if ($result !== false) {
            $this->getItems('pages', $result['data'], true, $result['meta']);
            $meta = $result['meta'];
        }

        $token = $this->session->getToken();

        $this->view->assign([
            'idcolumn'   => 'id_pages',
            'hashtoken'  => $token,
            'url_token'  => urlencode($token),
            'get_search' => $search,
            'sortable'   => false,
            'checkbox'   => true,
            'edit'       => true,
            'dlt'        => true,
            'meta'       => $meta
        ]);

        $this->view->display('pages/index.tpl');
    }

    public function add(): void
    {
        $db = new PagesDb();
        $idLangue = (int)$this->defaultLang['id_lang'];
        $activeLangs = $db->fetchLanguages();

        if (Request::isMethod('POST')) {
            $this->processAdd($db);
            return;
        }

        $pagesSelect = $db->fetchAllPagesForSelect($idLangue);

        $this->view->assign([
            'pagesSelect' => $pagesSelect,
            'langs'       => $activeLangs,
            'hashtoken'   => $this->session->getToken(),
            'url_token'   => urlencode($this->session->getToken())
        ]);

        $this->view->display('pages/add.tpl');
    }

    private function processAdd(PagesDb $db): void
    {
        $token = Request::isPost('hashtoken') ? $_POST['hashtoken'] : '';
        if (!$this->session->validateToken($token)) {
            $this->jsonResponse(false, 'Session expirée.', ['success' => false]);
        }

        $idParent = (int)($_POST['id_parent'] ?? 0);

        $mainData = [
            'menu_pages' => (int)($_POST['menu_pages'] ?? 0)
        ];

        if ($idParent > 0) {
            $mainData['id_parent'] = $idParent;
        }

        $newId = $db->insertPageStructure($mainData);

        if (!$newId) {
            $this->jsonResponse(false, 'Erreur lors de la création de la structure de la page.', ['success' => false]);
        }

        $success = true;
        $activeLangs = $db->fetchLanguages();

        if (isset($_POST['content']) && is_array($_POST['content'])) {
            foreach ($_POST['content'] as $idLang => $values) {

                if (!isset($activeLangs[(int)$idLang])) continue;

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

                if (!$db->savePageContent($newId, (int)$idLang, $data)) {
                    $success = false;
                }
            }
        }

        if ($success) {
            // 🟢 PURGE DU CACHE
            CacheManager::clearFrontend('pages');

            $this->jsonResponse(true, 'La page a été créée avec succès.', [
                'success' => true,
                'type'    => 'add',
                'id'      => $newId
            ]);
        } else {
            $this->jsonResponse(false, 'Page créée, mais erreur lors de l\'enregistrement des contenus.', ['success' => false]);
        }
    }

    public function edit(): void
    {
        $id = (int)($_REQUEST['edit'] ?? $_POST['id_pages'] ?? 0);
        $db = new PagesDb();

        $idLangue = (int)$this->defaultLang['id_lang'];
        $activeLangs = $db->fetchLanguages();

        if (Request::isMethod('POST')) {
            $this->processSave($db, $id);
            return;
        }

        $pageData = $db->fetchPageById($id);
        if (!$pageData) {
            return;
        }

        $children = $db->fetchPagesByParent($id, $idLangue);
        $pagesSelect = $db->fetchAllPagesForSelect($idLangue);

        $targetColumns = ['id_pages', 'parent_pages', 'name_pages', 'published_pages', 'date_register'];
        $rawScheme = array_merge(
            $db->getTableScheme('mc_cms_page'),
            $db->getTableScheme('mc_cms_page_content')
        );
        $rawScheme[] = ['column' => 'parent_pages', 'type' => 'varchar(255)'];

        $associations = [
            'id_pages' => ['title' => 'id', 'type' => 'text', 'class' => 'text-center text-muted small px-4'],
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

        $controller = StringTool::strtolower($_GET['controller'] ?? 'pages');
        foreach ($activeLangs as $langId => $iso) {
            $slug = $pageData['content'][$langId]['url_pages'] ?? '';
            $pageData['content'][$langId]['public_url'] = '/' . $iso . '/' . $controller . '/' . $id . '-' . $slug . '/';
        }

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

    private function processSave(PagesDb $db, int $idPage): void
    {
        $token = Request::isPost('hashtoken') ? $_POST['hashtoken'] : '';
        if (!$this->session->validateToken($token)) {
            $this->jsonResponse(false, 'Session expirée.', ['success' => false]);
        }

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

        $idParent = (int)($_POST['id_parent'] ?? 0);
        $finalParentId = ($idParent > 0 && $idParent !== $idPage) ? $idParent : null;

        $mainData = [
            'id_parent'  => $finalParentId,
            'menu_pages' => (int)($_POST['menu_pages'] ?? 0)
        ];

        if (!$db->updatePageStructure($idPage, $mainData)) {
            $success = false;
        }

        if (!isset($_POST['content']) || !is_array($_POST['content'])) {
            $this->jsonResponse(false, 'Erreur : Aucune donnée de traduction reçue.', ['success' => false]);
        }

        foreach ($_POST['content'] as $idLang => $values) {
            $iso = $activeLangs[(int)$idLang] ?? 'fr';

            $url = trim($values['url_pages'] ?? '');
            if ($url === '') {
                $url = Url::clean($values['name_pages'] ?? '');
            } else {
                $url = Url::clean($url);
            }

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
            } else {
                if (!empty($data['content_pages'])) {
                    $revDb = new RevisionsDb();
                    $revDb->saveRevision('pages', $idPage, (int)$idLang, 'content_pages', $data['content_pages']);
                }
            }
        }

        if ($success) {
            // 🟢 PURGE DU CACHE
            CacheManager::clearFrontend('pages');

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

    public function reorder(): void
    {
        if (ob_get_length()) ob_clean();

        $rawToken = $_GET['hashtoken'] ?? '';
        $token = str_replace(' ', '+', $rawToken);

        if (!$this->session->validateToken($token)) {
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

                // 🟢 PURGE DU CACHE
                CacheManager::clearFrontend('pages');

                $this->sendJsonResponse(['success' => true, 'message' => 'Ordre mis à jour']);
            } catch (\Exception $e) {
                $this->sendJsonResponse(['success' => false, 'message' => $e->getMessage()]);
            }
        }

        $this->sendJsonResponse(['success' => false, 'message' => 'Données invalides']);
    }

    public function delete(): void
    {
        if (ob_get_length()) ob_clean();

        $token = $_GET['hashtoken'] ?? '';
        if (!$this->session->validateToken(str_replace(' ', '+', $token))) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Token invalide']);
        }

        $ids = $_POST['ids'] ?? [$_POST['id'] ?? null];
        $cleanIds = array_filter(array_map('intval', (array)$ids));

        if (!empty($cleanIds)) {
            $db = new PagesDb();
            if ($db->deletePages($cleanIds)) {
                // 🟢 PURGE DU CACHE
                CacheManager::clearFrontend('pages');

                $this->sendJsonResponse([
                    'success' => true,
                    'message' => count($cleanIds) > 1 ? 'Les pages ont été supprimées.' : 'La page a été supprimée.',
                    'ids' => $cleanIds
                ]);
            }
        }

        $this->sendJsonResponse(['success' => false, 'message' => 'Aucune page sélectionnée.']);
    }

    public function processUploadImages(): void
    {
        $idPage = (int)($_POST['id'] ?? 0);

        if ($idPage <= 0 || empty($_FILES['img_multiple']['name'][0])) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Aucun fichier reçu.']);
        }

        $db = new PagesDb();
        $pageData = $db->fetchPageById($idPage);
        $idLangue = (int)$this->defaultLang['id_lang'];
        $slug = !empty($pageData['content'][$idLangue]['url_pages']) ? $pageData['content'][$idLangue]['url_pages'] : 'page';

        $uploadTool = new UploadTool();
        $lastImageId = $db->getLastImageId($idPage);

        $results = $uploadTool->multipleImageUpload(
            'pages', 'pages', 'upload/pages', [(string)$idPage],
            [
                'postKey'          => 'img_multiple',
                'suffix'           => $lastImageId,
                'suffix_increment' => true,
                'name'             => $slug
            ]
        );

        $uploadedCount = 0;
        $errors = [];

        foreach ($results as $res) {
            if ($res['status'] === true) {
                if ($db->insertImage($idPage, $res['file'])) {
                    $uploadedCount++;
                }
            } else {
                $errors[] = $res['msg'];
            }
        }

        if ($uploadedCount > 0) {
            // 🟢 PURGE DU CACHE
            CacheManager::clearFrontend('pages');

            $this->sendJsonResponse([
                'success' => true,
                'message' => "$uploadedCount image(s) ajoutée(s).",
                'uploaded' => $uploadedCount
            ]);
        } else {
            $msg = !empty($errors) ? implode(', ', $errors) : 'Erreur lors du traitement.';
            $this->sendJsonResponse(['success' => false, 'message' => $msg]);
        }
    }

    public function processOrderImages(): void
    {
        $imageIds = $_POST['image'] ?? [];

        if (!empty($imageIds) && is_array($imageIds)) {
            $db = new PagesDb();

            if ($db->reorderImages($imageIds)) {
                // 🟢 PURGE DU CACHE
                CacheManager::clearFrontend('pages');

                $this->jsonResponse(true, 'L\'ordre des images a été sauvegardé.', ['type' => 'order_success']);
            } else {
                $this->jsonResponse(false, 'Une erreur est survenue lors de la sauvegarde de l\'ordre.');
            }
        } else {
            $this->jsonResponse(false, 'Aucune donnée d\'ordre reçue.');
        }
    }

    public function processSetDefaultImage(): void
    {
        $idPage = (int)($_POST['edit'] ?? 0);
        $idImg = (int)($_POST['id_img'] ?? 0);

        if ($idPage > 0 && $idImg > 0) {
            $db = new PagesDb();

            if ($db->setDefaultImage($idPage, $idImg)) {
                // 🟢 PURGE DU CACHE
                CacheManager::clearFrontend('pages');

                $this->jsonResponse(true, 'Image par défaut mise à jour.', ['type' => 'update']);
            } else {
                $this->jsonResponse(false, 'Erreur lors de la mise à jour de l\'image par défaut.');
            }
        } else {
            $this->jsonResponse(false, 'Paramètres manquants.');
        }
    }

    public function processDeleteImage(): void
    {
        $ids = $_POST['ids'] ?? [];
        if (empty($ids) && isset($_POST['id_img'])) {
            $ids = [$_POST['id_img']];
        }

        $idPage = (int)($_POST['id_pages'] ?? 0);

        if (!empty($ids)) {
            $db = new PagesDb();
            $configDb = new ConfigDb();
            $urlTool = new UrlTool();

            $configs = $configDb->fetchImageSizes('pages', 'pages');
            $uploadDir = $urlTool->dirUpload('upload/pages/' . $idPage, true);

            $deletedCount = 0;

            foreach ($ids as $idImg) {
                $imgData = $db->deleteImage((int)$idImg);

                if ($imgData && !empty($imgData['name_img'])) {
                    $filesToDelete = [];
                    $filename = $imgData['name_img'];
                    $nameNoExt = pathinfo($filename, PATHINFO_FILENAME);

                    $filesToDelete[] = $uploadDir . $filename;
                    $filesToDelete[] = $uploadDir . $nameNoExt . '.webp';

                    if (!empty($configs)) {
                        foreach ($configs as $conf) {
                            $prefix = $conf['prefix'] . '_';
                            $filesToDelete[] = $uploadDir . $prefix . $filename;
                            $filesToDelete[] = $uploadDir . $prefix . $nameNoExt . '.webp';
                        }
                    }

                    FileTool::remove($filesToDelete);
                    $deletedCount++;
                }
            }

            if ($deletedCount > 0) {
                // 🟢 PURGE DU CACHE
                CacheManager::clearFrontend('pages');

                $this->jsonResponse(true, "$deletedCount image(s) supprimée(s).", ['type' => 'delete_success']);
            }
        }

        $this->jsonResponse(false, 'Erreur lors de la suppression.');
    }

    public function getImages(): void
    {
        if (ob_get_length()) ob_clean();
        $id = (int)($_GET['edit'] ?? $_GET['id_pages'] ?? 0);

        $db = new PagesDb();
        $images = $db->fetchImagesByPage($id);
        $imageTool = new \App\Component\File\ImageTool();
        $formatted = $imageTool->setModuleImages('pages', 'pages', $images, $id);

        $this->view->assign([
            'images'    => $formatted,
            'id_pages'  => $id,
            'current_c' => 'Pages'
        ]);

        $html = $this->view->fetch('components/gallery.tpl');
        $this->jsonResponse(true, 'OK', ['result' => $html]);
    }

    public function getImgMeta(): void
    {
        if (ob_get_length()) ob_clean();

        $idImg = (int)($_GET['id_img'] ?? 0);
        $db = new PagesDb();

        $langs = $db->fetchLanguages();
        $meta = $db->fetchImageMeta($idImg);

        $currentController = ucfirst($_GET['controller'] ?? 'Pages');

        $this->view->assign([
            'img_id'          => $idImg,
            'langs'           => $langs,
            'meta'            => $meta,
            'controller_name' => $currentController
        ]);

        $html = $this->view->fetch('components/modal-img-meta.tpl');

        $this->jsonResponse(true, 'OK', ['html' => $html]);
    }

    public function processSaveImgMeta(): void
    {
        if (ob_get_length()) ob_clean();

        $idImg = (int)($_POST['id_img'] ?? 0);
        $db = new PagesDb();
        $success = true;

        if ($idImg > 0 && isset($_POST['meta']) && is_array($_POST['meta'])) {
            foreach ($_POST['meta'] as $idLang => $values) {
                $data = [
                    'title_img'   => FormTool::simpleClean($values['title_img'] ?? ''),
                    'alt_img'     => FormTool::simpleClean($values['alt_img'] ?? ''),
                    'caption_img' => FormTool::simpleClean($values['caption_img'] ?? '')
                ];

                if (!$db->saveImageMeta($idImg, (int)$idLang, $data)) {
                    $success = false;
                }
            }
        } else {
            $success = false;
        }

        if ($success) {
            // 🟢 PURGE DU CACHE
            CacheManager::clearFrontend('pages');
        }

        $this->jsonResponse($success, $success ? 'Métadonnées sauvegardées avec succès.' : 'Erreur lors de la sauvegarde.');
    }

    public function tinymcePopup(): void
    {
        $db = new PagesDb();

        $requestedLangId = (int)($_GET['lang_id'] ?? $this->defaultLang['id_lang']);
        $activeLangs = $db->fetchLanguages();
        $iso = $activeLangs[$requestedLangId] ?? 'fr';

        $controllerSlug = StringTool::strtolower('pages');
        $rawPages = $db->getPagesForTinymce($requestedLangId);

        $pagesById = [];
        foreach ($rawPages as $p) {
            $pagesById[$p['id_pages']] = $p;
            $pagesById[$p['id_pages']]['children'] = [];
        }

        $tree = [];
        foreach ($pagesById as $id => &$p) {
            if (!empty($p['id_parent']) && isset($pagesById[$p['id_parent']])) {
                $pagesById[$p['id_parent']]['children'][] = &$p;
            } else {
                $tree[] = &$p;
            }
        }

        $flatList = [];
        $flatten = function($nodes, $depth = 0) use (&$flatten, &$flatList, $iso, $controllerSlug) {
            foreach ($nodes as $node) {
                $title = !empty($node['name_pages']) ? $node['name_pages'] : '⚠️ (Non traduit)';
                $slug = !empty($node['url_pages']) ? $node['url_pages'] : Url::clean($title);

                $publicUrl = '/' . $iso . '/' . $controllerSlug . '/' . $node['id_pages'] . '-' . $slug . '/';

                $flatList[] = [
                    'id'    => $node['id_pages'],
                    'title' => $title,
                    'url'   => $publicUrl,
                    'depth' => $depth
                ];

                if (!empty($node['children'])) {
                    $flatten($node['children'], $depth + 1);
                }
            }
        };

        $flatten($tree);

        $this->view->assign([
            'pagesList' => $flatList,
            'iso_lang'  => strtoupper($iso),
            'hashtoken' => $this->session->getToken()
        ]);

        $this->view->display('pages/tinymce_popup.tpl');
    }

    private function sendJsonResponse(array $data): void
    {
        header('Content-Type: application/json');
        echo $this->json->encode($data);
        exit;
    }
}