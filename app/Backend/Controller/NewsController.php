<?php

declare(strict_types=1);

namespace App\Backend\Controller;

use App\Backend\Db\NewsDb;
use App\Backend\Db\LangDb;
use App\Component\Db\ConfigDb;
use App\Component\Routing\UrlTool;
use App\Component\File\UploadTool;
use App\Component\File\ImageTool;
use Magepattern\Component\HTTP\Request;
use Magepattern\Component\Tool\FormTool;
use Magepattern\Component\HTTP\Url;
use Magepattern\Component\Tool\StringTool;
use Magepattern\Component\File\FileTool;
use Magepattern\Component\Tool\DateTool;
use App\Backend\Db\RevisionsDb;

class NewsController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function run(): void
    {
        $action = $_GET['action'] ?? null;

        if ($action === 'tinymcePopup') {
            $this->tinymcePopup();
            return;
        }

        if ($action && $action !== 'run' && method_exists($this, $action)) {
            $this->$action();
            return;
        }

        $idLangue = (int)$this->defaultLang['id_lang'];
        $db = new NewsDb();

        $targetColumns = ['id_news', 'name_news', 'published_news', 'date_publish', 'date_register'];

        $rawScheme = array_merge(
            $db->getTableScheme('mc_news'),
            $db->getTableScheme('mc_news_content')
        );

        $associations = [
            'id_news' => ['title' => 'id', 'type' => 'text', 'class' => 'text-center text-muted small px-2'],
            'name_news' => ['title' => 'name', 'type' => 'text', 'class' => 'w-50 fw-bold'],
            'published_news' => ['title' => 'status', 'type' => 'bin', 'class' => 'text-center px-3', 'enum' => 'status_'],
            'date_publish' => ['title' => 'Publication', 'type' => 'date', 'class' => 'text-center text-nowrap text-muted small'],
            'date_register' => ['title' => 'date', 'type' => 'date', 'class' => 'text-center text-nowrap text-muted small']
        ];

        $this->getScheme($rawScheme, $targetColumns, $associations);

        $page   = Request::isGet('page') ? (int)$_GET['page'] : 1;
        $limit  = Request::isGet('offset') ? (int)$_GET['offset'] : 25;
        $search = $_GET['search'] ?? [];

        $result = $db->fetchAllNews($page, $limit, $search, $idLangue);

        $meta = [];

        if ($result !== false) {
            $this->getItems('news_list', $result['data'], true, $result['meta']);
            $meta = $result['meta']; // <-- ON RÉCUPÈRE LE META ICI
        }

        $token = $this->session->getToken();

        $this->view->assign([
            'idcolumn'   => 'id_news',
            'hashtoken'  => $token,
            'url_token'  => urlencode($token),
            'get_search' => $search,
            'sortable'   => false, // Tri par date côté News, pas de Drag&Drop
            'checkbox'   => true,
            'edit'       => true,
            'dlt'        => true,
            'meta'       => $meta
        ]);

        $this->view->display('news/index.tpl');
    }

    public function add(): void
    {
        $db = new NewsDb();
        $idLangue = (int)$this->defaultLang['id_lang'];
        $activeLangs = (new LangDb())->fetchLanguages();

        if (Request::isMethod('POST')) {
            $this->processAdd($db);
            return;
        }

        $this->view->assign([
            'langs'       => $activeLangs,
            'all_tags'        => $db->fetchAllTagsForLang($idLangue), // Tags pour la langue par défaut
            'hashtoken'   => $this->session->getToken(),
            'url_token'   => urlencode($this->session->getToken())
        ]);

        $this->view->display('news/add.tpl');
    }

    private function processAdd(NewsDb $db): void
    {
        $token = Request::isPost('hashtoken') ? $_POST['hashtoken'] : '';
        if (!$this->session->validateToken($token)) {
            $this->jsonResponse(false, 'Session expirée.', ['success' => false]);
        }

        $mainData = [
            'date_publish'     => !empty($_POST['date_publish']) ? $_POST['date_publish'] : null,
            'date_event_start' => !empty($_POST['date_event_start']) ? $_POST['date_event_start'] : null,
            'date_event_end'   => !empty($_POST['date_event_end']) ? $_POST['date_event_end'] : null,
        ];

        $newId = $db->insertNewsStructure($mainData);

        if (!$newId) {
            $this->jsonResponse(false, 'Erreur lors de la création de la structure.', ['success' => false]);
        }

        $success = true;
        $activeLangs = (new LangDb())->getFrontendLanguages();

        if (isset($_POST['content']) && is_array($_POST['content'])) {
            foreach ($_POST['content'] as $idLang => $values) {
                $url = trim($values['url_news'] ?? '');
                if ($url === '') {
                    $url = Url::clean($values['name_news'] ?? '');
                } else {
                    $url = Url::clean($url);
                }

                $data = [
                    'name_news'       => FormTool::simpleClean($values['name_news'] ?? ''),
                    'longname_news'   => FormTool::simpleClean($values['longname_news'] ?? ''),
                    'url_news'        => $url,
                    'resume_news'     => FormTool::simpleClean($values['resume_news'] ?? ''),
                    'content_news'    => $values['content_news'] ?? '',
                    'link_label_news' => FormTool::simpleClean($values['link_label_news'] ?? ''),
                    'link_title_news' => FormTool::simpleClean($values['link_title_news'] ?? ''),
                    'seo_title_news'  => FormTool::simpleClean($values['seo_title_news'] ?? ''),
                    'seo_desc_news'   => FormTool::simpleClean($values['seo_desc_news'] ?? ''),
                    'published_news'  => (int)($values['published_news'] ?? 0),
                    'last_update'     => date('Y-m-d H:i:s')
                ];

                if (!$db->saveNewsContent($newId, (int)$idLang, $data)) {
                    $success = false;
                }
            }
        }

        // Enregistrement des tags
        $selectedTags = $_POST['tags'] ?? [];
        $db->syncNewsTags($newId, $selectedTags);

        if ($success) {
            $this->jsonResponse(true, 'Actualité créée avec succès.', [
                'success' => true,
                'type'    => 'add',
                'id'      => $newId
            ]);
        } else {
            $this->jsonResponse(false, 'Actualité créée, mais erreur sur les contenus.', ['success' => false]);
        }
    }

    public function edit(): void
    {
        $id = (int)($_REQUEST['edit'] ?? $_POST['id_news'] ?? 0);
        $db = new NewsDb();
        $idLangue = (int)$this->defaultLang['id_lang'];
        $activeLangs = (new LangDb())->fetchLanguages();

        if (Request::isMethod('POST')) {
            $this->processSave($db, $id);
            return;
        }

        $newsData = $db->fetchNewsById($id);
        if (!$newsData) return;

        $controller = StringTool::strtolower($_GET['controller'] ?? 'news');

        // --- UTILISATION DE DateTool ICI ---
        // Si la date est vide, on prend "now", sinon on prend la date de la BDD. On formatte en SQL (Y-m-d).
        $rawDate = !empty($newsData['date_publish']) ? $newsData['date_publish'] : 'now';
        $datePublish = DateTool::getDate($rawDate, 'sql');

        foreach ($activeLangs as $langId => $iso) {
            $slug = $newsData['content'][$langId]['url_news'] ?? '';

            if ($slug !== '') {
                $newsData['content'][$langId]['public_url'] = '/' . $iso . '/' . $controller . '/' . $datePublish . '/' . $id . '-' . $slug . '/';
            } else {
                $newsData['content'][$langId]['public_url'] = '';
            }
        }

        $this->view->assign([
            'news_data'     => $newsData,
            'langs'         => $activeLangs,
            'all_tags'          => $db->fetchAllTagsForLang($idLangue),
            'selected_tags' => $db->fetchNewsTagsIds($id),
            'idcolumn'      => 'id_news',
            'hashtoken'     => $this->session->getToken(),
            'url_token'     => urlencode($this->session->getToken()),
        ]);

        $this->view->display('news/edit.tpl');
    }

    private function processSave(NewsDb $db, int $idNews): void
    {
        $token = Request::isPost('hashtoken') ? $_POST['hashtoken'] : '';
        if (!$this->session->validateToken($token)) {
            $this->jsonResponse(false, 'Session expirée.', ['success' => false]);
        }

        if ($idNews === 0) {
            $this->jsonResponse(false, 'Identifiant manquant.', ['success' => false]);
        }

        $success = true;
        $publicUrls = [];
        $activeLangs = (new LangDb())->getFrontendLanguages();
        $controller = StringTool::strtolower($_GET['controller'] ?? 'news');

        $mainData = [
            'date_publish'     => !empty($_POST['date_publish']) ? $_POST['date_publish'] : null,
            'date_event_start' => !empty($_POST['date_event_start']) ? $_POST['date_event_start'] : null,
            'date_event_end'   => !empty($_POST['date_event_end']) ? $_POST['date_event_end'] : null,
        ];

        // --- UTILISATION DE DateTool ICI AUSSI ---
        $rawDate = $mainData['date_publish'] ?: 'now';
        $datePublish = DateTool::getDate($rawDate, 'sql');

        if (!$db->updateNewsStructure($idNews, $mainData)) {
            $success = false;
        }

        if (isset($_POST['content']) && is_array($_POST['content'])) {
            foreach ($_POST['content'] as $idLang => $values) {
                $iso = $activeLangs[(int)$idLang] ?? 'fr';

                $url = trim($values['url_news'] ?? '');
                if ($url === '') {
                    $url = Url::clean($values['name_news'] ?? '');
                } else {
                    $url = Url::clean($url);
                }

                if ($url !== '') {
                    $publicUrls[$idLang] = '/' . $iso . '/' . $controller . '/' . $datePublish . '/' . $idNews . '-' . $url . '/';
                }

                $data = [
                    'name_news'       => FormTool::simpleClean($values['name_news'] ?? ''),
                    'longname_news'   => FormTool::simpleClean($values['longname_news'] ?? ''),
                    'url_news'        => $url,
                    'resume_news'     => FormTool::simpleClean($values['resume_news'] ?? ''),
                    'content_news'    => $values['content_news'] ?? '',
                    'link_label_news' => FormTool::simpleClean($values['link_label_news'] ?? ''),
                    'link_title_news' => FormTool::simpleClean($values['link_title_news'] ?? ''),
                    'seo_title_news'  => FormTool::simpleClean($values['seo_title_news'] ?? ''),
                    'seo_desc_news'   => FormTool::simpleClean($values['seo_desc_news'] ?? ''),
                    'published_news'  => (int)($values['published_news'] ?? 0),
                    'last_update'     => date('Y-m-d H:i:s')
                ];

                if (!$db->saveNewsContent($idNews, (int)$idLang, $data)) {
                    $success = false;
                } else {
                    // 🟢 AJOUT : Enregistrement dans l'historique si le contenu n'est pas vide
                    if (!empty($data['content_news'])) {
                        $revDb = new RevisionsDb();
                        // Paramètres : item_type, item_id, id_lang, nom_du_champ, contenu
                        $revDb->saveRevision('news', $idNews, (int)$idLang, 'content_news', $data['content_news']);
                    }
                }
            }
        }

        $selectedTags = $_POST['tags'] ?? [];
        $db->syncNewsTags($idNews, $selectedTags);

        if ($success) {
            $this->jsonResponse(true, 'Actualité mise à jour avec succès.', [
                'success'     => true,
                'type'        => 'update',
                'id'          => $idNews,
                'public_urls' => $publicUrls
            ]);
        } else {
            $this->jsonResponse(false, 'Erreur SQL lors de l\'enregistrement.', ['success' => false]);
        }
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
            $db = new NewsDb();
            if ($db->deleteNews($cleanIds)) {
                $this->sendJsonResponse([
                    'success' => true,
                    'message' => count($cleanIds) > 1 ? 'Actualités supprimées.' : 'Actualité supprimée.',
                    'ids' => $cleanIds
                ]);
            }
        }
        $this->sendJsonResponse(['success' => false, 'message' => 'Aucune sélection.']);
    }

    // ==========================================================
    // GESTION GALERIE (Identique à PagesController)
    // ==========================================================

    public function processUploadImages(): void
    {
        $idNews = (int)($_POST['id'] ?? 0);

        if ($idNews <= 0 || empty($_FILES['img_multiple']['name'][0])) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Aucun fichier reçu.']);
        }

        $db = new NewsDb();
        $newsData = $db->fetchNewsById($idNews);
        $idLangue = (int)$this->defaultLang['id_lang'];
        $slug = !empty($newsData['content'][$idLangue]['url_news']) ? $newsData['content'][$idLangue]['url_news'] : 'news';

        $uploadTool = new UploadTool();
        $lastImageId = $db->getLastImageId($idNews);

        $results = $uploadTool->multipleImageUpload(
            'news', 'news', 'upload/news', [(string)$idNews],
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
                if ($db->insertImage($idNews, $res['file'])) $uploadedCount++;
            } else {
                $errors[] = $res['msg'];
            }
        }

        if ($uploadedCount > 0) {
            $this->sendJsonResponse(['success' => true, 'message' => "$uploadedCount image(s) ajoutée(s).", 'uploaded' => $uploadedCount]);
        } else {
            $msg = !empty($errors) ? implode(', ', $errors) : 'Erreur upload.';
            $this->sendJsonResponse(['success' => false, 'message' => $msg]);
        }
    }

    public function processOrderImages(): void
    {
        $imageIds = $_POST['image'] ?? [];
        if (!empty($imageIds) && is_array($imageIds)) {
            $db = new NewsDb();
            if ($db->reorderImages($imageIds)) {
                $this->jsonResponse(true, 'Ordre sauvegardé.', ['type' => 'order_success']);
            }
        }
        $this->jsonResponse(false, 'Erreur ordre.');
    }

    public function processSetDefaultImage(): void
    {
        $idNews = (int)($_POST['edit'] ?? 0);
        $idImg = (int)($_POST['id_img'] ?? 0);

        if ($idNews > 0 && $idImg > 0) {
            $db = new NewsDb();
            if ($db->setDefaultImage($idNews, $idImg)) {
                $this->jsonResponse(true, 'Image par défaut mise à jour.', ['type' => 'update']);
            }
        }
        $this->jsonResponse(false, 'Erreur image défaut.');
    }

    public function processDeleteImage(): void
    {
        $ids = $_POST['ids'] ?? [];
        if (empty($ids) && isset($_POST['id_img'])) $ids = [$_POST['id_img']];
        $idNews = (int)($_POST['id_news'] ?? 0);

        if (!empty($ids)) {
            $db = new NewsDb();
            $configDb = new ConfigDb();
            $urlTool = new UrlTool();

            $configs = $configDb->fetchImageSizes('news', 'news');
            $uploadDir = $urlTool->dirUpload('upload/news/' . $idNews, true);

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
                $this->jsonResponse(true, "$deletedCount image(s) supprimée(s).", ['type' => 'delete_success']);
            }
        }
        $this->jsonResponse(false, 'Erreur suppression.');
    }

    public function getImages(): void
    {
        if (ob_get_length()) ob_clean();
        $id = (int)($_GET['edit'] ?? $_GET['id_news'] ?? 0);

        $db = new NewsDb();
        $images = $db->fetchImagesByNews($id);
        $imageTool = new ImageTool();
        $formatted = $imageTool->setModuleImages('news', 'news', $images, $id);

        $this->view->assign([
            'images'    => $formatted,
            'id_news'   => $id,
            'current_c' => 'News'
        ]);

        $html = $this->view->fetch('components/gallery.tpl');
        $this->jsonResponse(true, 'OK', ['result' => $html]);
    }

    public function getImgMeta(): void
    {
        if (ob_get_length()) ob_clean();
        $idImg = (int)($_GET['id_img'] ?? 0);

        $db = new NewsDb();
        $langs = (new LangDb())->fetchLanguages();
        $meta = $db->fetchImageMeta($idImg);
        $currentController = ucfirst($_GET['controller'] ?? 'News');

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
        $db = new NewsDb();
        $success = true;

        if ($idImg > 0 && isset($_POST['meta']) && is_array($_POST['meta'])) {
            foreach ($_POST['meta'] as $idLang => $values) {
                $data = [
                    'title_img'   => FormTool::simpleClean($values['title_img'] ?? ''),
                    'alt_img'     => FormTool::simpleClean($values['alt_img'] ?? ''),
                    'caption_img' => FormTool::simpleClean($values['caption_img'] ?? '')
                ];
                if (!$db->saveImageMeta($idImg, (int)$idLang, $data)) $success = false;
            }
        } else {
            $success = false;
        }

        $this->jsonResponse($success, $success ? 'Métadonnées sauvegardées.' : 'Erreur sauvegarde.');
    }
    /**
     * Affiche la liste des actualités dans une fenêtre modale allégée pour TinyMCE
     */
    public function tinymcePopup(): void
    {
        $db = new NewsDb();

        // 🟢 1. LECTURE DE LA LANGUE DEMANDÉE PAR TINYMCE
        // Si TinyMCE n'envoie rien (cas rare), on retombe sur la langue par défaut
        $requestedLangId = (int)($_GET['lang_id'] ?? $this->defaultLang['id_lang']);

        $activeLangs = (new LangDb())->getFrontendLanguages();

        // On s'assure que l'ISO correspond bien à la langue demandée (ex: 'en', 'fr')
        $iso = $activeLangs[$requestedLangId] ?? 'fr';

        // Base de l'URL frontend
        $controllerSlug = \Magepattern\Component\Tool\StringTool::strtolower('news');

        // 🟢 2. REQUÊTE CIBLÉE SUR LA BONNE LANGUE
        $result = $db->fetchAllNews(1, 100, [], $requestedLangId);

        $newsList = [];
        if ($result !== false && !empty($result['data'])) {
            foreach ($result['data'] as $news) {

                $rawDate = !empty($news['date_publish']) ? $news['date_publish'] : 'now';
                $datePublish = \Magepattern\Component\Tool\DateTool::getDate($rawDate, 'sql');

                // Fallback de sécurité : si le nom est vide (car l'actu n'est pas encore traduite dans cette langue)
                // on affiche un marqueur visuel pour alerter le rédacteur
                $title = !empty($news['name_news']) ? $news['name_news'] : '⚠️ (Non traduit)';
                $slug = !empty($news['url_news']) ? $news['url_news'] : \Magepattern\Component\HTTP\Url::clean($title);

                // L'URL publique sera générée avec le bon /iso/ (ex: /en/news/...)
                $publicUrl = '/' . $iso . '/' . $controllerSlug . '/' . $datePublish . '/' . $news['id_news'] . '-' . $slug . '/';

                $newsList[] = [
                    'id'    => $news['id_news'],
                    'title' => $title,
                    'date'  => $datePublish,
                    'url'   => $publicUrl
                ];
            }
        }

        $this->view->assign([
            'newsList'  => $newsList,
            'iso_lang'  => strtoupper($iso), // Pour l'afficher dans le titre de la popup
            'hashtoken' => $this->session->getToken()
        ]);

        $this->view->display('news/tinymce_popup.tpl');
    }
    private function sendJsonResponse(array $data): void
    {
        header('Content-Type: application/json');
        echo $this->json->encode($data);
        exit;
    }
}