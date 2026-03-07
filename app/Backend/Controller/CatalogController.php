<?php

declare(strict_types=1);

namespace App\Backend\Controller;

use App\Backend\Db\CatalogDb;
use Magepattern\Component\HTTP\Request;
use Magepattern\Component\Tool\FormTool;

class CatalogController extends BaseController
{
    public function run(): void
    {
        // Pas besoin de routeur complexe, le root du catalogue EST la page d'accueil de la boutique.
        if (Request::isMethod('POST')) {
            $this->processSave();
            return;
        }

        $this->index();
    }

    private function index(): void
    {
        $db = new CatalogDb();

        // On assigne les données actuelles à la vue
        $this->view->assign([
            'catalog_home' => $db->getCatalogHome(),
            'content'      => $db->getCatalogHomeContent(),
            'hashtoken'    => $this->session->getToken()
        ]);

        $this->view->display('catalog/index.tpl');
    }

    private function processSave(): void
    {
        $token = $_POST['hashtoken'] ?? '';
        if (!$this->session->validateToken($token)) {
            $this->jsonResponse(false, 'Session expirée.');
        }

        $db = new CatalogDb();
        $langs = $this->view->getTemplateVars('langs') ?? [];
        $success = true;

        // On boucle sur toutes les langues actives
        foreach ($langs as $idLang => $iso) {

            // On récupère les données postées pour cette langue spécifique
            $title    = $_POST['title_page'][$idLang] ?? '';
            $content  = $_POST['content_page'][$idLang] ?? '';
            $seoTitle = $_POST['seo_title_page'][$idLang] ?? '';
            $seoDesc  = $_POST['seo_desc_page'][$idLang] ?? '';
            $status   = isset($_POST['published'][$idLang]) ? 1 : 0;

            // On ne sauvegarde que si un titre est renseigné
            if (!empty($title)) {
                $data = [
                    'title_page'     => FormTool::simpleClean($title),
                    'content_page'   => $content, // Attention: WYSIWYG, ne pas trop nettoyer !
                    'seo_title_page' => FormTool::simpleClean($seoTitle),
                    'seo_desc_page'  => FormTool::simpleClean($seoDesc),
                    'published'      => $status
                ];

                if (!$db->saveCatalogContent($idLang, $data)) {
                    $success = false;
                }
            }
        }

        if ($success) {
            $this->jsonResponse(true, 'La page racine du catalogue a été mise à jour avec succès.', ['type' => 'update']);
        } else {
            $this->jsonResponse(false, 'Une erreur est survenue lors de l\'enregistrement de certaines traductions.');
        }
    }
}