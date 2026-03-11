<?php

declare(strict_types=1);

namespace App\Frontend\Controller;

use App\Frontend\Db\AboutDb;
use App\Frontend\Model\AboutPresenter;
use App\Component\Routing\UrlTool;
use Magepattern\Component\HTTP\Request;

class AboutController extends BaseController
{
    public function run(): void
    {
        $id = Request::isGet('id') ? (int)$_GET['id'] : 0;
        $idLang = (int)($this->currentLang['id_lang'] ?? 1);
        $siteUrl = $this->view->getTemplateVars('site_url');

        $db = new AboutDb();
        $rawPage = $db->getAboutPage($id, $idLang);

        if (!$rawPage) {
            $this->render404();
            return;
        }

        // 1. Formatage de la page principale
        $about = AboutPresenter::format($rawPage, $this->currentLang, $siteUrl);

        // 2. Galerie d'images
        $about['gallery'] = [];
        $images = $db->getAboutImages($id, $idLang);
        foreach ($images as $imgRow) {
            $about['gallery'][] = AboutPresenter::format(array_merge($rawPage, $imgRow), $this->currentLang, $siteUrl)['img'];
        }

        // 3. Gestion de l'arborescence (Subdata & Root)
        $about['subdata'] = [];
        $about['root'] = [];

        $rawChildren = $db->getAboutChildren($id, $idLang);

        if (!empty($rawChildren)) {
            foreach ($rawChildren as $childRow) {
                $formattedChild = AboutPresenter::format($childRow, $this->currentLang, $siteUrl);

                // Règle : Si le parent est bien la page actuelle -> subdata
                // Sinon (parent manquant ou orphelin) -> re-attachement à root
                if ((int)$formattedChild['id_parent'] === $id) {
                    $about['subdata'][] = $formattedChild;
                } else {
                    $about['root'][] = $formattedChild;
                }
            }
        }

        // 4. Assignation Smarty
        $this->view->assign([
            'about'     => $about,
            'seo_title' => $about['seo']['title'],
            'seo_desc'  => $about['seo']['description']
        ]);

        $this->view->display('about/index.tpl');
    }

    /**
     * Centralisation de la gestion 404
     */
    private function render404(): void
    {
        header("HTTP/1.0 404 Not Found");
        $tpl404 = 'errors/404.tpl';
        if ($this->view->templateExists($tpl404)) {
            $this->view->display($tpl404);
        } else {
            die("Erreur 404 : Template de secours manquant.");
        }
    }
}