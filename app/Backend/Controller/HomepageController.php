<?php

declare(strict_types=1);

namespace App\Backend\Controller;

use App\Backend\Db\HomepageDb;
use Magepattern\Component\HTTP\Request;
use Magepattern\Component\Tool\FormTool;

class HomepageController extends BaseController
{
    public function run(): void
    {
        $homeDb = new HomepageDb();

        // 1. Plus besoin de $session = new Session(false) ici !
        if (Request::isMethod('POST') && Request::isGet('action') && $_GET['action'] === 'edit') {
            $this->processSave($homeDb); // On ne passe même plus la session en paramètre
        }

        // 2. Chargement des données
        $pageData = $homeDb->getHomeData();

        // 3. Assignation à la vue en utilisant la session parente
        $this->view->assign([
            'page'      => $pageData,
            'hashtoken' => $this->session->getToken() // Accès direct
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
            $success = true;

            foreach ($_POST['content'] as $idLang => $values) {
                $data = [
                    'title_page'     => FormTool::simpleClean($values['title_page']),
                    'content_page'   => $values['content_page'],
                    'seo_title_page' => FormTool::simpleClean($values['seo_title_page']),
                    'seo_desc_page'  => FormTool::simpleClean($values['seo_desc_page']),
                    'published'      => isset($values['published']) ? 1 : 0
                ];

                if (!$db->saveContent($idPage, (int)$idLang, $data)) {
                    $success = false;
                }
            }

            if ($success) {
                // On renvoie un succès JSON avec l'ID pour ton script JS
                $this->jsonResponse(true, 'Mise à jour réussie.', [
                    'type' => 'update',
                    'id'   => $idPage
                ]);
            } else {
                $this->jsonResponse(false, 'Erreur lors de la sauvegarde en base de données.');
            }
        }
    }
}