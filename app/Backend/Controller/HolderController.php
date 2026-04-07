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
        $socialDir = ROOT_DIR . 'img/social/'; // 🟢 Ajout du dossier Social
        $holders = [];

        $allowedModules = ['product', 'category', 'news', 'pages', 'about'];

        if (is_dir($holderDir)) {
            $files = scandir($holderDir);
            foreach ($files as $file) {
                if (in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png'])) {
                    $parts = explode('_', $file);
                    if (in_array($parts[0], $allowedModules)) {
                        $holders[] = $file;
                    }
                }
            }
        }

        // 🟢 Récupération de l'image sociale pour l'afficher si elle existe
        if (file_exists($socialDir . 'social_default.jpg')) {
            $holders[] = '../social/social_default.jpg'; // Chemin relatif depuis img/default/
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

        // Sécurité Hexa
        $bgColor = $settings['holder_bgcolor'] ?? '#ffffff';
        if (!str_starts_with($bgColor, '#')) {
            $bgColor = '#' . $bgColor;
        }

        $logoPercent = (int)($settings['logo_percent'] ?? 50);
        $outputDir   = ROOT_DIR . 'img/default/';
        $socialDir   = ROOT_DIR . 'img/social/'; // 🟢 Nouveau dossier

        $logoPath    = $logoName ? ROOT_DIR . 'img/logo/' . $logoName : null;
        $hasLogo     = $logoPath && file_exists($logoPath);

        // GD ne sait pas lire les SVG
        if ($hasLogo) {
            $ext = strtolower(pathinfo($logoPath, PATHINFO_EXTENSION));
            if ($ext === 'svg') {
                $hasLogo = false;
            }
        }

        if (!is_dir($outputDir)) mkdir($outputDir, 0755, true);
        if (!is_dir($socialDir)) mkdir($socialDir, 0755, true);

        $manager = new ImageManager(new Driver());
        $generatedFiles = [];

        $allowedModules = ['product', 'category', 'news', 'pages', 'about'];

        try {
            // --- 1. GÉNÉRATION DES MODULES ---
            foreach ($configs as $conf) {
                if (empty($conf['attribute_img']) || empty($conf['type_img'])) {
                    continue;
                }

                $module = strtolower((string)$conf['attribute_img']);
                if (!in_array($module, $allowedModules, true)) {
                    continue;
                }

                $type   = strtolower((string)$conf['type_img']);
                $width  = (int)($conf['width_img'] ?? 0);
                $height = (int)($conf['height_img'] ?? 0);

                if ($width <= 0 || $height <= 0) continue;

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
                $generatedFiles[$fileName] = $fileName;
            }

            // --- 2. GÉNÉRATION DE L'IMAGE OPEN GRAPH (SOCIAL) ---
            // Le ratio recommandé par FB/X/LinkedIn est de 1200x630 px.
            $socialWidth = 1200;
            $socialHeight = 630;

            $socialImg = $manager->create($socialWidth, $socialHeight)->fill($bgColor);

            if ($hasLogo) {
                $logo = $manager->read($logoPath);
                // On met le logo un peu plus petit sur les réseaux sociaux pour que ça respire
                $targetLogoWidth = (int)($socialWidth * (($logoPercent / 100) * 0.8));

                if ($targetLogoWidth > 10) {
                    $logo->scale(width: $targetLogoWidth);
                    $socialImg->place($logo, 'center');
                }
            }

            $socialFileName = 'social_default.jpg';
            $socialImg->toJpeg(90)->save($socialDir . $socialFileName);
            $generatedFiles[$socialFileName] = '../social/' . $socialFileName; // Pour l'affichage JS

            // --- 3. RETOUR JSON ---
            $finalFilesList = array_values($generatedFiles);

            $this->view->assign([
                'data'     => $finalFilesList,
                'site_url' => $this->view->getTemplateVars('site_url')
            ]);

            $htmlOutput = $this->view->fetch('holder/loop/holders.tpl');

            $this->jsonResponse(true, 'Les images et la vignette sociale ont été générées avec succès.', [
                'count' => count($finalFilesList),
                'html'  => $htmlOutput
            ]);

        } catch (\Exception $e) {
            $this->jsonResponse(false, 'Erreur de génération : ' . $e->getMessage());
        }
    }
}