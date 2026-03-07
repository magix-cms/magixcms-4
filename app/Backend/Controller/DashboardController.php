<?php

declare(strict_types=1);

namespace App\Backend\Controller;

use App\Backend\Db\LangDb;
use App\Backend\Db\DashboardDb;
use App\Component\Hook\HookManager;
use App\Backend\Db\PagesDb;
use App\Backend\Db\ProductDb;
use Intervention\Image\File;
use Magepattern\Component\File\FileTool;

class DashboardController extends BaseController
{
    /**
     * L'aiguilleur (Routeur)
     */
    public function run(): void
    {
        $action = $_GET['action'] ?? 'index';

        // Si la méthode (ex: saveOrder) existe, on l'appelle
        if (method_exists($this, $action)) {
            $this->$action();
        } else {
            // Sinon, on affiche le dashboard normal
            $this->index();
        }
    }

    /**
     * L'affichage classique du Dashboard (Anciennement run())
     */
    public function index(): void
    {
        $idAdmin = (int)$this->session->get('id_admin');
        $db = new DashboardDb();

        $savedOrder = $db->fetchWidgetsOrder($idAdmin);
        // On récupère les vrais widgets
        $rawWidgets = HookManager::execToArray('dashboard_main');

        // --- AJOUT DU WIDGET DE TEST DANS LE TABLEAU POUR QU'IL SOIT TRIABLE ---
        $rawWidgets['TestWidget'] = '
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3" style="cursor: grab;">
                <h6 class="m-0 fw-bold text-muted">Widget de Test (PHP)</h6>
            </div>
            <div class="card-body">
                <p>Ce widget est maintenant injecté depuis le contrôleur, il gardera sa position !</p>
            </div>
        </div>';

        $langDb = new LangDb();
        $pageDb = new PagesDb();
        $productDb = new ProductDb();

        // --- CORRECTION DU CHEMIN UPLOADS ---
        // Essayez de retirer 'public' si votre dossier uploads est à la racine
        $mediaPath = ROOT_DIR . 'upload';

        // Si le dossier n'est pas trouvé, on le signale pour vous aider à debugger
        if (!is_dir($mediaPath)) {
            $mediaSizeFormatted = "Erreur chemin";
        } else {
            $mediaSizeBytes = FileTool::getDirectorySize($mediaPath);//$this->getDirectorySize($mediaPath);
            $mediaSizeFormatted = $this->formatBytes($mediaSizeBytes);
        }

        $this->view->assign([
            'widgets_main'       => $rawWidgets,
            'saved_order'        => $savedOrder,
            'total_langs'        => $langDb->countActiveLanguages(),
            'total_pages'        => $pageDb->countActivePages(),
            'total_products'     => $productDb->countProducts(),
            'total_media_size'   => $mediaSizeFormatted
        ]);

        $this->view->display('dashboard/index.tpl');
    }

    /**
     * L'action appelée par l'AJAX (Fetch)
     */
    public function saveOrder(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $order = $input['order'] ?? [];
        $idAdmin = (int)$this->session->get('id_admin');

        if ($idAdmin > 0 && !empty($order)) {
            $db = new DashboardDb();
            $db->updateWidgetsOrder($idAdmin, $order);
            // jsonResponse fait un "exit;", ce qui empêche le HTML de s'afficher !
            $this->jsonResponse(true, "Ordre enregistré");
        }

        $this->jsonResponse(false, "Erreur lors de l'enregistrement");
    }

    /**
     * Calcule la taille totale d'un dossier de manière récursive
     */
    private function getDirectorySize(string $directory): int
    {
        $size = 0;
        if (is_dir($directory)) {
            // RecursiveDirectoryIterator gère parfaitement les sous-dossiers !
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS)
            );
            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $size += $file->getSize();
                }
            }
        }
        return $size;
    }

    /**
     * Convertit des octets en format lisible
     */
    private function formatBytes(int $bytes): string
    {
        if ($bytes === 0) {
            return '0 o';
        }
        $units = ['o', 'Ko', 'Mo', 'Go', 'To'];
        $power = floor(log($bytes) / log(1024));
        $power = min($power, count($units) - 1);
        $bytes /= (1024 ** $power);

        return round($bytes, 2) . ' ' . $units[$power];
    }
}