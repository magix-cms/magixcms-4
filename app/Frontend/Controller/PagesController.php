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
        $idLang = (int)($this->currentLang['id_lang'] ?? 1);
        $siteUrl = $this->view->getTemplateVars('site_url');

        $db = new PagesDb();
        $rawPage = $db->getPagesPage($id, $idLang);

        if (!$rawPage) {
            $this->render404();
            return;
        }

        $companyDb = new CompanyDb();
        $companyInfo = $companyDb->getCompanyInfo();
        $skinFolder = $this->siteSettings['theme']['value'] ?? 'default'; // 🟢 Ajout du skin

        // 1. Formatage
        $pages = PagesPresenter::format($rawPage, $this->currentLang, $siteUrl, $companyInfo, $skinFolder);

        // 2. Galerie
        $pages['gallery'] = [];
        $images = $db->getPagesImages($id, $idLang);
        foreach ($images as $imgRow) {
            $pages['gallery'][] = PagesPresenter::format(array_merge($rawPage, $imgRow), $this->currentLang, $siteUrl, $companyInfo, $skinFolder)['img'];
        }

        // 3. Sous-pages
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

        // 🟢 NOUVEAU : GÉNÉRATION DU TABLEAU HREFLANG
        $allLangs = $this->view->getTemplateVars('langs'); // Récupéré depuis le BaseController
        $hreflangUrls = [];
        $urlTool = new UrlTool();

        if ($allLangs && is_array($allLangs)) {
            foreach ($allLangs as $l) {
                $lId = (int)$l['id_lang'];
                $lIso = strtolower($l['iso_lang']);

                // On récupère les données de la page pour la langue de la boucle
                $translatedPage = $db->getPagesPage($id, $lId);

                // Si la page existe et a une URL dans cette langue
                if ($translatedPage && !empty($translatedPage['url_pages'])) {
                    $hreflangUrls[$lId] = $urlTool->buildUrl([
                        'type' => 'pages',
                        'id'   => $id,
                        'url'  => $translatedPage['url_pages'],
                        'iso'  => $lIso
                    ]);

                    // On définit l'URL x-default (la langue par défaut du site)
                    if (isset($l['is_default']) && $l['is_default'] == 1) {
                        $this->view->assign('x_default_url', $hreflangUrls[$lId]);
                    }
                }
            }
        }

        // 1. Assignation Smarty modifiée
        $this->view->assign([
            'pages'     => $pages,
            'json_ld'   => $jsonLdList,
            'seo_title' => $pages['seo']['title'],
            'seo_desc'  => $pages['seo']['description'],
            'hreflang'  => $hreflangUrls // 🟢 On passe le tableau au template !
        ]);

        $this->view->display('pages/index.tpl');
    }

    private function render404(): void
    {
        header("HTTP/1.0 404 Not Found");
        $tpl404 = 'errors/404.tpl';
        if ($this->view->templateExists($tpl404)) $this->view->display($tpl404);
        else die("Erreur 404 : Template manquant.");
    }
}