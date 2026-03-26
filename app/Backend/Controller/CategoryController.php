<?php

declare(strict_types=1);

namespace App\Backend\Controller;

use App\Backend\Db\CategoryDb;
use App\Component\Db\ConfigDb;
use App\Component\Routing\UrlTool;
use App\Component\File\UploadTool;
use App\Component\File\ImageTool;
use Magepattern\Component\HTTP\Request;
use Magepattern\Component\Tool\FormTool;
use Magepattern\Component\HTTP\Url;
use Magepattern\Component\Tool\StringTool;
use Magepattern\Component\File\FileTool;
use App\Backend\Db\ProductDb;
use App\Backend\Db\RevisionsDb;


class CategoryController extends BaseController
{
    public function run(): void
    {
        // --- 1. ROUTEUR D'ACTION ---
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
        $this->index();
    }

    private function index(): void
    {
        $idLangue = (int)$this->defaultLang['id_lang'];
        $db = new CategoryDb();

        // Les colonnes que nous voulons afficher dans le tableau
        $targetColumns = ['id_cat', 'parent_cat', 'name_cat', 'published_cat', 'date_register'];

        $rawScheme = array_merge(
            $db->getTableScheme('mc_catalog_cat'),
            $db->getTableScheme('mc_catalog_cat_content')
        );

        // On ajoute l'alias parent_cat manuellement pour le DataHelperTrait
        $rawScheme[] = ['column' => 'parent_cat', 'type' => 'varchar(255)'];

        $associations = [
            'id_cat'          => ['title' => 'id', 'type' => 'text', 'class' => 'text-center text-muted small px-2'],
            'parent_cat'      => ['title' => 'parent', 'type' => 'text', 'class' => 'text-muted small text-nowrap'],
            'name_cat'        => ['title' => 'name', 'type' => 'text', 'class' => 'w-50 fw-bold'],
            'published_cat'   => ['title' => 'status', 'type' => 'bin', 'class' => 'text-center px-3', 'enum' => 'status_'],
            'date_register'   => ['title' => 'date', 'type' => 'date', 'class' => 'text-center text-nowrap text-muted small']
        ];

        $this->getScheme($rawScheme, $targetColumns, $associations);

        $page   = Request::isGet('page') ? (int)$_GET['page'] : 1;
        $limit  = Request::isGet('offset') ? (int)$_GET['offset'] : 25;
        $search = $_GET['search'] ?? [];

        $result = $db->fetchAllCategories($page, $limit, $search, $idLangue);

        $meta = [];
        if ($result !== false) {
            $this->getItems('categories', $result['data'], true, $result['meta']);
            $meta = $result['meta'];
        }

        $token = $this->session->getToken();

        $this->view->assign([
            'idcolumn'   => 'id_cat',
            'hashtoken'  => $token,
            'url_token'  => urlencode($token),
            'get_search' => $search,
            'sortable'   => false, // Passez à true si vous gérez le drag & drop dans la liste principale
            'checkbox'   => true,
            'edit'       => true,
            'dlt'        => true,
            'meta'       => $meta
        ]);

        $this->view->display('category/index.tpl');
    }

    // ==========================================
    // AFFICHAGE DES FORMULAIRES
    // ==========================================

    public function add(): void
    {
        if (Request::isMethod('POST')) {
            $this->processAdd();
            return;
        }

        $db = new CategoryDb();
        $idLangDefault = (int)$this->defaultLang['id_lang'];

        $this->view->assign([
            // On récupère toutes les catégories pour le menu déroulant "Parent"
            'category_select' => $db->fetchAllCategoriesForSelect($idLangDefault),
            'hashtoken'       => $this->session->getToken()
        ]);

        $this->view->display('category/add.tpl');
    }

