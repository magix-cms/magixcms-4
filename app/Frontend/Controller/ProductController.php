<?php

declare(strict_types=1);

namespace App\Frontend\Controller;

use App\Frontend\Db\ProductDb;
use App\Frontend\Model\ProductPresenter;
use Magepattern\Component\HTTP\Request;
use App\Frontend\Db\CompanyDb;

class ProductController extends BaseController
{
    public function run(): void
    {
        $id = Request::isGet('id') ? (int)$_GET['id'] : 0;
        $idLang = (int)($this->currentLang['id_lang'] ?? 1);
        $siteUrl = $this->view->getTemplateVars('site_url');

        $db = new ProductDb();
        $rawProduct = $db->getProductPage($id, $idLang);

        if (!$rawProduct) {
            $this->render404();
            return;
        }

        $companyDb = new CompanyDb();
        $companyInfo = $companyDb->getCompanyInfo();
        $skinFolder = $this->siteSettings['theme']['value'] ?? 'default';

        // 1. Formatage du produit
        $product = ProductPresenter::format($rawProduct, $this->currentLang, $siteUrl, $companyInfo, $skinFolder, $this->siteSettings);

        // 2. Galerie
        $product['gallery'] = [];
        $images = $db->getProductImages($id, $idLang);

        foreach ($images as $imgRow) {
            $tempRow = array_merge($rawProduct, $imgRow);
            $formattedTemp = ProductPresenter::format($tempRow, $this->currentLang, $siteUrl, $companyInfo, $skinFolder, $this->siteSettings);
            $formattedTemp['img']['is_default'] = (int)$imgRow['default_img'] === 1;
            $product['gallery'][] = $formattedTemp['img'];
        }

        // 🟢 GÉNÉRATION DU TABLEAU HREFLANG (Product)
        $allLangs = $this->view->getTemplateVars('langs');
        $hreflangUrls = [];
        $urlTool = new \App\Component\Routing\UrlTool();

        if ($allLangs && is_array($allLangs)) {
            foreach ($allLangs as $l) {
                $lId = (int)$l['id_lang'];
                $lIso = strtolower($l['iso_lang']);

                // Requête spécifique aux Produits
                $translatedProduct = $db->getProductPage($id, $lId);

                if ($translatedProduct && !empty($translatedProduct['url_product'])) {
                    // Attention au type pour ProductTool, ça peut être 'catalog' ou 'product' selon votre config
                    $hreflangUrls[$lId] = $urlTool->buildUrl([
                        'type' => 'product',
                        'id'   => $id,
                        'url'  => $translatedProduct['url_product'],
                        'iso'  => $lIso
                    ]);

                    if (isset($l['is_default']) && $l['is_default'] == 1) {
                        $this->view->assign('x_default_url', $hreflangUrls[$lId]);
                    }
                }
            }
        }

        $this->view->assign([
            'product'   => $product,
            'hreflang' => $hreflangUrls,
            'seo_title' => $product['seo_title'] ?? $product['name'],
            'seo_desc'  => $product['resume'] ?? ''
        ]);

        $this->view->display('catalog/product.tpl');
    }

    private function render404(): void
    {
        header("HTTP/1.0 404 Not Found");
        $tpl = 'errors/404.tpl';
        if ($this->view->templateExists($tpl)) $this->view->display($tpl);
        else die("Erreur 404 : Template manquant.");
    }
}