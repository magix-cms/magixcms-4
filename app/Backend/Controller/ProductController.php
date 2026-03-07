<?php

declare(strict_types=1);

namespace App\Backend\Controller;

use App\Backend\Db\ProductDb;
use App\Backend\Db\CategoryDb; // Nécessaire pour récupérer l'arborescence et l'URL parente
use App\Component\Db\ConfigDb;
use App\Component\Routing\UrlTool;
use App\Component\File\UploadTool;
use App\Component\File\ImageTool;
use Magepattern\Component\HTTP\Request;
use Magepattern\Component\Tool\FormTool;
use Magepattern\Component\HTTP\Url;
use Magepattern\Component\Tool\StringTool;
use Magepattern\Component\File\FileTool;

class ProductController extends BaseController
{
    public function run(): void
    {
        $action = $_GET['action'] ?? null;
        if ($action && $action !== 'run' && method_exists($this, $action)) {
            $this->$action();
            return;
        }

        $this->index();
    }

    private function index(): void
    {
        $idLangue = (int)$this->defaultLang['id_lang'];
        $db = new ProductDb();

        // Ajout de date_register pour coller au design category
        $targetColumns = ['id_product', 'reference_p', 'name_p', 'default_category_name', 'price_p', 'published_p', 'date_register'];

        $rawScheme = array_merge(
            $db->getTableScheme('mc_catalog_product'),
            $db->getTableScheme('mc_catalog_product_content')
        );
        $rawScheme[] = ['column' => 'default_category_name', 'type' => 'varchar(255)'];

        $associations = [
            'id_product'            => ['title' => 'id', 'type' => 'text', 'class' => 'text-center text-muted small px-2'],
            'reference_p'           => ['title' => 'Réf.', 'type' => 'text', 'class' => 'text-muted small text-nowrap'],
            'name_p'                => ['title' => 'Nom du produit', 'type' => 'text', 'class' => 'w-50 fw-bold'],
            'default_category_name' => ['title' => 'Catégorie', 'type' => 'text', 'class' => 'text-muted small text-nowrap'],
            'price_p'               => ['title' => 'Prix', 'type' => 'text', 'class' => 'text-end fw-medium'],
            'published_p'           => ['title' => 'Statut', 'type' => 'bin', 'class' => 'text-center px-3', 'enum' => 'status_'],
            'date_register'         => ['title' => 'Date', 'type' => 'date', 'class' => 'text-center text-nowrap text-muted small']
        ];

        $this->getScheme($rawScheme, $targetColumns, $associations);

        $page   = Request::isGet('page') ? (int)$_GET['page'] : 1;
        $limit  = Request::isGet('offset') ? (int)$_GET['offset'] : 25;
        $search = $_GET['search'] ?? [];

        $result = $db->fetchAllProducts($page, $limit, $search, $idLangue);

        $meta = [];
        $urlTool = new \App\Component\Routing\UrlTool();

        if ($result !== false) {
            foreach ($result['data'] as &$row) {
                if (!empty($row['url_p']) && !empty($row['default_category_id'])) {
                    $row['public_url'] = $urlTool->buildUrl([
                        'iso'          => $this->defaultLang['iso'] ?? 'fr',
                        'type'         => 'product',
                        'id'           => $row['id_product'],
                        'url'          => $row['url_p'],
                        'id_category'  => $row['default_category_id'],
                        'url_category' => $row['default_category_url'] ?? ''
                    ]);
                } else {
                    $row['public_url'] = '';
                }
            }
            unset($row);

            $this->getItems('products', $result['data'], true, $result['meta']);
            $meta = $result['meta'];
        }

        $token = $this->session->getToken();

        $this->view->assign([
            'idcolumn'   => 'id_product',
            'hashtoken'  => $token,
            'url_token'  => urlencode($token),
            'get_search' => $search,
            'sortable'   => false,
            'checkbox'   => true,
            'edit'       => true,
            'dlt'        => true,
            'meta'       => $meta
        ]);

        $this->view->display('product/index.tpl');
    }

