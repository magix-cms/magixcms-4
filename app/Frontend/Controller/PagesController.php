<?php

declare(strict_types=1);

namespace App\Frontend\Controller;

use App\Frontend\Db\PagesDb;
use App\Frontend\Db\CompanyDb;
use App\Frontend\Model\PagesPresenter;
use Magepattern\Component\HTTP\Request;
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

        $this->view->assign([
            'pages'     => $pages,
            'json_ld'   => $jsonLdList,
            'seo_title' => $pages['seo']['title'],
            'seo_desc'  => $pages['seo']['description']
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