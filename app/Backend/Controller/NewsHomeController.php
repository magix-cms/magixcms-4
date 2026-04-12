<?php

declare(strict_types=1);

namespace App\Backend\Controller;

use App\Backend\Db\NewsHomeDb;
use App\Backend\Db\LangDb;
use Magepattern\Component\HTTP\Request;
use Magepattern\Component\Tool\FormTool;
use App\Backend\Db\RevisionsDb;
use App\Component\Cache\CacheManager;

class NewsHomeController extends BaseController
{
    public function run(): void
    {
        $db = new NewsHomeDb();
        $activeLangs = (new LangDb())->fetchLanguages();

        if (Request::isMethod('POST') && Request::isGet('action') && $_GET['action'] === 'edit') {
            $this->processSave($db);
            return;
        }

        $pageData = $db->getHomeData();

        $this->view->assign([
            'page'      => $pageData,
            'langs'     => $activeLangs,
            'hashtoken' => $this->session->getToken()
        ]);

        // Vous pourrez créer un template `news/home.tpl` basé sur celui de `homepage/index.tpl`
        $this->view->display('news/home.tpl');
    }

    private function processSave(NewsHomeDb $db): void
    {
        $token = Request::isPost('hashtoken') ? $_POST['hashtoken'] : '';

        if (!$this->session->validateToken($token)) {
            $this->jsonResponse(false, 'Session expirée ou jeton invalide.');
        }

        if (isset($_POST['content']) && is_array($_POST['content'])) {
            $idPage = $db->getOrInsertHomeId();

            if ($idPage === 0) {
                $this->jsonResponse(false, 'Erreur critique : Impossible de créer la page d\'accueil des actualités.');
            }

            $success = true;

            foreach ($_POST['content'] as $idLang => $values) {
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
                    if (!empty($content)) {
                        $revDb = new RevisionsDb();
                        // Enregistrement de la révision pour l'historique
                        $revDb->saveRevision('news_home', $idPage, (int)$idLang, 'content_page', $content);
                    }
                }
            }

            if ($success) {
                // 🟢 Appel au manager global pour vider les caches de la liste des news
                CacheManager::clearFrontend('news_list');

                $this->jsonResponse(true, 'Configuration de la page d\'accueil mise à jour.', [
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