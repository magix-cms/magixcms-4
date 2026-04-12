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

        // 🟢 1. Création de l'ID de cache unique pour cette URL exacte
        $cacheId = md5($_SERVER['REQUEST_URI']);

        // 🟢 2. Si le cache Smarty N'EST PAS valide, on exécute la logique métier
        if (!$this->view->isCached('about/index.tpl', $cacheId)) {

            $idLang = (int)($this->currentLang['id_lang'] ?? 1);
            $siteUrl = rtrim((string)$this->view->getTemplateVars('site_url'), '/');

            $db = new AboutDb();
            $rawPage = $db->getAboutPage($id, $idLang);

            if (!$rawPage) {
                $this->render404();
                return;
            }

            // 🟢 3. Sécurisation stricte des balises SEO (Fallback Anti-vide)
            $siteName = $this->siteSettings['site_name']['value'] ?? 'Magix CMS';

            $seoTitle = !empty($rawPage['seo_title_about'])
                ? $rawPage['seo_title_about']
                : ($rawPage['name_about'] ?? 'À propos') . ' - ' . $siteName;

            $seoDesc = !empty($rawPage['seo_desc_about'])
                ? $rawPage['seo_desc_about']
                : ($rawPage['resume_about'] ?? '');

            // Récupération des infos de l'entreprise
            $companyDb = new CompanyDb();
            $companyInfo = $companyDb->getCompanyInfo() ?: [];

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

                    // Si l'enfant a ce parent précis, c'est un sous-niveau
                    if ((int)$formattedChild['id_parent'] === $id) {
                        $about['subdata'][] = $formattedChild;
                    } else {
                        // Sinon c'est une ré-attache au root
                        $about['root'][] = $formattedChild;
                    }
                }
            }

            $jsonLdList = SeoHelper::generateItemListJsonLd($about['subdata']);

            // Génération du tableau Hreflang
            $allLangs = $this->view->getTemplateVars('langs');
            $hreflangUrls = [];
            $urlTool = new UrlTool();

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

            // Assignation Smarty
            $this->view->assign([
                'about'     => $about,
                'json_ld'   => $jsonLdList,
                'hreflang'  => $hreflangUrls,
                'seo_title' => $seoTitle,
                'seo_desc'  => $seoDesc
            ]);
        }

        // 🟢 4. Affichage de la vue (avec liaison de l'ID de cache)
        $this->view->display('about/index.tpl', $cacheId);
    }
}