    public function add(): void
    {
        if (Request::isMethod('POST')) {
            $this->processAdd();
            return;
        }

        $catDb = new CategoryDb();
        $idLangDefault = (int)$this->defaultLang['id_lang'];

        $flatCategories = $catDb->fetchAllCategoriesForSelect($idLangDefault);
        $categoryTree = $flatCategories ? $this->buildCategoryTree($flatCategories) : [];

        $this->view->assign([
            'category_tree' => $categoryTree,
            'hashtoken'       => $this->session->getToken()
        ]);

        $this->view->display('product/add.tpl');
    }

    public function edit(): void
    {
        $id = (int)($_REQUEST['edit'] ?? $_POST['id_product'] ?? 0);
        $db = new ProductDb();

        if (Request::isMethod('POST')) {
            $this->processEdit($db, $id);
            return;
        }

        $product = $db->fetchProductById($id);
        if (!$product) {
            header('Location: index.php?controller=Product');
            exit;
        }

        $catDb = new CategoryDb();
        $idLangDefault = (int)$this->defaultLang['id_lang'];
        $activeLangs = $db->fetchLanguages();

        $urlTool = new UrlTool();
        $defaultCategoryData = $product['default_category_id'] > 0 ? $catDb->fetchCategoryById($product['default_category_id']) : null;

        // Calcul des URLs publiques
        foreach ($activeLangs as $langId => $iso) {
            $slug = $product['content'][$langId]['url_p'] ?? '';
            $urlParent = $defaultCategoryData ? ($defaultCategoryData['content'][$langId]['url_cat'] ?? '') : '';

            $product['content'][$langId]['public_url'] = $urlTool->buildUrl([
                'iso'          => $iso,
                'type'         => 'product',
                'id'           => $id,
                'url'          => $slug,
                'id_category'  => $product['default_category_id'],
                'url_category' => $urlParent
            ]);
        }

        $flatCategories = $catDb->fetchAllCategoriesForSelect($idLangDefault);
        $categoryTree = $flatCategories ? $this->buildCategoryTree($flatCategories) : [];

        $this->view->assign([
            'product'         => $product,
            'category_tree' => $categoryTree,
            'langs'           => $activeLangs,
            'hashtoken'       => $this->session->getToken()
        ]);

        $this->view->display('product/edit.tpl');
    }

    private function processAdd(): void
    {
        $token = $_POST['hashtoken'] ?? '';
        if (!$this->session->validateToken($token)) {
            $this->jsonResponse(false, 'Session expirée.');
        }

        $db = new ProductDb();

        // Formatage de la structure
        $structureData = [
            'price_p'        => (float)str_replace(',', '.', $_POST['price_p'] ?? '0'),
            'price_promo_p'  => (float)str_replace(',', '.', $_POST['price_promo_p'] ?? '0'),
            'reference_p'    => FormTool::simpleClean($_POST['reference_p'] ?? ''),
            'ean_p'          => FormTool::simpleClean($_POST['ean_p'] ?? ''),
            'width_p'        => (float)str_replace(',', '.', $_POST['width_p'] ?? '0'),
            'height_p'       => (float)str_replace(',', '.', $_POST['height_p'] ?? '0'),
            'depth_p'        => (float)str_replace(',', '.', $_POST['depth_p'] ?? '0'),
            'weight_p'       => (float)str_replace(',', '.', $_POST['weight_p'] ?? '0'),
            'availability_p' => FormTool::simpleClean($_POST['availability_p'] ?? 'InStock')
        ];

        $newId = $db->insertProductStructure($structureData);

        if ($newId) {
            // Gestion des catégories
            $categories = isset($_POST['categories']) && is_array($_POST['categories']) ? array_map('intval', $_POST['categories']) : [];
            $defaultCat = (int)($_POST['default_category'] ?? 0);

            // --- CORRECTION CRITIQUE ICI ---
            // On force le defaultCat côté Contrôleur AVANT de générer les traductions et l'URL
            if (!in_array($defaultCat, $categories) && count($categories) > 0) {
                $defaultCat = $categories[0];
            }

            $db->saveProductCategories($newId, $categories, $defaultCat);

            // Gestion des traductions (qui utilisera maintenant le bon $defaultCat)
            $this->saveTranslations($db, $newId, $defaultCat);

            $this->jsonResponse(true, 'Produit créé avec succès.', ['type' => 'add', 'id' => $newId]);
        } else {
            $this->jsonResponse(false, 'Erreur lors de la création du produit.');
        }
    }

