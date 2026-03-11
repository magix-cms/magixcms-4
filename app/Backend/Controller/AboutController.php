<?php

declare(strict_types=1);

namespace App\Backend\Controller;

use App\Backend\Db\AboutDb;
use App\Component\Db\ConfigDb;
use App\Component\Routing\UrlTool;
use App\Component\File\UploadTool;
use App\Component\File\ImageTool;
use Magepattern\Component\HTTP\Request;
use Magepattern\Component\Tool\FormTool;
use Magepattern\Component\HTTP\Url;
use Magepattern\Component\Tool\StringTool;
use Magepattern\Component\File\FileTool;

class AboutController extends BaseController
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

        $db = new AboutDb();
        $idLangue = (int)$this->defaultLang['id_lang'];

        // 1. Définition des colonnes à afficher
        $targetColumns = ['id_about', 'parent_about', 'name_about', 'published_about', 'date_register'];

        // 2. Récupération du schéma brut des tables
        $rawScheme = array_merge(
            $db->getTableScheme('mc_about'),
            $db->getTableScheme('mc_about_content')
        );
        // Ajout manuel de la colonne virtuelle "parent_about" (créée par le JOIN dans AboutDb)
        $rawScheme[] = ['column' => 'parent_about', 'type' => 'varchar(255)'];

        // 3. Configuration de l'affichage (Titres, Types, Classes)
        $associations = [
            'id_about' => ['title' => 'ID', 'type' => 'text', 'class' => 'text-center text-muted small px-2'],
            'parent_about' => ['title' => 'Parent', 'type' => 'text', 'class' => 'text-muted small text-nowrap'],
            'name_about' => ['title' => 'Titre', 'type' => 'text', 'class' => 'w-50 fw-bold'],
            'published_about' => ['title' => 'Statut', 'type' => 'bin', 'class' => 'text-center px-3', 'enum' => 'status_'],
            'date_register' => ['title' => 'Date', 'type' => 'date', 'class' => 'text-center text-nowrap text-muted small']
        ];

        // 4. Génération du schéma pour la vue
        $this->getScheme($rawScheme, $targetColumns, $associations);

        // 5. Récupération des données
        $page   = Request::isGet('page') ? (int)$_GET['page'] : 1;
        $limit  = Request::isGet('offset') ? (int)$_GET['offset'] : 25;
        $search = $_GET['search'] ?? [];

        $result = $db->fetchAllAbout($page, $limit, $search, $idLangue);

        $meta = [];

        if ($result !== false) {
            $this->getItems('about_list', $result['data'], true, $result['meta']);
            $meta = $result['meta']; // <-- ON RÉCUPÈRE LE META ICI
        }

        $this->view->assign([
            'idcolumn'   => 'id_about',
            'hashtoken'  => $this->session->getToken(),
            'url_token'  => urlencode($this->session->getToken()),
            'get_search' => $search,
            'sortable'   => empty($search), // Le tri manuel n'est possible que si on ne cherche pas
            'checkbox'   => true,
            'edit'       => true,
            'dlt'        => true,
            'meta'       => $meta
        ]);

        $this->view->display('about/index.tpl');
    }

    // --- AJOUT ---

    public function add(): void
    {
        $db = new AboutDb();
        $idLangue = (int)$this->defaultLang['id_lang'];
        $activeLangs = $db->fetchLanguages();

        if (Request::isMethod('POST')) {
            $this->processAdd($db);
            return;
        }

        // Récupération de la liste pour le select parent (C'est ici que ça plantait avant)
        $aboutSelect = $db->fetchAllAboutForSelect($idLangue);

        $this->view->assign([
            'aboutSelect' => $aboutSelect,
            'langs'       => $activeLangs,
            'hashtoken'   => $this->session->getToken(),
            'url_token'   => urlencode($this->session->getToken())
        ]);
        $this->view->display('about/add.tpl');
    }

    private function processAdd(AboutDb $db): void
    {
        $token = $_POST['hashtoken'] ?? '';
        if (!$this->session->validateToken($token)) {
            $this->jsonResponse(false, 'Session expirée.');
        }

        // 1. Structure
        $idParent = (int)($_POST['id_parent'] ?? 0);
        $structureData = [
            'id_parent'   => ($idParent > 0) ? $idParent : null,
            'menu_about'  => (int)($_POST['menu_about'] ?? 1),
            'order_about' => 0
        ];

        $newId = $db->insertAboutStructure($structureData);
        if (!$newId) {
            $this->jsonResponse(false, 'Erreur création structure.');
        }

        // 2. Contenus
        $activeLangs = $db->fetchLanguages();
        $success = true;

        if (isset($_POST['content']) && is_array($_POST['content'])) {
            foreach ($_POST['content'] as $idLang => $values) {
                if (!isset($activeLangs[(int)$idLang])) continue;

                // Gestion URL
                $url = trim($values['url_about'] ?? '');
                if (empty($url)) {
                    $url = Url::clean($values['name_about'] ?? '');
                } else {
                    $url = Url::clean($url);
                }

                $data = [
                    'name_about'       => FormTool::simpleClean($values['name_about'] ?? ''),
                    'longname_about'   => FormTool::simpleClean($values['longname_about'] ?? ''),
                    'url_about'        => $url,
                    'resume_about'     => FormTool::simpleClean($values['resume_about'] ?? ''),
                    'content_about'    => $values['content_about'] ?? '', // HTML allowed
                    'link_label_about' => FormTool::simpleClean($values['link_label_about'] ?? ''),
                    'link_title_about' => FormTool::simpleClean($values['link_title_about'] ?? ''),
                    'seo_title_about'  => FormTool::simpleClean($values['seo_title_about'] ?? ''),
                    'seo_desc_about'   => FormTool::simpleClean($values['seo_desc_about'] ?? ''),
                    'published_about'  => (int)($values['published_about'] ?? 0),
                    'last_update'      => date('Y-m-d H:i:s')
                ];

                if (!$db->saveAboutContent($newId, (int)$idLang, $data)) {
                    $success = false;
                }
            }
        }

        if ($success) {
            // CORRECTION : Format strict attendu par MagixForms pour la création
            $this->jsonResponse(true, 'La fiche a été créée avec succès.', [
                'success' => true,
                'type'    => 'add',
                'id'      => $newId
            ]);
        } else {
            $this->jsonResponse(false, 'Erreur lors de la sauvegarde des contenus.', ['success' => false]);
        }
    }

    // --- ÉDITION ---

    public function edit(): void
    {
        $id = (int)($_REQUEST['edit'] ?? $_POST['id_about'] ?? 0);
        $db = new AboutDb();
        $activeLangs = $db->fetchLanguages();

        if (Request::isMethod('POST')) {
            $this->processSave($db, $id);
            return;
        }

        $aboutData = $db->fetchAboutById($id);
        if (!$aboutData) return;

        $idLangue = (int)$this->defaultLang['id_lang'];
        $aboutSelect = $db->fetchAllAboutForSelect($idLangue);

        // ------------------------------------------------------------------
        // 🟢 NOUVEAU : On génère le schéma pour le tableau des sous-pages
        // ------------------------------------------------------------------
        $targetColumns = ['id_about', 'name_about', 'published_about', 'date_register'];

        $rawScheme = array_merge(
            $db->getTableScheme('mc_about'),
            $db->getTableScheme('mc_about_content')
        );

        $associations = [
            'id_about' => ['title' => 'ID', 'type' => 'text', 'class' => 'text-center text-muted small px-2'],
            'name_about' => ['title' => 'Titre', 'type' => 'text', 'class' => 'w-50 fw-bold'],
            'published_about' => ['title' => 'Statut', 'type' => 'bin', 'class' => 'text-center px-3', 'enum' => 'status_'],
            'date_register' => ['title' => 'Date', 'type' => 'date', 'class' => 'text-center text-nowrap text-muted small']
        ];

        $this->getScheme($rawScheme, $targetColumns, $associations);

        // ------------------------------------------------------------------
        // 🟢 NOUVEAU : Récupération et formatage des données
        // ------------------------------------------------------------------
        $children = $db->fetchAboutByParent($id, $idLangue);

        // On formate les données (pour que les switchs on/off et les dates s'affichent bien)
        if (!empty($children)) {
            // Cela crée automatiquement la variable Smarty $subpages
            $this->getItems('subpages', $children, true);
        } else {
            $this->view->assign('subpages', []);
        }

        // URL Preview
        $controller = StringTool::strtolower($_GET['controller'] ?? 'about');
        foreach ($activeLangs as $langId => $iso) {
            $slug = $aboutData['content'][$langId]['url_about'] ?? '';
            $aboutData['content'][$langId]['public_url'] = '/' . $iso . '/' . $controller . '/' . $id . '-' . $slug . '/';
        }

        $this->view->assign([
            'idcolumn'    => 'id_about', // <-- REQUIS pour les boutons "Supprimer" et "Editer" du tableau
            'page_data'   => $aboutData,
            'aboutSelect' => $aboutSelect,
            // 'subpages' => déjà assigné par getItems() juste au-dessus
            'langs'       => $activeLangs,
            'hashtoken'   => $this->session->getToken()
        ]);

        $this->view->display('about/edit.tpl');
    }

    private function processSave(AboutDb $db, int $idAbout): void
    {
        $token = $_POST['hashtoken'] ?? '';
        if (!$this->session->validateToken($token)) {
            $this->jsonResponse(false, 'Session expirée.');
        }

        $idParent = (int)($_POST['id_parent'] ?? 0);
        $structureData = [
            'id_parent'  => ($idParent > 0 && $idParent !== $idAbout) ? $idParent : null,
            'menu_about' => (int)($_POST['menu_about'] ?? 0)
        ];

        $db->updateAboutStructure($idAbout, $structureData);
        $success = true;
        $publicUrls = [];
        $activeLangs = $db->fetchLanguages();
        $controller = StringTool::strtolower($_GET['controller'] ?? 'about');

        if (isset($_POST['content']) && is_array($_POST['content'])) {
            foreach ($_POST['content'] as $idLang => $values) {
                $iso = $activeLangs[(int)$idLang] ?? 'fr';

                $url = trim($values['url_about'] ?? '');
                if (empty($url)) {
                    $url = Url::clean($values['name_about'] ?? '');
                } else {
                    $url = Url::clean($url);
                }

                $publicUrls[$idLang] = '/' . $iso . '/' . $controller . '/' . $idAbout . '-' . $url . '/';

                $data = [
                    'name_about'       => FormTool::simpleClean($values['name_about'] ?? ''),
                    'longname_about'   => FormTool::simpleClean($values['longname_about'] ?? ''),
                    'url_about'        => $url,
                    'resume_about'     => FormTool::simpleClean($values['resume_about'] ?? ''),
                    'content_about'    => $values['content_about'] ?? '',
                    'link_label_about' => FormTool::simpleClean($values['link_label_about'] ?? ''),
                    'link_title_about' => FormTool::simpleClean($values['link_title_about'] ?? ''),
                    'seo_title_about'  => FormTool::simpleClean($values['seo_title_about'] ?? ''),
                    'seo_desc_about'   => FormTool::simpleClean($values['seo_desc_about'] ?? ''),
                    'published_about'  => (int)($values['published_about'] ?? 0),
                    'last_update'      => date('Y-m-d H:i:s')
                ];
                if (!$db->saveAboutContent($idAbout, (int)$idLang, $data)) $success = false;
            }
        }

        // CORRECTION : On ajoute le 'type' et l''id' attendus par MagixForms
        $this->jsonResponse($success, $success ? 'Mise à jour réussie' : 'Erreur SQL', [
            'success'     => $success,
            'type'        => 'update',
            'id'          => $idAbout,
            'public_urls' => $publicUrls
        ]);
    }

    // --- IMAGES ---

    public function processUploadImages(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) $this->jsonResponse(false, 'ID manquant.');

        $db = new AboutDb();
        $uploadTool = new UploadTool();
        $lastId = $db->getLastImageId($id);

        $results = $uploadTool->multipleImageUpload('about', 'about', 'upload/about', [(string)$id], [
            'postKey' => 'img_multiple',
            'suffix'  => $lastId,
            'suffix_increment' => true
        ]);

        $count = 0;
        foreach ($results as $res) {
            if ($res['status']) {
                if ($db->insertImage($id, $res['file'])) $count++;
            }
        }
        $this->jsonResponse(true, "$count images.", ['uploaded' => $count]);
    }

    public function getImages(): void
    {
        if (ob_get_length()) ob_clean();
        $id = (int)($_GET['edit'] ?? 0);

        $db = new AboutDb();
        $images = $db->fetchImagesByAbout($id);
        $imageTool = new ImageTool();
        $formatted = $imageTool->setModuleImages('about', 'about', $images, $id);

        $this->view->assign([
            'images'    => $formatted,
            'id_about'  => $id,
            'current_c' => 'About' // <--- LA CORRECTION EST ICI
        ]);

        $html = $this->view->fetch('components/gallery.tpl');
        $this->jsonResponse(true, 'OK', ['result' => $html]);
    }

    public function processDeleteImage(): void
    {
        $ids = $_POST['ids'] ?? [];
        $idAbout = (int)($_POST['id_pages'] ?? 0);

        if (!empty($ids)) {
            $db = new AboutDb();
            $configDb = new ConfigDb();
            $urlTool = new UrlTool();
            $configs = $configDb->fetchImageSizes('about', 'about');
            $uploadDir = $urlTool->dirUpload('upload/about/' . $idAbout, true);

            foreach ($ids as $idImg) {
                $imgData = $db->deleteAboutImage((int)$idImg);
                if ($imgData) {
                    $filename = $imgData['name_img'];
                    $nameNoExt = pathinfo($filename, PATHINFO_FILENAME);
                    $files = [$uploadDir . $filename, $uploadDir . $nameNoExt . '.webp'];
                    foreach ($configs as $conf) {
                        $p = $conf['prefix'] . '_';
                        $files[] = $uploadDir . $p . $filename;
                        $files[] = $uploadDir . $p . $nameNoExt . '.webp';
                    }
                    FileTool::remove($files);
                }
            }
            $this->jsonResponse(true, 'Supprimé');
        }
        $this->jsonResponse(false, 'Erreur');
    }

    // --- AUTRES ACTIONS ---

    public function processOrderImages(): void {
        $imageIds = $_POST['image'] ?? [];
        if (!empty($imageIds)) (new AboutDb())->reorderImages($imageIds);
        $this->jsonResponse(true, 'Ordre sauvegardé');
    }

    public function processSetDefaultImage(): void {
        $id = (int)$_POST['edit'];
        $img = (int)$_POST['id_img'];
        (new AboutDb())->setDefaultImage($id, $img);
        $this->jsonResponse(true, 'Défaut mis à jour');
    }

    public function delete(): void {
        $ids = $_POST['ids'] ?? [$_POST['id'] ?? null];
        $cleanIds = array_filter(array_map('intval', (array)$ids));

        if (!empty($cleanIds)) {
            if ((new AboutDb())->deleteAbout($cleanIds)) {
                $this->jsonResponse(true, 'Supprimé');
            }
        }
        $this->jsonResponse(false, 'Erreur');
    }

    // Pour le tri des éléments dans la liste (Drag and drop des lignes)
    public function reorder(): void {
        // Logique similaire à PagesController::reorder
        // Récupérer le JSON, parser, et appeler updateOrderAbout
        $input = file_get_contents('php://input');
        $data = $this->json->decode($input);
        if (isset($data['order']) && is_array($data['order'])) {
            $db = new AboutDb();
            $pos = 1;
            foreach ($data['order'] as $id) {
                $db->updateOrderAbout((int)$id, $pos++);
            }
            $this->jsonResponse(true, 'Ordre mis à jour');
        }
        $this->jsonResponse(false, 'Erreur');
    }
    /**
     * AJAX : Récupère la modale d'édition des métadonnées d'une image
     */
    /**
     * AJAX : Récupère la modale d'édition des métadonnées d'une image
     */
    public function getImgMeta(): void
    {
        if (ob_get_length()) ob_clean();

        $idImg = (int)($_GET['id_img'] ?? 0);

        // ATTENTION : Laissez PagesDb() dans PagesController, et AboutDb() dans AboutController
        $db = new AboutDb();

        $langs = $db->fetchLanguages();
        $meta = $db->fetchImageMeta($idImg);

        // NOUVEAU : On récupère dynamiquement le nom du contrôleur depuis l'URL (GET)
        // ex: Si on est sur l'URL index.php?controller=About, ça vaudra 'About'
        $currentController = ucfirst($_GET['controller'] ?? 'About');

        $this->view->assign([
            'img_id'          => $idImg,
            'langs'           => $langs,
            'meta'            => $meta,
            'controller_name' => $currentController // <-- La variable est maintenant dynamique !
        ]);

        $html = $this->view->fetch('components/modal-img-meta.tpl');

        $this->jsonResponse(true, 'OK', ['html' => $html]);
    }

    /**
     * AJAX : Sauvegarde les métadonnées de l'image
     */
    public function processSaveImgMeta(): void
    {
        if (ob_get_length()) ob_clean();

        $idImg = (int)($_POST['id_img'] ?? 0);
        $db = new AboutDb();
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

        $this->jsonResponse($success, $success ? 'Métadonnées sauvegardées avec succès.' : 'Erreur lors de la sauvegarde.');
    }
}