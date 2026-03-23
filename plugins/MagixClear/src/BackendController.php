<?php

declare(strict_types=1);

namespace Plugins\MagixClear\src;

use App\Backend\Controller\BaseController;
use Magepattern\Component\HTTP\Request;

class BackendController extends BaseController
{
    public function run(): void
    {
        $action = $_GET['action'] ?? null;

        if ($action === 'clear' && Request::isMethod('POST')) {
            $this->processClear();
            return;
        }

        $this->index();
    }

    private function index(): void
    {
        // On récupère la taille des dossiers pour information
        $sizes = [
            'front_tpl'   => $this->getDirSize(ROOT_DIR . 'var/templates_c'),
            'front_cache' => $this->getDirSize(ROOT_DIR . 'var/caches'),
            'front_log'   => $this->getDirSize(ROOT_DIR . 'var/log'),
            'front_sql'   => $this->getDirSize(ROOT_DIR . 'var/sql'),
            'back_tpl'    => $this->getDirSize(ROOT_DIR . BASEADMIN . '/var/templates_c'),
            'back_cache'  => $this->getDirSize(ROOT_DIR . BASEADMIN . '/var/caches'),
            'back_log'    => $this->getDirSize(ROOT_DIR . BASEADMIN . '/var/log'),
            'back_sql'    => $this->getDirSize(ROOT_DIR . BASEADMIN . '/var/sql'),
        ];

        $this->view->assign([
            'sizes'     => $sizes,
            'hashtoken' => $this->session->getToken()
        ]);

        // Appel direct au template du plugin
        $this->view->display(ROOT_DIR . 'plugins/MagixClear/views/admin/index.tpl');
    }

    private function processClear(): void
    {
        $token = $_POST['hashtoken'] ?? '';
        if (!$this->session->validateToken($token)) {
            $this->jsonResponse(false, 'Session expirée ou jeton de sécurité invalide.');
        }

        $targets = $_POST['targets'] ?? [];

        if (empty($targets)) {
            $this->jsonResponse(false, 'Veuillez sélectionner au moins un dossier à vider.');
        }

        // Mapping des identifiants du formulaire vers les chemins réels
        $pathsMapping = [
            'front_tpl'   => ROOT_DIR . 'var/templates_c',
            'front_cache' => ROOT_DIR . 'var/caches',
            'front_log'   => ROOT_DIR . 'var/log',
            'front_sql'   => ROOT_DIR . 'var/sql',
            'back_tpl'    => ROOT_DIR . BASEADMIN . '/var/templates_c',
            'back_cache'  => ROOT_DIR . BASEADMIN . '/var/caches',
            'back_log'    => ROOT_DIR . BASEADMIN . '/var/log',
            'back_sql'    => ROOT_DIR . BASEADMIN . '/var/sql',
        ];

        $clearedCount = 0;

        foreach ($targets as $target) {
            if (array_key_exists($target, $pathsMapping)) {
                $dirPath = $pathsMapping[$target];
                if ($this->emptyDirectorySafe($dirPath)) {
                    $clearedCount++;
                }
            }
        }

        if ($clearedCount > 0) {
            // On envoie un reload pour mettre à jour les tailles des dossiers via MagixForms
            $this->jsonResponse(true, "Nettoyage terminé ($clearedCount dossier(s) traité(s)).", ['reload' => true]);
        } else {
            $this->jsonResponse(false, "Aucun fichier n'a pu être supprimé (Dossiers déjà vides ou permissions insuffisantes).");
        }
    }

    /**
     * Vide un dossier récursivement tout en protégeant les fichiers vitaux (.gitignore, .htaccess...)
     */
    private function emptyDirectorySafe(string $dir): bool
    {
        if (!is_dir($dir)) {
            return false;
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        $protectedFiles = ['.htaccess', 'index.html', 'index.php', '.gitignore'];
        $success = true;

        foreach ($files as $fileinfo) {
            $realPath = $fileinfo->getRealPath();

            // Protection des fichiers vitaux
            if ($fileinfo->isFile() && in_array($fileinfo->getFilename(), $protectedFiles)) {
                continue;
            }

            if ($fileinfo->isDir()) {
                // Échouera silencieusement si le dossier contient un fichier protégé (ex: racine templates_c)
                @rmdir($realPath);
            } else {
                @unlink($realPath);
            }
        }

        return $success;
    }

    /**
     * Utilitaire : Calcule le poids d'un dossier pour l'affichage
     */
    private function getDirSize(string $dir): string
    {
        if (!is_dir($dir)) return '0 B';

        $size = 0;
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS)) as $file) {
            $size += $file->getSize();
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        for ($i = 0; $size >= 1024 && $i < 4; $i++) {
            $size /= 1024;
        }

        return round($size, 2) . ' ' . $units[$i];
    }
}