    private function processEdit(ProductDb $db, int $id): void
    {
        $token = $_POST['hashtoken'] ?? '';
        if (!$this->session->validateToken($token)) {
            $this->jsonResponse(false, 'Session expirée.');
        }

        $structureData = [
            'price_p'        => (float)str_replace(',', '.', $_POST['price_p'] ?? '0'),
            'price_promo_p'  => (float)str_replace(',', '.', $_POST['price_promo_p'] ?? '0'),
            'reference_p'    => FormTool::simpleClean($_POST['reference_p'] ?? ''),
            'ean_p'          => FormTool::simpleClean($_POST['ean_p'] ?? ''),
            'width_p'        => (float)str_replace(',', '.', $_POST['width_p'] ?? '0'),
            'height_p'       => (float)str_replace(',', '.', $_POST['height_p'] ?? '0'),
            'depth_p'        => (float)str_replace(',', '.', $_POST['depth_p'] ?? '0'),
            'weight_p'       => (float)str_replace(',', '.', $_POST['weight_p'] ?? '0'),
            'availability_p' => FormTool::simpleClean($_POST['availability_p'] ?? 'InStock')
        ];

        if ($db->updateProductStructure($id, $structureData)) {

            $categories = isset($_POST['categories']) && is_array($_POST['categories']) ? array_map('intval', $_POST['categories']) : [];
            $defaultCat = (int)($_POST['default_category'] ?? 0);

            // --- CORRECTION CRITIQUE ICI ---
            if (!in_array($defaultCat, $categories) && count($categories) > 0) {
                $defaultCat = $categories[0];
            }

            $db->saveProductCategories($id, $categories, $defaultCat);

            $publicUrls = $this->saveTranslations($db, $id, $defaultCat);

            $this->jsonResponse(true, 'Produit mis à jour.', [
                'type'        => 'update',
                'public_urls' => $publicUrls
            ]);
        } else {
            $this->jsonResponse(false, 'Erreur lors de la mise à jour.');
        }
    }

