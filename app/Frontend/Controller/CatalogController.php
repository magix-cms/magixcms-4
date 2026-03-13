<?php

declare(strict_types=1);

namespace App\Frontend\Controller;

use App\Frontend\Db\CatalogDb;
use App\Frontend\Model\CatalogHomePresenter;
use App\Frontend\Model\CategoryPresenter;
use Magepattern\Component\HTTP\Request;
use App\Frontend\Controller\ProductController;
use App\Frontend\Controller\CategoryController;


class CatalogController extends BaseController
{
    public function run(): void
    {
        // 1. L'AIGUILLEUR (DISPATCHER)
        $id = Request::isGet('id') ? (int)$_GET['id'] : 0;
        $idParent = Request::isGet('id_parent') ? (int)$_GET['id_parent'] : 0;

        // Si on a un parent ET un id, c'est forcément un produit !
        if ($idParent > 0 && $id > 0) {
            $productController = new ProductController();
            $productController->run();
            return;
        }

        // Si on a juste un id, c'est une catégorie !
        if ($id > 0) {
            $categoryController = new CategoryController();
            $categoryController->run();
            return;
        }

        // --- 2. CODE NORMAL DE LA PAGE D'ACCUEIL DU CATALOGUE ---
        $idLang = (int)($this->currentLang['id_lang'] ?? 1);
        $siteUrl = $this->view->getTemplateVars('site_url');

        $db = new CatalogDb();
        $rawHome = $db->getHomePage($idLang);

        if (!$rawHome) {
            $catalogHome = [
                'title'   => 'Notre Catalogue',
                'content' => '',
                'seo'     => ['title' => 'Catalogue', 'description' => 'Découvrez tous nos produits.']
            ];
        } else {
            $catalogHome = CatalogHomePresenter::format($rawHome);
        }

        $catalogHome['subdata'] = [];
        $rawCategories = $db->getRootCategories($idLang);

        if (!empty($rawCategories)) {
            foreach ($rawCategories as $catRow) {
                $catalogHome['subdata'][] = CategoryPresenter::format($catRow, $this->currentLang, $siteUrl);
            }
        }

        $this->view->assign([
            'catalog_home' => $catalogHome,
            'seo_title'    => $catalogHome['seo']['title'],
            'seo_desc'     => $catalogHome['seo']['description']
        ]);

        $this->view->display('catalog/index.tpl');
    }
}