    public function edit(): void
    {
        $id = (int)($_REQUEST['edit'] ?? $_POST['id_cat'] ?? 0);
        $db = new CategoryDb();

        if (Request::isMethod('POST')) {
            $this->processEdit($db, $id);
            return;
        }

        $category = $db->fetchCategoryById($id);
        if (!$category) {
            header('Location: index.php?controller=Category');
            exit;
        }

        $idLangDefault = (int)$this->defaultLang['id_lang'];
        $activeLangs = $db->fetchLanguages();

        // ==========================================
        // 1. SCHEME ET DONNÉES : SOUS-CATÉGORIES
        // ==========================================
        $rawSchemeCat = array_merge(
            $db->getTableScheme('mc_catalog_cat'),
            $db->getTableScheme('mc_catalog_cat_content')
        );
        $rawSchemeCat[] = ['column' => 'parent_cat', 'type' => 'varchar(255)'];

        $associationsCat = [
            'id_cat'          => ['title' => 'id', 'type' => 'text', 'class' => 'text-center text-muted small px-2'],
            'parent_cat'      => ['title' => 'parent', 'type' => 'text', 'class' => 'text-muted small text-nowrap'],
            'name_cat'        => ['title' => 'name', 'type' => 'text', 'class' => 'w-50 fw-bold'],
            'published_cat'   => ['title' => 'status', 'type' => 'bin', 'class' => 'text-center px-3', 'enum' => 'status_'],
            'date_register'   => ['title' => 'date', 'type' => 'date', 'class' => 'text-center text-nowrap text-muted small']
        ];

        // On génère le scheme pour les catégories
        $this->getScheme($rawSchemeCat, ['id_cat', 'parent_cat', 'name_cat', 'published_cat', 'date_register'], $associationsCat);
        $schemeCat = $this->view->getTemplateVars('scheme');
        $columnsCat = $this->view->getTemplateVars('columns');

        // On récupère et formate les enfants
        $children = $db->fetchCategoriesByParent($id, $idLangDefault);
        $subcategoriesData = [];
        if ($children !== false && !empty($children)) {
            $subcategoriesData = $this->getItems('subcategories', $children, true);
        }

        // --- Calcul des URLs publiques ---
        $urlTool = new UrlTool();
        foreach ($activeLangs as $langId => $iso) {
            $slug = $category['content'][$langId]['url_cat'] ?? '';
            $category['content'][$langId]['public_url'] = $urlTool->buildUrl([
                'iso'  => $iso,
                'type' => 'category',
                'id'   => $id,
                'url'  => $slug
            ]);
        }

        // ==========================================
        // 2. SCHEME ET DONNÉES : PRODUITS
        // ==========================================
        $productDb = new ProductDb();
        $productsList = $productDb->fetchProductsByCategory($id, $idLangDefault);

        $rawSchemeProd = [
            ['column' => 'id_product', 'type' => 'int'],
            ['column' => 'reference_p', 'type' => 'varchar'],
            ['column' => 'name_p', 'type' => 'varchar'],
            ['column' => 'published_p', 'type' => 'int']
        ];
        $associationsProd = [
            'id_product'  => ['title' => 'ID', 'type' => 'text', 'class' => 'text-center text-muted px-2'],
            'reference_p' => ['title' => 'Réf.', 'type' => 'text', 'class' => 'text-muted text-nowrap'],
            'name_p'      => ['title' => 'Produit', 'type' => 'text', 'class' => 'fw-bold w-50'],
            'published_p' => ['title' => 'Statut', 'type' => 'bin', 'class' => 'text-center px-3', 'enum' => 'status_']
        ];

        // On écrase l'ancien scheme pour générer celui des produits
        $this->getScheme($rawSchemeProd, ['id_product', 'reference_p', 'name_p', 'published_p'], $associationsProd);
        $schemeProd = $this->view->getTemplateVars('scheme');
        $columnsProd = $this->view->getTemplateVars('columns');

        // ==========================================
        // 3. ASSIGNATION À SMARTY
        // ==========================================
        $this->view->assign([
            'category'        => $category,
            'category_select' => $db->fetchAllCategoriesForSelect($idLangDefault),
            'subcategories'   => $subcategoriesData ?: $children,
            'scheme_cat'      => $schemeCat,   // Les colonnes des sous-catégories
            'columns_cat'     => $columnsCat,
            'langs'           => $activeLangs,
            'hashtoken'       => $this->session->getToken(),
            'products_list'   => $productsList,
            'scheme_prod'     => $schemeProd,  // Les colonnes des produits
            'columns_prod'    => $columnsProd,
        ]);

        $this->view->display('category/edit.tpl');
    }

