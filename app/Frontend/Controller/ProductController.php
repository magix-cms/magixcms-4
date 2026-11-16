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

        if ($id === 0) {
            $this->render404();
        }

        $idLang = (int)($this->currentLang['id_lang'] ?? 1);
        $isoLang = strtolower($this->currentLang['iso_lang'] ?? 'fr');
        $db = new ProductDb();

        // 1. VÉRIFICATION STRICTE DE L'URL ET REDIRECTION 301
        $rawProduct = $db->getProductPage($id, $idLang);

        if (!$rawProduct) {
            $this->render404();
        }

        // CORRECTION ICI : On récupère l'ID et l'URL de la catégorie parente (soit de la BDD, soit du routeur)
        $idCategory = $rawProduct['id_cat'] ?? $rawProduct['id_parent'] ?? ($_GET['id_parent'] ?? '');
        $urlCategory = $rawProduct['url_cat'] ?? $rawProduct['url_parent'] ?? ($_GET['url_parent'] ?? '');

        $urlTool = new \App\Component\Routing\UrlTool();
        $officialUrl = $urlTool->buildUrl([
            'type'         => 'product',
            'id'           => $id,
            'url'          => $rawProduct['url_p'],
            'iso'          => $isoLang,
            'id_category'  => $idCategory,
            'url_category' => $urlCategory
        ]);

        $currentUri = explode('?', $_SERVER['REQUEST_URI'])[0];
        $officialUri = parse_url($officialUrl, PHP_URL_PATH);

        if ($currentUri !== $officialUri) {
            header("HTTP/1.1 301 Moved Permanently");
            header("Location: " . $officialUrl);
            exit;
        }

        // 2. GESTION DU CACHE
        $cacheId = md5($_SERVER['REQUEST_URI']);

        if (!$this->view->isCached('catalog/product.tpl', $cacheId)) {

            $siteUrl = rtrim((string)$this->view->getTemplateVars('site_url'), '/');

            $companyDb = new CompanyDb();
            $companyInfo = $companyDb->getCompanyInfo() ?: [];

            $siteName = $companyInfo['name'] ?? 'MagixCMS';

            $seoTitle = !empty($rawProduct['seo_title_p'])
                ? $rawProduct['seo_title_p']
                : ($rawProduct['name_p'] ?? 'Produit') . ' - ' . $siteName;

            $seoDesc = !empty($rawProduct['seo_desc_p'])
                ? $rawProduct['seo_desc_p']
                : ($rawProduct['resume_p'] ?? '');

            $skinFolder = $this->siteSettings['theme']['value'] ?? 'default';

            // Formatage du produit
            $product = ProductPresenter::format($rawProduct, $this->currentLang, $siteUrl, $companyInfo, $skinFolder, $this->siteSettings);

            // Galerie
            $product['gallery'] = [];
            $images = $db->getProductImages($id, $idLang);

            foreach ($images as $imgRow) {
                $tempRow = array_merge($rawProduct, $imgRow);
                $formattedTemp = ProductPresenter::format($tempRow, $this->currentLang, $siteUrl, $companyInfo, $skinFolder, $this->siteSettings);
                $formattedTemp['img']['is_default'] = (int)$imgRow['default_img'] === 1;
                $product['gallery'][] = $formattedTemp['img'];
            }

            // GÉNÉRATION DU TABLEAU HREFLANG
            $allLangs = $this->view->getTemplateVars('langs');
            $hreflangUrls = [];

            if ($allLangs && is_array($allLangs)) {
                foreach ($allLangs as $l) {
                    $lId = (int)$l['id_lang'];
                    $lIso = strtolower($l['iso_lang']);

                    $translatedProduct = $db->getProductPage($id, $lId);

                    if ($translatedProduct && !empty($translatedProduct['url_p'])) {

                        $tIdCategory = $translatedProduct['id_cat'] ?? $translatedProduct['id_parent'] ?? $idCategory;
                        $tUrlCategory = $translatedProduct['url_cat'] ?? $translatedProduct['url_parent'] ?? $urlCategory;

                        $hreflangUrls[$lId] = $urlTool->buildUrl([
                            'type'         => 'product',
                            'id'           => $id,
                            'url'          => $translatedProduct['url_p'],
                            'iso'          => $lIso,
                            'id_category'  => $tIdCategory,
                            'url_category' => $tUrlCategory
                        ]);

                        if (isset($l['is_default']) && $l['is_default'] == 1) {
                            $this->view->assign('x_default_url', $hreflangUrls[$lId]);
                        }
                    }
                }
            }

            $this->view->assign('canonical_url', $officialUrl);

            $this->view->assign([
                'product'   => $product,
                'hreflang'  => $hreflangUrls,
                'seo_title' => $seoTitle,
                'seo_desc'  => $seoDesc
            ]);
        }

        $this->view->display('catalog/product.tpl', $cacheId);
    }
}