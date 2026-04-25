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

        // 🟢 NOUVELLE ROUTE AJAX : Pour charger les tailles sans bloquer la page
        if ($action === 'getSizes' && Request::isMethod('POST')) {
            $this->processGetSizes();
            return;
        }

        $this->index();
    }

    /**
     * NOUVEAU : Calcule les tailles uniquement quand le navigateur le demande
     */
    private function processGetSizes(): void
    {
        $token = $_POST['hashtoken'] ?? '';
        if (!$this->session->validateToken($token)) {
            $this->jsonResponse(false, 'Session expirée.');
        }

        clearstatcache();

        $sizes = [
            'front_tpl'         => $this->getDirSize(ROOT_DIR . 'var/templates_c'),
            'front_tpl_cache'   => $this->getDirSize(ROOT_DIR . 'var/tpl_caches'),
            'front_cache'       => $this->getDirSize(ROOT_DIR . 'var/caches'),
            'front_sql'         => $this->getDirSize(ROOT_DIR . 'var/caches/sql'),
            'front_log'         => $this->getDirSize(ROOT_DIR . 'var/log'),

            'back_tpl'          => $this->getDirSize(ROOT_DIR . BASEADMIN . '/var/templates_c'),
            'back_tpl_cache'    => $this->getDirSize(ROOT_DIR . BASEADMIN . '/var/tpl_caches'),
            'back_cache'        => $this->getDirSize(ROOT_DIR . BASEADMIN . '/var/caches'),
            'back_sql'          => $this->getDirSize(ROOT_DIR . BASEADMIN . '/var/caches/sql'),
            'back_log'          => $this->getDirSize(ROOT_DIR . BASEADMIN . '/var/log'),
        ];

        $this->jsonResponse(true, 'Tailles calculées', ['sizes' => $sizes]);
    }

    private function index(): void
    {
        // 🟢 FORCAGE DU CACHE STAT : Garantit que les tailles affichées sont réelles
        clearstatcache();

        // On récupère la taille des dossiers pour information
        $sizes = [
            'front_tpl'         => $this->getDirSize(ROOT_DIR . 'var/templates_c'),
            'front_tpl_cache'   => $this->getDirSize(ROOT_DIR . 'var/tpl_caches'), // 🟢 NOUVEAU
            'front_cache'       => $this->getDirSize(ROOT_DIR . 'var/caches'),
            'front_sql'         => $this->getDirSize(ROOT_DIR . 'var/caches/sql'), // 🟢 CORRIGÉ
            'front_log'         => $this->getDirSize(ROOT_DIR . 'var/log'),

            'back_tpl'          => $this->getDirSize(ROOT_DIR . BASEADMIN . '/var/templates_c'),
            'back_tpl_cache'    => $this->getDirSize(ROOT_DIR . BASEADMIN . '/var/tpl_caches'), // 🟢 NOUVEAU
            'back_cache'        => $this->getDirSize(ROOT_DIR . BASEADMIN . '/var/caches'),
            'back_sql'          => $this->getDirSize(ROOT_DIR . BASEADMIN . '/var/caches/sql'), // 🟢 CORRIGÉ
            'back_log'          => $this->getDirSize(ROOT_DIR . BASEADMIN . '/var/log'),
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
            'front_tpl'         => ROOT_DIR . 'var/templates_c',
            'front_tpl_cache'   => ROOT_DIR . 'var/tpl_caches', // 🟢 NOUVEAU
            'front_cache'       => ROOT_DIR . 'var/caches',
            'front_sql'         => ROOT_DIR . 'var/caches/sql', // 🟢 CORRIGÉ
            'front_log'         => ROOT_DIR . 'var/log',

            'back_tpl'          => ROOT_DIR . BASEADMIN . '/var/templates_c',
            'back_tpl_cache'    => ROOT_DIR . BASEADMIN . '/var/tpl_caches', // 🟢 NOUVEAU
            'back_cache'        => ROOT_DIR . BASEADMIN . '/var/caches',
            'back_sql'          => ROOT_DIR . BASEADMIN . '/var/caches/sql', // 🟢 CORRIGÉ
            'back_log'          => ROOT_DIR . BASEADMIN . '/var/log',
        ];

        $clearedCount = 0;
        $hasCacheCleared = false;

        foreach ($targets as $target) {
            if (array_key_exists($target, $pathsMapping)) {
                $dirPath = $pathsMapping[$target];
                if ($this->emptyDirectorySafe($dirPath)) {
                    $clearedCount++;

                    // On détecte si on vient de vider un dossier lié au cache ou SQL
                    if (str_contains($target, 'tpl') || str_contains($target, 'cache') || str_contains($target, 'sql')) {
                        $hasCacheCleared = true;
                    }
                }
            }
        }

        // 🟢 PURGE OPCACHE : Indispensable après avoir supprimé physiquement des fichiers de cache/template
        if ($hasCacheCleared && function_exists('opcache_reset')) {
            @opcache_reset();
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

        try {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            );

            $protectedFiles = ['.htaccess', 'index.html', 'index.php', '.gitignore'];

            foreach ($files as $fileinfo) {
                $realPath = $fileinfo->getRealPath();

                // Protection des fichiers vitaux
                if ($fileinfo->isFile() && in_array($fileinfo->getFilename(), $protectedFiles)) {
                    continue;
                }

                if ($fileinfo->isDir()) {
                    @rmdir($realPath); // Suppression des sous-dossiers
                } else {
                    @unlink($realPath); // Suppression des fichiers
                }
            }
            return true;

        } catch (\Exception $e) {
            // 🟢 ANTI-CRASH : Si un fichier de log est verrouillé par le serveur en écriture
            return false;
        }
    }

    /**
     * Utilitaire : Calcule le poids d'un dossier pour l'affichage
     */
    private function getDirSize(string $dir): string
    {
        if (!is_dir($dir)) return '0 B';

        $size = 0;

        // 🟢 TURBO LINUX : Si disponible, 'du' calcule la taille 100x plus vite que PHP
        if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN' && function_exists('exec') && is_callable('exec')) {
            $output = @exec('du -sb ' . escapeshellarg($dir));
            if ($output) {
                $size = (float) explode("\t", $output)[0];
                return $this->formatSize($size);
            }
        }

        // Fallback PHP natif (plus lent mais compatible partout)
        try {
            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS)) as $file) {
                $size += $file->getSize();
            }
        } catch (\Exception $e) {
            // Ignorer silencieusement
        }

        return $this->formatSize((float)$size);
    }

    /**
     * Utilitaire de formatage (séparé pour la propreté)
     */
    private function formatSize(float $size): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;

        while ($size >= 1024 && $i < 4) {
            $size /= 1024;
            $i++;
        }

        return round($size, 2) . ' ' . $units[$i];
    }
}