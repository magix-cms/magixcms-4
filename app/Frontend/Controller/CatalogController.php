<?php

declare(strict_types=1);

namespace App\Frontend\Controller;

use App\Frontend\Db\CatalogDb;
use App\Frontend\Db\ProductDb;
use App\Frontend\Db\CompanyDb;
use App\Frontend\Model\CatalogHomePresenter;
use App\Frontend\Model\CategoryPresenter;
use App\Frontend\Model\ProductPresenter;
use Magepattern\Component\HTTP\Request;
use App\Component\Routing\UrlTool;

class CatalogController extends BaseController
{
    public function run(): void
    {
        // 1. L'AIGUILLEUR (DISPATCHER)
        $id = Request::isGet('id') ? (int)$_GET['id'] : 0;
        $idParent = Request::isGet('id_parent') ? (int)$_GET['id_parent'] : 0;

        if ($idParent > 0 && $id > 0) {
            $productController = new ProductController();
            $productController->run();
            return;
        }

        if ($id > 0) {
            $categoryController = new CategoryController();
            $categoryController->run();
            return;
        }

        // --- 2. CODE NORMAL DE LA PAGE D'ACCUEIL DU CATALOGUE ---
        // 🟢 ID de Cache Smarty
        $cacheId = md5($_SERVER['REQUEST_URI']);

        if (!$this->view->isCached('catalog/index.tpl', $cacheId)) {

            $idLang = (int)($this->currentLang['id_lang'] ?? 1);
            $siteUrl = $this->view->getTemplateVars('site_url');

            $db = new CatalogDb();
            $rawHome = $db->getHomePage($idLang);

            // 🟢 Sécurité SEO "Anti-vide"
            $siteName = $this->siteSettings['site_name']['value'] ?? 'Magix CMS';

            if (!$rawHome) {
                $catalogHome = [
                    'title'   => 'Notre Catalogue',
                    'content' => '',
                    'seo'     => ['title' => 'Catalogue - ' . $siteName, 'description' => 'Découvrez tous nos produits.']
                ];
                $seoTitle = $catalogHome['seo']['title'];
                $seoDesc = $catalogHome['seo']['description'];
            } else {
                $catalogHome = CatalogHomePresenter::format($rawHome);
                $seoTitle = !empty($rawHome['seo_title_page']) ? $rawHome['seo_title_page'] : ($rawHome['title_page'] ?? 'Catalogue') . ' - ' . $siteName;
                $seoDesc = !empty($rawHome['seo_desc_page']) ? $rawHome['seo_desc_page'] : '';
            }

            // 🟢 RÉCUPÉRATION DES INFOS GLOBALES
            $companyDb = new CompanyDb();
            $companyInfo = $companyDb->getCompanyInfo();
            $skinFolder = $this->siteSettings['theme']['value'] ?? 'default';

            // 🟢 LECTURE DU PARAMÈTRE D'AFFICHAGE (0 = Catégories seules, 1 = Produits inclus)
            $showAllProducts = ($this->siteSettings['product_catalog']['value'] ?? '0') === '1';

            $catalogHome['subdata'] = []; // Pour les catégories
            $catalogHome['products'] = []; // Pour les produits
            $paginationData = [];
            $pageUrlBase = '';

            // ==========================================================
            // 🟢 ACTION 1 : TOUJOURS CHARGER LES CATÉGORIES MÈRES
            // ==========================================================
            $rawCategories = $db->getRootCategories($idLang);

            if (!empty($rawCategories)) {
                foreach ($rawCategories as $catRow) {
                    $catalogHome['subdata'][] = CategoryPresenter::format($catRow, $this->currentLang, $siteUrl, $companyInfo, $skinFolder);
                }
            }

            // ==========================================================
            // 🟢 ACTION 2 : CHARGER LES PRODUITS UNIQUEMENT SI ACTIVÉ
            // ==========================================================
            $page = Request::isGet('p') ? (int)$_GET['p'] : 1;

            if ($showAllProducts) {
                $productDb = new ProductDb();

                // Paramètres de pagination
                $limit = (int)($this->siteSettings['product_per_page']['value'] ?? 20);

                $filters = [
                    'page'  => $page,
                    'limit' => $limit > 0 ? $limit : 20
                ];

                // Appel de votre nouvelle méthode propulsée par PaginationTool
                $dbResult = $productDb->getPaginatedProductList($idLang, $filters);
                $rawProducts = $dbResult['items'] ?? [];
                $paginationData = $dbResult['pagination'] ?? [];

                if (!empty($rawProducts)) {
                    foreach ($rawProducts as $productRow) {
                        $formattedProduct = ProductPresenter::format($productRow, $this->currentLang, $siteUrl, $companyInfo, $skinFolder, $this->siteSettings);
                        if ($formattedProduct) {
                            $catalogHome['products'][] = $formattedProduct;
                        }
                    }
                }

                // Génération de l'URL de base pour la pagination Smarty
                $currentUrl = $_SERVER['REQUEST_URI'] ?? '/';
                $currentUrl = preg_replace('/([?&])p=[0-9]+&?/', '$1', $currentUrl);
                $currentUrl = rtrim($currentUrl, '?&');
                $sep = str_contains($currentUrl, '?') ? '&' : '?';
                $pageUrlBase = $currentUrl . $sep . 'p=';
            }

            if ($page > 1) {
                $seoTitle .= ' - Page ' . $page;
            }

            // 🟢 NOUVEAU : GÉNÉRATION DU TABLEAU HREFLANG (Racine Catalogue)
            $allLangs = $this->view->getTemplateVars('langs');
            $hreflangUrls = [];
            $urlTool = new UrlTool();

            if ($allLangs && is_array($allLangs)) {
                foreach ($allLangs as $l) {
                    $lId = (int)$l['id_lang'];
                    $lIso = strtolower($l['iso_lang']);

                    // Pas besoin d'ID ni d'URL spécifique pour la racine du module
                    $hreflangUrls[$lId] = $urlTool->buildUrl([
                        'type' => 'catalog',
                        'iso'  => $lIso
                    ]);

                    if (isset($l['is_default']) && $l['is_default'] == 1) {
                        $this->view->assign('x_default_url', $hreflangUrls[$lId]);
                    }
                }
            }

            // --- ASSIGNATION SMARTY ---
            $this->view->assign([
                'catalog_home'  => $catalogHome,
                'show_products' => $showAllProducts,
                'pagination'    => $paginationData,
                'page_url_base' => $pageUrlBase,
                'seo_title'     => $seoTitle,
                'seo_desc'      => $seoDesc,
                'hreflang'      => $hreflangUrls
            ]);
        }

        $this->view->display('catalog/index.tpl', $cacheId);
    }
}