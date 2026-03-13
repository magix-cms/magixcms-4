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

        // 1. Formatage des données principales du produit
        $companyDb = new CompanyDb();
        $companyInfo = $companyDb->getCompanyInfo();

        $product = ProductPresenter::format($rawProduct, $this->currentLang, $siteUrl, $companyInfo);

        // 2. Gestion de la galerie d'images
        $product['gallery'] = [];
        $images = $db->getProductImages($id, $idLang);

        foreach ($images as $imgRow) {
            $tempRow = array_merge($rawProduct, $imgRow);
            // 🟢 On passe aussi $companyInfo ici pour éviter des warnings si la méthode format l'attend
            $formattedTemp = ProductPresenter::format($tempRow, $this->currentLang, $siteUrl, $companyInfo);

            $formattedTemp['img']['is_default'] = (int)$imgRow['default_img'] === 1;
            $product['gallery'][] = $formattedTemp['img'];
        }

        // Assignation à Smarty
        $this->view->assign([
            'product'   => $product,
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