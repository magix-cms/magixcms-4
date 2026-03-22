<?php

declare(strict_types=1);

namespace App\Backend\Controller;

use App\Backend\Db\HomepageDb;
use Magepattern\Component\HTTP\Request;
use Magepattern\Component\Tool\FormTool;
use App\Backend\Db\RevisionsDb;

class HomepageController extends BaseController
{
    public function run(): void
    {
        $homeDb = new HomepageDb();

        if (Request::isMethod('POST') && Request::isGet('action') && $_GET['action'] === 'edit') {
            $this->processSave($homeDb);
        }

        // 2. Chargement des données
        $pageData = $homeDb->getHomeData();

        // 3. Assignation à la vue
        $this->view->assign([
            'page'      => $pageData,
            'hashtoken' => $this->session->getToken()
        ]);

        $this->view->display('homepage/index.tpl');
    }

    private function processSave(HomepageDb $db): void
    {
        $token = Request::isPost('hashtoken') ? $_POST['hashtoken'] : '';

        if (!$this->session->validateToken($token)) {
            $this->jsonResponse(false, 'Session expirée ou jeton invalide.');
        }

        if (isset($_POST['content']) && is_array($_POST['content'])) {
            $idPage = $db->getOrInsertHomeId();

            // Sécurité : Si l'ID est 0, c'est que la création a échoué
            if ($idPage === 0) {
                $this->jsonResponse(false, 'Erreur critique : Impossible de créer la page d\'accueil racine.');
            }

            $success = true;

            foreach ($_POST['content'] as $idLang => $values) {
                // 🟢 CORRECTION : Utilisation de ?? '' pour éviter les erreurs "Undefined array key" en PHP 8
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

                if (!$db->saveContent($idPage, (int)$idLang, $data)) {
                    $success = false;
                } else {
                    // 🟢 AJOUT : Enregistrement dans l'historique si le contenu n'est pas vide
                    if (!empty($content)) {
                        $revDb = new RevisionsDb();
                        // Paramètres : item_type ('homepage'), item_id (souvent 1), id_lang, nom_du_champ, contenu
                        $revDb->saveRevision('homepage', $idPage, (int)$idLang, 'content_page', $content);
                    }
                }
            }

            if ($success) {
                $this->jsonResponse(true, 'Mise à jour réussie.', [
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