    // ==========================================
    // TRAITEMENT DES DONNÉES (POST)
    // ==========================================

    private function processAdd(): void
    {
        $token = $_POST['hashtoken'] ?? '';
        if (!$this->session->validateToken($token)) {
            $this->jsonResponse(false, 'Session expirée.');
        }

        $db = new CategoryDb();

        $idParent = (int)($_POST['id_parent'] ?? 0);

        $structureData = [
            'id_parent' => $idParent > 0 ? $idParent : null,
            'menu_cat'  => (int)($_POST['menu_cat'] ?? 0)
        ];

        $newId = $db->insertCategoryStructure($structureData);

        if ($newId) {
            // On appelle la fonction, mais on se fiche du tableau retourné puisqu'on va rediriger
            $this->saveTranslations($db, $newId);

            $this->jsonResponse(true, 'La catégorie a été créée avec succès.', ['type' => 'add', 'id' => $newId]);
        } else {
            $this->jsonResponse(false, 'Erreur lors de la création de la catégorie.');
        }
    }

    private function processEdit(CategoryDb $db, int $id): void
    {
        $token = $_POST['hashtoken'] ?? '';
        if (!$this->session->validateToken($token)) {
            $this->jsonResponse(false, 'Session expirée.');
        }

        $idParent = (int)($_POST['id_parent'] ?? 0);

        if ($idParent === $id) {
            $this->jsonResponse(false, 'Une catégorie ne peut pas être son propre parent.');
        }

        $structureData = [
            'id_parent' => $idParent > 0 ? $idParent : null,
            'menu_cat'  => (int)($_POST['menu_cat'] ?? 0)
        ];

        if ($db->updateCategoryStructure($id, $structureData)) {

            // On récupère les URLs générées pour mettre à jour la vue dynamiquement
            $publicUrls = $this->saveTranslations($db, $id);

            $this->jsonResponse(true, 'La catégorie a été mise à jour.', [
                'type'        => 'update',
                'public_urls' => $publicUrls // <-- MagixForms utilisera ça pour rafraîchir les champs !
            ]);
        } else {
            $this->jsonResponse(false, 'Erreur lors de la mise à jour de la catégorie.');
        }
    }

    /**
     * Traite les traductions et RETOURNE les URLs publiques générées
     */
    private function saveTranslations(CategoryDb $db, int $idCat): array
    {
        // On s'assure d'avoir l'outil d'URL et les langues
        $urlTool = new \App\Component\Routing\UrlTool();
        // Si 'langs' n'est pas assigné dans la vue lors du POST, on les récupère en BDD
        $langs = $this->view->getTemplateVars('langs') ?? $db->fetchLanguages();

        $publicUrls = [];

        foreach ($langs as $idLang => $iso) {
            $nameCat = $_POST['name_cat'][$idLang] ?? '';

            if (!empty($nameCat)) {
                $urlCat = FormTool::simpleClean($_POST['url_cat'][$idLang] ?? '');
                if (empty($urlCat)) {
                    $urlCat = class_exists(Url::class) ? Url::clean($nameCat) : StringTool::strtolower(str_replace(' ', '-', $nameCat));
                }

                $contentData = [
                    'name_cat'       => FormTool::simpleClean($nameCat),
                    'longname_cat'   => FormTool::simpleClean($_POST['longname_cat'][$idLang] ?? ''),
                    'url_cat'        => $urlCat,
                    'resume_cat'     => $_POST['resume_cat'][$idLang] ?? '',
                    'content_cat'    => $_POST['content_cat'][$idLang] ?? '',
                    'seo_title_cat'  => FormTool::simpleClean($_POST['seo_title_cat'][$idLang] ?? ''),
                    'seo_desc_cat'   => FormTool::simpleClean($_POST['seo_desc_cat'][$idLang] ?? ''),
                    'link_label_cat' => FormTool::simpleClean($_POST['link_label_cat'][$idLang] ?? ''),
                    'link_title_cat' => FormTool::simpleClean($_POST['link_title_cat'][$idLang] ?? ''),
                    'published_cat'  => isset($_POST['published_cat'][$idLang]) ? 1 : 0
                ];

                $db->saveCategoryContent($idCat, $idLang, $contentData);

                // 🟢 AJOUT : Enregistrement dans l'historique si le contenu n'est pas vide
                if (!empty($contentData['content_cat'])) {
                    $revDb = new RevisionsDb();
                    // Paramètres : item_type, item_id, id_lang, nom_du_champ, contenu
                    $revDb->saveRevision('category', $idCat, (int)$idLang, 'content_cat', $contentData['content_cat']);
                }

                // --- NOUVEAU : On génère l'URL avec votre UrlTool ---
                $publicUrls[$idLang] = $urlTool->buildUrl([
                    'iso'  => $iso,
                    'type' => 'category',
                    'id'   => $idCat,
                    'url'  => $urlCat
                ]);
            }
        }

        return $publicUrls;
    }

