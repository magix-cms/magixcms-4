<?php

declare(strict_types=1);

namespace App\Frontend\Controller;

use App\Frontend\Db\AboutDb;
use App\Frontend\Db\CompanyDb;
use App\Frontend\Model\AboutPresenter;
use App\Component\Routing\UrlTool;
use Magepattern\Component\HTTP\Request;
use App\Frontend\Model\SeoHelper;

class AboutController extends BaseController
{
    public function run(): void
    {
        $id = Request::isGet('id') ? (int)$_GET['id'] : 0;

        if ($id === 0) {
            $this->render404();
        }

        $idLang = (int)($this->currentLang['id_lang'] ?? 1);
        $isoLang = strtolower($this->currentLang['iso_lang'] ?? 'fr');
        $db = new AboutDb();

        // 1. VÉRIFICATION STRICTE DE L'URL ET REDIRECTION 301
        $rawPage = $db->getAboutPage($id, $idLang);

        if (!$rawPage) {
            $this->render404();
        }

        $urlTool = new UrlTool();
        $officialUrl = $urlTool->buildUrl([
            'type' => 'about',
            'id'   => $id,
            'url'  => $rawPage['url_about'],
            'iso'  => $isoLang
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

        if (!$this->view->isCached('about/index.tpl', $cacheId)) {

            $siteUrl = rtrim((string)$this->view->getTemplateVars('site_url'), '/');

            $companyDb = new CompanyDb();
            $companyInfo = $companyDb->getCompanyInfo() ?: [];

            $siteName = $companyInfo['name'] ?? 'MagixCMS';

            $seoTitle = !empty($rawPage['seo_title_about'])
                ? $rawPage['seo_title_about']
                : ($rawPage['name_about'] ?? 'À propos') . ' - ' . $siteName;

            $seoDesc = !empty($rawPage['seo_desc_about'])
                ? $rawPage['seo_desc_about']
                : ($rawPage['resume_about'] ?? '');

            $skinFolder = $this->siteSettings['theme']['value'] ?? 'default';

            // Formatage de la page principale
            $about = AboutPresenter::format($rawPage, $this->currentLang, $siteUrl, $companyInfo, $skinFolder);

            // Galerie d'images
            $about['gallery'] = [];
            $images = $db->getAboutImages($id, $idLang);
            if ($images) {
                foreach ($images as $imgRow) {
                    $formattedImg = AboutPresenter::format(array_merge($rawPage, $imgRow), $this->currentLang, $siteUrl, $companyInfo, $skinFolder);
                    if (!empty($formattedImg['img'])) {
                        $about['gallery'][] = $formattedImg['img'];
                    }
                }
            }

            // Gestion de l'arborescence (Subdata & Root)
            $about['subdata'] = [];
            $about['root'] = [];

            $rawChildren = $db->getAboutChildren($id, $idLang);

            if (!empty($rawChildren)) {
                foreach ($rawChildren as $childRow) {
                    $formattedChild = AboutPresenter::format($childRow, $this->currentLang, $siteUrl, $companyInfo, $skinFolder);

                    if ((int)$formattedChild['id_parent'] === $id) {
                        $about['subdata'][] = $formattedChild;
                    } else {
                        $about['root'][] = $formattedChild;
                    }
                }
            }

            $jsonLdList = SeoHelper::generateItemListJsonLd($about['subdata']);

            // Génération du tableau Hreflang
            $allLangs = $this->view->getTemplateVars('langs');
            $hreflangUrls = [];

            if ($allLangs && is_array($allLangs)) {
                foreach ($allLangs as $l) {
                    $lId = (int)$l['id_lang'];
                    $lIso = strtolower($l['iso_lang']);

                    $translatedAbout = $db->getAboutPage($id, $lId);

                    if ($translatedAbout && !empty($translatedAbout['url_about'])) {
                        $hreflangUrls[$lId] = $urlTool->buildUrl([
                            'type' => 'about',
                            'id'   => $id,
                            'url'  => $translatedAbout['url_about'],
                            'iso'  => $lIso
                        ]);

                        if (isset($l['is_default']) && $l['is_default'] == 1) {
                            $this->view->assign('x_default_url', $hreflangUrls[$lId]);
                        }
                    }
                }
            }

            $this->view->assign('canonical_url', $officialUrl);

            // Assignation Smarty
            $this->view->assign([
                'about'     => $about,
                'json_ld'   => $jsonLdList,
                'hreflang'  => $hreflangUrls,
                'seo_title' => $seoTitle,
                'seo_desc'  => $seoDesc
            ]);
        }

        $this->view->display('about/index.tpl', $cacheId);
    }
}