    private function saveTranslations(ProductDb $db, int $idProduct, int $defaultCategoryId): array
    {
        $urlTool = new UrlTool();
        $catDb = new CategoryDb();
        $langs = $this->view->getTemplateVars('langs') ?? $db->fetchLanguages();

        $publicUrls = [];
        $defaultCategoryData = $defaultCategoryId > 0 ? $catDb->fetchCategoryById($defaultCategoryId) : null;

        foreach ($langs as $idLang => $iso) {
            $nameProduct = $_POST['name_p'][$idLang] ?? '';

            if (!empty($nameProduct)) {
                $urlProduct = FormTool::simpleClean($_POST['url_p'][$idLang] ?? '');
                if (empty($urlProduct)) {
                    $urlProduct = class_exists(Url::class) ? Url::clean($nameProduct) : StringTool::strtolower(str_replace(' ', '-', $nameProduct));
                }

                $contentData = [
                    'name_p'       => FormTool::simpleClean($nameProduct),
                    'longname_p'   => FormTool::simpleClean($_POST['longname_p'][$idLang] ?? ''),
                    'url_p'        => $urlProduct,
                    'resume_p'     => $_POST['resume_p'][$idLang] ?? '',
                    'content_p'    => $_POST['content_p'][$idLang] ?? '',
                    'seo_title_p'  => FormTool::simpleClean($_POST['seo_title_p'][$idLang] ?? ''),
                    'seo_desc_p'   => FormTool::simpleClean($_POST['seo_desc_p'][$idLang] ?? ''),
                    'link_label_p' => FormTool::simpleClean($_POST['link_label_p'][$idLang] ?? ''),
                    'link_title_p' => FormTool::simpleClean($_POST['link_title_p'][$idLang] ?? ''),
                    'published_p'  => isset($_POST['published_p'][$idLang]) ? 1 : 0
                ];

                $db->saveProductContent($idProduct, $idLang, $contentData);

                $urlParent = $defaultCategoryData ? ($defaultCategoryData['content'][$idLang]['url_cat'] ?? '') : '';

                $publicUrls[$idLang] = $urlTool->buildUrl([
                    'iso'          => $iso,
                    'type'         => 'product',
                    'id'           => $idProduct,
                    'url'          => $urlProduct,
                    'id_category'  => $defaultCategoryId,
                    'url_category' => $urlParent
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
        $idProduct = (int)($_POST['id'] ?? 0);

        if ($idProduct <= 0 || empty($_FILES['img_multiple']['name'][0])) {
            $this->jsonResponse(false, 'Aucun fichier reçu.');
        }

        $db = new ProductDb();
        $prodData = $db->fetchProductById($idProduct);
        $idLangue = (int)$this->defaultLang['id_lang'];
        $slug = !empty($prodData['content'][$idLangue]['url_p']) ? $prodData['content'][$idLangue]['url_p'] : 'product';

        $uploadTool = new UploadTool();
        $lastImageId = $db->getLastImageId($idProduct);

        $results = $uploadTool->multipleImageUpload(
            'product', 'product', 'upload/product', [(string)$idProduct],
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
                if ($db->insertImage($idProduct, $res['file'])) {
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

        $idProduct = (int)($_POST['id_product'] ?? 0);

        if (!empty($ids)) {
            $db = new ProductDb();
            $configDb = new ConfigDb();
            $urlTool = new UrlTool();

            $configs = $configDb->fetchImageSizes('product', 'product');
            $uploadDir = $urlTool->dirUpload('upload/product/' . $idProduct, true);

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

    public function processOrderImages(): void
    {
        $imageIds = $_POST['image'] ?? [];

        if (!empty($imageIds) && is_array($imageIds)) {
            $db = new ProductDb();
            if ($db->reorderImages($imageIds)) {
                $this->jsonResponse(true, 'L\'ordre des images a été sauvegardé.', ['type' => 'order_success']);
            }
        }
        $this->jsonResponse(false, 'Erreur d\'ordre.');
    }

    public function processSetDefaultImage(): void
    {
        $idProduct = (int)($_POST['edit'] ?? 0);
        $idImg = (int)($_POST['id_img'] ?? 0);

        if ($idProduct > 0 && $idImg > 0) {
            $db = new ProductDb();
            if ($db->setDefaultImage($idProduct, $idImg)) {
                $this->jsonResponse(true, 'Image par défaut mise à jour.', ['type' => 'update']);
            }
        }
        $this->jsonResponse(false, 'Erreur image par défaut.');
    }

    public function getImages(): void
    {
        if (ob_get_length()) ob_clean();
        $id = (int)($_GET['edit'] ?? $_GET['id_product'] ?? 0);

        $db = new ProductDb();
        $images = $db->fetchImagesByProduct($id);

        $imageTool = new ImageTool();
        $formatted = $imageTool->setModuleImages('product', 'product', $images, $id);

        $this->view->assign([
            'images'    => $formatted,
            'item_id'   => $id,
            'current_c' => 'Product'
        ]);

        $html = $this->view->fetch('components/gallery.tpl');
        $this->jsonResponse(true, 'OK', ['result' => $html]);
    }

    public function getImgMeta(): void
    {
        if (ob_get_length()) ob_clean();
        $idImg = (int)($_GET['id_img'] ?? 0);

        $db = new ProductDb();
        $langs = $db->fetchLanguages();
        $meta = $db->fetchImageMeta($idImg);
        $currentController = ucfirst($_GET['controller'] ?? 'Product');

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
        $db = new ProductDb();
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
     * Construit un arbre hiérarchique à partir d'une liste plate de catégories.
     */
    private function buildCategoryTree(array $flatCategories): array
    {
        $tree = ['root' => []];
        $indexed = [];

        // 1. Indexation par ID et initialisation du conteneur d'enfants
        foreach ($flatCategories as $cat) {
            $cat['subdata'] = [];
            $indexed[$cat['id_cat']] = $cat;
        }

        // 2. Création de la hiérarchie via les références
        foreach ($indexed as $id => &$cat) {
            $parentId = (int)($cat['parent_cat'] ?? 0);

            // Si c'est un parent de 1er niveau ou si le parent n'est pas dans le dataset, on l'attache à la racine
            if ($parentId === 0 || !isset($indexed[$parentId])) {
                $tree['root'][] = &$cat;
            } else {
                // Sinon, on l'attache dans les "subdata" de son parent direct
                $indexed[$parentId]['subdata'][] = &$cat;
            }
        }
        unset($cat); // Toujours détruire la référence après la boucle

        return $tree['root'];
    }
}