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
        $catalogDb = new CatalogDb();

        // On utilise la même logique que Homepage : l'action 'edit' déclenche la sauvegarde
        if (Request::isMethod('POST') && Request::isGet('action') && $_GET['action'] === 'edit') {
            $this->processSave($catalogDb);
            return;
        }

        // Chargement des données au format unifié ['id_page' => X, 'content' => [...]]
        $pageData = $catalogDb->getCatalogHomeData();

        $this->view->assign([
            'page'      => $pageData,
            'hashtoken' => $this->session->getToken()
        ]);

        $this->view->display('catalog/index.tpl');
    }

    private function processSave(CatalogDb $db): void
    {
        $token = Request::isPost('hashtoken') ? $_POST['hashtoken'] : '';

        if (!$this->session->validateToken($token)) {
            $this->jsonResponse(false, 'Session expirée ou jeton invalide.');
        }

        if (isset($_POST['content']) && is_array($_POST['content'])) {
            $idPage = $db->getOrInsertCatalogHomeId();

            if ($idPage === 0) {
                $this->jsonResponse(false, 'Erreur critique : Impossible de créer la page racine du catalogue.');
            }

            $success = true;

            foreach ($_POST['content'] as $idLang => $values) {
                // Utilisation de ?? '' pour éviter les erreurs PHP 8
                $title     = $values['title_page'] ?? '';
                $content   = $values['content_page'] ?? '';
                $seoTitle  = $values['seo_title_page'] ?? '';
                $seoDesc   = $values['seo_desc_page'] ?? '';
                $published = isset($values['published']) ? 1 : 0;

                $data = [
                    'title_page'     => FormTool::simpleClean($title),
                    'content_page'   => $content,
                    'seo_title_page' => FormTool::simpleClean($seoTitle),
                    'seo_desc_page'  => FormTool::simpleClean($seoDesc),
                    'published'      => $published
                ];

                if (!$db->saveCatalogContent($idPage, (int)$idLang, $data)) {
                    $success = false;
                }
            }

            if ($success) {
                $this->jsonResponse(true, 'La page racine du catalogue a été mise à jour avec succès.', [
                    'type' => 'update',
                    'id'   => $idPage
                ]);
            } else {
                $this->jsonResponse(false, 'Erreur lors de la sauvegarde du contenu multilingue.');
            }
        } else {
            $this->jsonResponse(false, 'Aucune donnée reçue.');
        }
    }
}