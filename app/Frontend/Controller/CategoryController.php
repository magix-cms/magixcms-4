<?php

declare(strict_types=1);

namespace App\Frontend\Controller;

use App\Frontend\Db\CategoryDb;
use App\Frontend\Db\ProductDb; // 🟢 Import indispensable pour récupérer les produits !
use App\Frontend\Model\CategoryPresenter;
use App\Frontend\Model\ProductPresenter;
use Magepattern\Component\HTTP\Request;

class CategoryController extends BaseController
{
    public function run(): void
    {
        $id = Request::isGet('id') ? (int)$_GET['id'] : 0;

        $idLang = (int)($this->currentLang['id_lang'] ?? 1);
        $siteUrl = $this->view->getTemplateVars('site_url');

        $db = new CategoryDb();
        $rawCategory = $db->getCategoryPage($id, $idLang);

        if (!$rawCategory) {
            $this->render404();
            return;
        }

        // 1. Formatage de la catégorie courante
        $category = CategoryPresenter::format($rawCategory, $this->currentLang, $siteUrl);

        // 2. Galerie d'images de la catégorie
        $category['gallery'] = [];
        $images = $db->getCategoryImages($id, $idLang);
        foreach ($images as $imgRow) {
            $category['gallery'][] = CategoryPresenter::format(array_merge($rawCategory, $imgRow), $this->currentLang, $siteUrl)['img'];
        }

        // 3. Récupération des sous-catégories (Rayons enfants)
        $category['subdata'] = [];
        $rawChildren = $db->getCategoryChildren($id, $idLang);
        if (!empty($rawChildren)) {
            foreach ($rawChildren as $childRow) {
                $category['subdata'][] = CategoryPresenter::format($childRow, $this->currentLang, $siteUrl);
            }
        }

        // 4. 🟢 LA LISTE DES PRODUITS (Via le moteur centralisé ProductDb)
        $category['products'] = [];
        $productDb = new ProductDb();

        // On récupère les produits en filtrant par la catégorie courante
        $rawProducts = $productDb->getProductList($idLang, [
            'id_cat' => $id
        ]);

        if (!empty($rawProducts)) {
            foreach ($rawProducts as $productRow) {
                // On utilise le ProductPresenter qui gère déjà l'override !
                $formattedProduct = ProductPresenter::format($productRow, $this->currentLang, $siteUrl);

                // Vérification de sécurité (si le format renvoie null car parent introuvable)
                if ($formattedProduct) {
                    $category['products'][] = $formattedProduct;
                }
            }
        }

        // Assignation à Smarty
        $this->view->assign([
            'category'  => $category,
            'seo_title' => $category['seo']['title'],
            'seo_desc'  => $category['seo']['description']
        ]);

        $this->view->display('catalog/category.tpl');
    }

    /**
     * Gestion centralisée de la 404
     */
    private function render404(): void
    {
        header("HTTP/1.0 404 Not Found");
        $tpl = 'errors/404.tpl';
        if ($this->view->templateExists($tpl)) {
            $this->view->display($tpl);
        } else {
            die("Erreur 404 : Template manquant.");
        }
    }
}