    // ==========================================
    // GESTION DES IMAGES (LA GALERIE)
    // ==========================================

    public function processUploadImages(): void
    {
        $idCat = (int)($_POST['id'] ?? 0);

        if ($idCat <= 0 || empty($_FILES['img_multiple']['name'][0])) {
            $this->jsonResponse(false, 'Aucun fichier reçu.');
        }

        $db = new CategoryDb();

        $catData = $db->fetchCategoryById($idCat);
        $idLangue = (int)$this->defaultLang['id_lang'];
        $slug = !empty($catData['content'][$idLangue]['url_cat']) ? $catData['content'][$idLangue]['url_cat'] : 'category';

        $uploadTool = new UploadTool();
        $lastImageId = $db->getLastImageId($idCat);

        $results = $uploadTool->multipleImageUpload(
            'catalog', 'category', 'upload/category', [(string)$idCat],
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
                if ($db->insertImage($idCat, $res['file'])) {
                    $uploadedCount++;
                }
            } else {
                $errors[] = $res['msg'];
            }
        }

        if ($uploadedCount > 0) {
            $this->jsonResponse(true, "$uploadedCount image(s) ajoutée(s).", ['uploaded' => $uploadedCount]);
        } else {
            $msg = !empty($errors) ? implode(', ', $errors) : 'Erreur lors du traitement.';
            $this->jsonResponse(false, $msg);
        }
    }

    public function processDeleteImage(): void
    {
        $ids = $_POST['ids'] ?? [];
        if (empty($ids) && isset($_POST['id_img'])) {
            $ids = [$_POST['id_img']];
        }

        $idCat = (int)($_POST['id_cat'] ?? 0);

        if (!empty($ids)) {
            $db = new CategoryDb();
            $configDb = new ConfigDb();
            $urlTool = new UrlTool();

            $configs = $configDb->fetchImageSizes('category', 'category');
            $uploadDir = $urlTool->dirUpload('upload/category/' . $idCat, true);

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

        $this->jsonResponse(false, 'Erreur lors de la suppression.');
    }

    public function getImages(): void
    {
        if (ob_get_length()) ob_clean();
        $id = (int)($_GET['edit'] ?? $_GET['id_cat'] ?? 0);

        $db = new CategoryDb();
        $images = $db->fetchImagesByCategory($id);

        $imageTool = new ImageTool();
        $formatted = $imageTool->setModuleImages('category', 'category', $images, $id);

        $this->view->assign([
            'images'    => $formatted,
            'id_cat'    => $id,
            'current_c' => 'Category'
        ]);

        $html = $this->view->fetch('components/gallery.tpl');
        $this->jsonResponse(true, 'OK', ['result' => $html]);
    }

    public function getImgMeta(): void
    {
        if (ob_get_length()) ob_clean();
        $idImg = (int)($_GET['id_img'] ?? 0);

        $db = new CategoryDb();
        $langs = $db->fetchLanguages();
        $meta = $db->fetchImageMeta($idImg);
        $currentController = ucfirst($_GET['controller'] ?? 'Category');

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
        $db = new CategoryDb();
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
    /**
     * Intercepte la suppression depuis l'onglet "Produits associés" (Unlink)
     */
    public function processUnlinkProducts(): void
    {
        $idCat = (int)($_GET['id_cat'] ?? $_POST['id_cat'] ?? 0);
        $ids = $_POST['delete_item'] ?? $_POST['id_product'] ?? [];

        if ($idCat > 0 && !empty($ids)) {
            $productDb = new ProductDb();
            if ($productDb->unlinkProductsFromCategory($idCat, $ids)) {
                $this->jsonResponse(true, 'Les produits ont été retirés de la catégorie.', ['type' => 'delete_success']);
            }
        }
        $this->jsonResponse(false, 'Erreur lors de la dissociation.');
    }

    /**
     * Intercepte le Drag & Drop depuis l'onglet "Produits associés" (Order)
     */
    public function processOrderProducts(): void
    {
        $idCat = (int)($_GET['id_cat'] ?? $_POST['id_cat'] ?? 0);
        $ids = $_POST['item'] ?? []; // Tableau généré par SortableJS

        if ($idCat > 0 && !empty($ids)) {
            $productDb = new ProductDb();
            if ($productDb->reorderProductsInCategory($idCat, $ids)) {
                $this->jsonResponse(true, 'L\'ordre d\'affichage a été sauvegardé.', ['type' => 'order_success']);
            }
        }
        $this->jsonResponse(false, 'Erreur d\'ordre.');
    }
    /**
     * Affiche la liste des catégories dans une fenêtre modale allégée pour TinyMCE
     */
    public function tinymcePopup(): void
    {
        $db = new CategoryDb();

        $requestedLangId = (int)($_GET['lang_id'] ?? $this->defaultLang['id_lang']);
        $activeLangs = $db->fetchLanguages();
        $iso = $activeLangs[$requestedLangId] ?? 'fr';

        $rawCats = $db->getCategoriesForTinymce($requestedLangId);

        // 1. Indexation pour recréer l'arbre (Parent -> Enfants)
        $catsById = [];
        foreach ($rawCats as $c) {
            $catsById[$c['id_cat']] = $c;
            $catsById[$c['id_cat']]['children'] = [];
        }

        $tree = [];
        foreach ($catsById as $id => &$c) {
            if (!empty($c['id_parent']) && isset($catsById[$c['id_parent']])) {
                $catsById[$c['id_parent']]['children'][] = &$c;
            } else {
                $tree[] = &$c;
            }
        }

        // 2. Aplatissement de l'arbre avec calcul de la profondeur (Depth)
        $flatList = [];
        $urlTool = new \App\Component\Routing\UrlTool();

        $flatten = function($nodes, $depth = 0) use (&$flatten, &$flatList, $iso, $urlTool) {
            foreach ($nodes as $node) {
                $title = !empty($node['name_cat']) ? $node['name_cat'] : '⚠️ (Non traduit)';
                $slug = !empty($node['url_cat']) ? $node['url_cat'] : Url::clean($title);

                // Utilisation de votre UrlTool comme dans l'édition
                $publicUrl = $urlTool->buildUrl([
                    'iso'  => $iso,
                    'type' => 'category',
                    'id'   => $node['id_cat'],
                    'url'  => $slug
                ]);

                $flatList[] = [
                    'id'    => $node['id_cat'],
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
            'categoriesList' => $flatList,
            'iso_lang'       => strtoupper($iso),
            'hashtoken'      => $this->session->getToken()
        ]);

        $this->view->display('category/tinymce_popup.tpl');
    }
}