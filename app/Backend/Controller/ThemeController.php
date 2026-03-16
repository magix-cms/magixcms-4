<?php
declare(strict_types=1);

namespace App\Backend\Controller;

use App\Backend\Db\ThemeDb;

class ThemeController extends BaseController
{
    public function run(): void
    {
        $action = $_GET['action'] ?? 'index';
        if (method_exists($this, $action)) {
            $this->$action();
        } else {
            $this->index();
        }
    }

    public function index(): void
    {
        $db = new ThemeDb();
        $currentTheme = $db->getCurrentTheme();

        $skinDir = ROOT_DIR . 'skin' . DS;
        $themes = [];

        if (is_dir($skinDir)) {
            $folders = array_filter(glob($skinDir . '*'), 'is_dir');

            foreach ($folders as $folder) {
                $themeName = basename($folder);

                // Recherche de la vignette dans le dossier du skin
                // L'URL relative commence par ../skin/ pour l'affichage depuis le backend
                $previewUrl = 'https://placehold.co/600x400/eeeeee/999999?text=Aucun+Aper%C3%A7u';

                if (file_exists($folder . DS . 'preview.jpg')) {
                    $previewUrl = '../skin/' . $themeName . '/preview.jpg';
                } elseif (file_exists($folder . DS . 'preview.png')) {
                    $previewUrl = '../skin/' . $themeName . '/preview.png';
                }

                $themes[] = [
                    'name'      => $themeName,
                    'preview'   => $previewUrl,
                    'is_active' => ($themeName === $currentTheme)
                ];
            }
        }

        $this->view->assign('themes', $themes);
        $this->view->assign('hashtoken', $this->session->getToken());
        $this->view->display('appearance/theme/index.tpl');
    }

    public function activate(): void
    {
        $token = $_POST['hashtoken'] ?? '';
        if (!$this->session->validateToken($token)) {
            $this->jsonResponse(false, 'Session invalide.');
        }

        $themeName = $_POST['theme'] ?? '';
        $skinPath = ROOT_DIR . 'skin' . DS . $themeName;

        // On vérifie que le dossier du thème existe physiquement avant de l'enregistrer
        if (!empty($themeName) && is_dir($skinPath)) {
            $db = new ThemeDb();
            if ($db->setActiveTheme($themeName)) {
                $this->jsonResponse(true, "Le thème '" . ucfirst($themeName) . "' est maintenant actif !");
            }
        }

        $this->jsonResponse(false, "Erreur lors de l'activation du thème.");
    }
}