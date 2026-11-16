<?php

declare(strict_types=1);

namespace App\Frontend\Controller;

use App\Frontend\Db\PagesDb;
use App\Frontend\Db\CompanyDb;
use App\Frontend\Model\PagesPresenter;
use Magepattern\Component\HTTP\Request;
use App\Component\Routing\UrlTool;
use App\Frontend\Model\SeoHelper;

class PagesController extends BaseController
{
    public function run(): void
    {
        $id = Request::isGet('id') ? (int)$_GET['id'] : 0;

        // 1. SÉCURITÉ : Si aucun ID, c'est direct une 404 (et render404 fait un exit)
        if ($id === 0) {
            $this->render404();
        }

        // 2. DÉFINITION GLOBALE DES VARIABLES (PhpStorm est content)
        $idLang = (int)($this->currentLang['id_lang'] ?? 1);
        $isoLang = strtolower($this->currentLang['iso_lang'] ?? 'fr');
        $db = new PagesDb();

        $rawPage = $db->getPagesPage($id, $idLang);

        if (!$rawPage) {
            $this->render404();
        }

        // 3. VÉRIFICATION STRICTE DE L'URL ET REDIRECTION 301
        $urlTool = new UrlTool();

        $officialUrl = $urlTool->buildUrl([
            'type' => 'pages',
            'id'   => $id,
            'url'  => $rawPage['url_pages'],
            'iso'  => $isoLang
        ]);

        $currentUri = explode('?', $_SERVER['REQUEST_URI'])[0];
        $officialUri = parse_url($officialUrl, PHP_URL_PATH);

        if ($currentUri !== $officialUri) {
            header("HTTP/1.1 301 Moved Permanently");
            header("Location: " . $officialUrl);
            exit;
        }

        // 4. GESTION DU CACHE
        $cacheId = md5($_SERVER['REQUEST_URI']);

        if (!$this->view->isCached('pages/index.tpl', $cacheId)) {

            $siteUrl = rtrim((string)$this->view->getTemplateVars('site_url'), '/');

            // Sécurité si aucun ID (cas très rare sur PagesController)
            if (!$rawPage) {
                $this->render404();
                //return;
            }

            $companyDb = new CompanyDb();
            $companyInfo = $companyDb->getCompanyInfo() ?: [];

            $siteName = $companyInfo['name'] ?? 'MagixCMS';

            $seoTitle = !empty($rawPage['seo_title_pages'])
                ? $rawPage['seo_title_pages']
                : ($rawPage['name_pages'] ?? 'Page') . ' - ' . $siteName;

            $seoDesc = !empty($rawPage['seo_desc_pages'])
                ? $rawPage['seo_desc_pages']
                : ($rawPage['resume_pages'] ?? '');

            $skinFolder = $this->siteSettings['theme']['value'] ?? 'default';

            // Formatage
            $pages = PagesPresenter::format($rawPage, $this->currentLang, $siteUrl, $companyInfo, $skinFolder);

            // Galerie
            $pages['gallery'] = [];
            $images = $db->getPagesImages($id, $idLang);
            if ($images) {
                foreach ($images as $imgRow) {
                    $formattedImg = PagesPresenter::format(array_merge($rawPage, $imgRow), $this->currentLang, $siteUrl, $companyInfo, $skinFolder);
                    if (!empty($formattedImg['img'])) {
                        $pages['gallery'][] = $formattedImg['img'];
                    }
                }
            }

            // Sous-pages
            $pages['subdata'] = [];
            $pages['root'] = [];
            $rawChildren = $db->getPagesChildren($id, $idLang);

            if (!empty($rawChildren)) {
                foreach ($rawChildren as $childRow) {
                    $formattedChild = PagesPresenter::format($childRow, $this->currentLang, $siteUrl, $companyInfo, $skinFolder);

                    if ((int)$formattedChild['id_parent'] === $id) {
                        $pages['subdata'][] = $formattedChild;
                    } else {
                        $pages['root'][] = $formattedChild;
                    }
                }
            }

            $jsonLdList = SeoHelper::generateItemListJsonLd($pages['subdata']);

            // Génération du tableau Hreflang
            $allLangs = $this->view->getTemplateVars('langs');
            $hreflangUrls = [];
            $urlTool = new UrlTool(); // Instancié ici aussi pour la boucle

            if ($allLangs && is_array($allLangs)) {
                foreach ($allLangs as $l) {
                    $lId = (int)$l['id_lang'];
                    $lIso = strtolower($l['iso_lang']);

                    $translatedPage = $db->getPagesPage($id, $lId);

                    if ($translatedPage && !empty($translatedPage['url_pages'])) {
                        $hreflangUrls[$lId] = $urlTool->buildUrl([
                            'type' => 'pages',
                            'id'   => $id,
                            'url'  => $translatedPage['url_pages'],
                            'iso'  => $lIso
                        ]);

                        if (isset($l['is_default']) && $l['is_default'] == 1) {
                            $this->view->assign('x_default_url', $hreflangUrls[$lId]);
                        }
                    }
                }
            }

            $breadcrumbs_data = [];
            $currentParentId = (int)$rawPage['id_parent'];

            if ($currentParentId > 0) {
                $parentInfo = $db->getParentBreadcrumbInfo($currentParentId, $idLang);

                if ($parentInfo) {
                    $parentUrl = $urlTool->buildUrl([
                        'type' => 'pages',
                        'id'   => $parentInfo['id_pages'],
                        'url'  => $parentInfo['url_pages'],
                        'iso'  => $isoLang
                    ]);

                    $breadcrumbs_data[] = [
                        'label' => $parentInfo['name_pages'],
                        'url'   => $parentUrl
                    ];
                }
            }

            // Pour s'assurer que la vraie URL est poussée dans le layout pour le link rel="canonical"
            $this->view->assign('canonical_url', $officialUrl);

            // Assignation Smarty
            $this->view->assign([
                'pages'              => $pages,
                'parent_breadcrumbs' => $breadcrumbs_data,
                'json_ld'            => $jsonLdList,
                'seo_title'          => $seoTitle,
                'seo_desc'           => $seoDesc,
                'hreflang'           => $hreflangUrls
            ]);
        }

        // 3. Affichage de la vue
        $this->view->display('pages/index.tpl', $cacheId);
    }
}