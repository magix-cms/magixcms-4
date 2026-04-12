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

        // 🟢 1. Création de l'ID de cache unique pour l'URL
        $cacheId = md5($_SERVER['REQUEST_URI']);

        // 🟢 2. Si la vue n'est pas en cache, on lance le calcul
        if (!$this->view->isCached('pages/index.tpl', $cacheId)) {

            $idLang = (int)($this->currentLang['id_lang'] ?? 1);
            $siteUrl = rtrim((string)$this->view->getTemplateVars('site_url'), '/');

            $db = new PagesDb();
            $rawPage = $db->getPagesPage($id, $idLang);

            if (!$rawPage) {
                $this->render404();
                return;
            }

            // 🟢 3. Sécurité SEO : Fallback Anti-vide depuis les données brutes
            $siteName = $this->siteSettings['site_name']['value'] ?? 'Magix CMS';

            $seoTitle = !empty($rawPage['seo_title_pages'])
                ? $rawPage['seo_title_pages']
                : ($rawPage['name_pages'] ?? 'Page') . ' - ' . $siteName;

            $seoDesc = !empty($rawPage['seo_desc_pages'])
                ? $rawPage['seo_desc_pages']
                : ($rawPage['resume_pages'] ?? '');

            $companyDb = new CompanyDb();
            $companyInfo = $companyDb->getCompanyInfo() ?: [];
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
                        // Ré-attache au root comme stipulé dans la logique métier de Magix CMS
                        $pages['root'][] = $formattedChild;
                    }
                }
            }

            $jsonLdList = SeoHelper::generateItemListJsonLd($pages['subdata']);

            // Génération du tableau Hreflang
            $allLangs = $this->view->getTemplateVars('langs');
            $hreflangUrls = [];
            $urlTool = new UrlTool();

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

            // Assignation Smarty
            $this->view->assign([
                'pages'     => $pages,
                'json_ld'   => $jsonLdList,
                'seo_title' => $seoTitle, // 🟢 Sécurisé
                'seo_desc'  => $seoDesc,  // 🟢 Sécurisé
                'hreflang'  => $hreflangUrls
            ]);
        }

        // 🟢 4. Affichage de la vue avec le Cache ID
        $this->view->display('pages/index.tpl', $cacheId);
    }
}