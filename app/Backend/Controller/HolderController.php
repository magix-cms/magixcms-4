<?php

declare(strict_types=1);

namespace App\Backend\Controller;

use App\Backend\Db\HolderDb;
use Magepattern\Component\HTTP\Request;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class HolderController extends BaseController
{
    public function run(): void
    {
        $action = $_GET['action'] ?? 'index';

        if ($action === 'generate' && Request::isMethod('POST')) {
            $this->generateHolders();
            return;
        }

        $this->index();
    }

    private function index(): void
    {
        $holderDir = ROOT_DIR . 'img/default/';
        $holders = [];

        // La même liste blanche
        $allowedModules = ['product', 'category', 'news', 'pages', 'about'];

        if (is_dir($holderDir)) {
            $files = scandir($holderDir);
            foreach ($files as $file) {
                if (in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png'])) {

                    // 🟢 On vérifie si le fichier commence par l'un de nos modules autorisés
                    $parts = explode('_', $file);
                    if (in_array($parts[0], $allowedModules)) {
                        $holders[] = $file;
                    }

                }
            }
        }

        $this->view->assign([
            'holders'   => $holders,
            'hashtoken' => $this->session->getToken()
        ]);

        $this->view->display('holder/index.tpl');
    }

    private function generateHolders(): void
    {
        $token = $_POST['hashtoken'] ?? '';
        if (!$this->session->validateToken($token)) {
            $this->jsonResponse(false, 'Session expirée.');
            return;
        }

        $db = new HolderDb();
        $configs  = $db->getAllImageConfigs();
        $settings = $db->getHolderSettings();
        $logoName = $db->getActiveLogo();

        if (empty($configs)) {
            $this->jsonResponse(false, 'Aucune configuration d\'image n\'a été trouvée.');
            return;
        }

        // 🟢 SÉCURITÉ : Vérification de la couleur Hexadécimale
        $bgColor = $settings['holder_bgcolor'] ?? '#ffffff';
        if (!str_starts_with($bgColor, '#')) {
            $bgColor = '#' . $bgColor;
        }

        $logoPercent = (int)($settings['logo_percent'] ?? 50);
        $outputDir   = ROOT_DIR . 'img/default/';
        $logoPath    = $logoName ? ROOT_DIR . 'img/logo/' . $logoName : null;
        $hasLogo     = $logoPath && file_exists($logoPath);

        // 🟢 SÉCURITÉ : GD ne sait pas lire les SVG. On ignore le logo si c'en est un.
        if ($hasLogo) {
            $ext = strtolower(pathinfo($logoPath, PATHINFO_EXTENSION));
            if ($ext === 'svg') {
                $hasLogo = false;
            }
        }

        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        $manager = new ImageManager(new Driver());
        $generatedFiles = [];

        // 🟢 1. LA LISTE BLANCHE (Whitelist)

        $allowedModules = ['product', 'category', 'news', 'pages', 'about'];

        try {
            foreach ($configs as $conf) {
                // 🟢 CORRECTION : On vérifie l'existence de l'attribut et du type
                if (empty($conf['attribute_img']) || empty($conf['type_img'])) {
                    continue;
                }

                // 🟢 LA MAGIE EST ICI : On utilise attribute_img !
                // Il contiendra naturellement 'product', 'category', 'news', etc.
                $module = strtolower((string)$conf['attribute_img']);

                // 🟢 2. ON FILTRE LES MODULES INUTILES
                if (!in_array($module, $allowedModules, true)) {
                    continue;
                }

                $type   = strtolower((string)$conf['type_img']);
                $width  = (int)($conf['width_img'] ?? 0);
                $height = (int)($conf['height_img'] ?? 0);

                if ($width <= 0 || $height <= 0) {
                    continue;
                }

                $image = $manager->create($width, $height)->fill($bgColor);

                if ($hasLogo) {
                    $logo = $manager->read($logoPath);
                    $targetLogoWidth = (int)($width * ($logoPercent / 100));

                    if ($targetLogoWidth > 10) {
                        $logo->scale(width: $targetLogoWidth);
                        $image->place($logo, 'center');
                    }
                }

                $fileName = "{$module}_{$type}.jpg";
                $image->toJpeg(90)->save($outputDir . $fileName);

                // 🟢 3. ON EMPÊCHE LES DOUBLONS
                $generatedFiles[$fileName] = $fileName;
            }

            if (empty($generatedFiles)) {
                $this->jsonResponse(false, 'Aucune dimension trouvée pour les modules autorisés.');
                return;
            }

            // On remet les clés à zéro pour Smarty (0, 1, 2, 3...)
            $finalFilesList = array_values($generatedFiles);

            $this->view->assign([
                'data'     => $finalFilesList,
                'site_url' => $this->view->getTemplateVars('site_url')
            ]);

            $htmlOutput = $this->view->fetch('holder/loop/holders.tpl');

            $this->jsonResponse(true, 'Les images de substitution ont été générées avec succès.', [
                'count' => count($finalFilesList),
                'html'  => $htmlOutput
            ]);

        } catch (\Exception $e) {
            $this->jsonResponse(false, 'Erreur de génération : ' . $e->getMessage());
        }
    }
}