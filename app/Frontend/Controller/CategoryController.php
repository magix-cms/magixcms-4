<?php

declare(strict_types=1);

namespace App\Frontend\Controller;

use App\Frontend\Db\CategoryDb;
use App\Frontend\Db\ProductDb;
use App\Frontend\Db\CompanyDb; // 🟢 Ajout pour le JSON-LD
use App\Frontend\Model\CategoryPresenter;
use App\Frontend\Model\ProductPresenter;
use Magepattern\Component\HTTP\Request;
use App\Frontend\Model\SeoHelper;

class CategoryController extends BaseController
{
    public function run(): void
    {
        $id = Request::isGet('id') ? (int)$_GET['id'] : 0;
        $idLang = (int)($this->currentLang['id_lang'] ?? 1);
        $siteUrl = $this->view->getTemplateVars('site_url');

        $db = new CategoryDb();
        $rawCategory = $db->getCategoryPage($id, $idLang);

        if (!$rawCategory) {
            $this->render404();
            return;
        }

        // 🟢 Récupération des données globales nécessaires
        $companyDb = new CompanyDb();
        $companyInfo = $companyDb->getCompanyInfo();
        $skinFolder = $this->siteSettings['theme']['value'] ?? 'default';

        // 1. Formatage de la catégorie courante
        $category = CategoryPresenter::format($rawCategory, $this->currentLang, $siteUrl, $companyInfo, $skinFolder);

        // 2. Galerie d'images
        $category['gallery'] = [];
        $images = $db->getCategoryImages($id, $idLang);
        foreach ($images as $imgRow) {
            $category['gallery'][] = CategoryPresenter::format(array_merge($rawCategory, $imgRow), $this->currentLang, $siteUrl, $companyInfo, $skinFolder)['img'];
        }

        // 3. Sous-catégories
        $category['subdata'] = [];
        $rawChildren = $db->getCategoryChildren($id, $idLang);
        if (!empty($rawChildren)) {
            foreach ($rawChildren as $childRow) {
                $category['subdata'][] = CategoryPresenter::format($childRow, $this->currentLang, $siteUrl, $companyInfo, $skinFolder);
            }
        }

        // 4. Liste des produits
        $category['products'] = [];
        $productDb = new ProductDb();
        $rawProducts = $productDb->getProductList($idLang, ['id_cat' => $id]);

        if (!empty($rawProducts)) {
            foreach ($rawProducts as $productRow) {
                $formattedProduct = ProductPresenter::format($productRow, $this->currentLang, $siteUrl, $companyInfo, $skinFolder, $this->siteSettings);
                if ($formattedProduct) {
                    $category['products'][] = $formattedProduct;
                }
            }
        }

        $jsonLdList = SeoHelper::generateItemListJsonLd($category['products']);

        // 🟢 GÉNÉRATION DU TABLEAU HREFLANG (Category)
        $allLangs = $this->view->getTemplateVars('langs');
        $hreflangUrls = [];
        $urlTool = new \App\Component\Routing\UrlTool();

        if ($allLangs && is_array($allLangs)) {
            foreach ($allLangs as $l) {
                $lId = (int)$l['id_lang'];
                $lIso = strtolower($l['iso_lang']);

                // Requête spécifique aux Catégories
                $translatedCat = $db->getCategoryPage($id, $lId);

                if ($translatedCat && !empty($translatedCat['url_cat'])) {
                    $hreflangUrls[$lId] = $urlTool->buildUrl([
                        'type' => 'category',
                        'id'   => $id,
                        'url'  => $translatedCat['url_cat'],
                        'iso'  => $lIso
                    ]);

                    if (isset($l['is_default']) && $l['is_default'] == 1) {
                        $this->view->assign('x_default_url', $hreflangUrls[$lId]);
                    }
                }
            }
        }

        $this->view->assign([
            'category'  => $category,
            'json_ld'   => $jsonLdList,
            'hreflang'  => $hreflangUrls,
            'seo_title' => $category['seo']['title'],
            'seo_desc'  => $category['seo']['description']
        ]);

        $this->view->display('catalog/category.tpl');
    }

    private function render404(): void
    {
        header("HTTP/1.0 404 Not Found");
        $tpl = 'errors/404.tpl';
        if ($this->view->templateExists($tpl)) $this->view->display($tpl);
        else die("Erreur 404 : Template manquant.");
    }
}