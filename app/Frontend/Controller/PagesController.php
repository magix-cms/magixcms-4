<?php

declare(strict_types=1);

namespace App\Frontend\Controller;

use App\Frontend\Db\PagesDb;
use App\Frontend\Model\PagesPresenter;
use App\Component\Routing\UrlTool; // On importe l'UrlTool
use Magepattern\Component\HTTP\Request;

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
            $this->render404(); // On utilise une petite méthode privée pour plus de clarté
            return;
        }

        // Initialisation de l'UrlTool pour le passer au Presenter ou l'utiliser ici
        $urlTool = new UrlTool();

        // 1. Formatage de la page principale
        $pages = PagesPresenter::format($rawPage, $this->currentLang, $siteUrl);

        // 2. Galerie : On boucle et on formate
        $pages['gallery'] = [];
        $images = $db->getPagesImages($id, $idLang);
        foreach ($images as $imgRow) {
            $pages['gallery'][] = PagesPresenter::format(array_merge($rawPage, $imgRow), $this->currentLang, $siteUrl)['img'];
        }

        // 3. Sous-pages : Gestion subdata / root
        $pages['subdata'] = [];
        $pages['root'] = [];

        $rawChildren = $db->getPagesChildren($id, $idLang);

        if (!empty($rawChildren)) {
            foreach ($rawChildren as $childRow) {
                // On prépare les données pour l'UrlTool si besoin
                $formattedChild = PagesPresenter::format($childRow, $this->currentLang, $siteUrl);

                // Règle de rattachement :
                // Si l'id_parent est celui de la page actuelle -> subdata
                // Sinon (orphelin ou parent hors dataset) -> root
                if ((int)$formattedChild['id_parent'] === $id) {
                    $pages['subdata'][] = $formattedChild;
                } else {
                    $pages['root'][] = $formattedChild;
                }
            }
        }

        $this->view->assign([
            'pages'     => $pages,
            'seo_title' => $pages['seo']['title'],
            'seo_desc'  => $pages['seo']['description']
        ]);

        $this->view->display('pages/index.tpl');
    }

    /**
     * Centralisation de la gestion 404 pour éviter la duplication de code
     */
    private function render404(): void
    {
        header("HTTP/1.0 404 Not Found");
        $tpl404 = 'errors/404.tpl';
        if ($this->view->templateExists($tpl404)) {
            $this->view->display($tpl404);
        } else {
            die("Erreur 404 : Template manquant.");
        }
    }
}