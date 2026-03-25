<?php

declare(strict_types=1);

namespace App\Backend\Controller;

use App\Backend\Db\LogoDb;
use App\Component\File\UploadTool;
use App\Component\File\ImageTool;
use App\Backend\Db\CompanyDb;
use Magepattern\Component\HTTP\Url;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class LogoController extends BaseController
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
        $db = new LogoDb();
        $imageTool = new ImageTool();
        $companyDb = new CompanyDb();

        $idLangue = (int)$this->defaultLang['id_lang'];
        $activeLangs = $db->fetchLanguages();

        $company = $companyDb->getCompanyInfo();
        $defaultName = !empty($company['name']) ? $company['name'] : (!empty($company['name_info']) ? $company['name_info'] : 'logo-site');

        $rawLogos = $db->fetchAllLogos($idLangue);
        $formattedLogos = $imageTool->setModuleImages('logo', 'logo', $rawLogos, 0, '/img/logo/');

        $this->view->assign([
            'logos'        => $formattedLogos,
            'default_name' => $defaultName,
            'langs'        => $activeLangs,
            'token'        => $this->session->getToken(),
            'favicons'     => $this->getFaviconStatus()
        ]);

        $this->view->display('appearance/logo/index.tpl');
    }

    // 🟢 LA SOLUTION : Fonction pour générer logo, logo-1, logo-2 dynamiquement
    private function generateUniqueFilename(string $baseName, string $postKey): string
    {
        $cleanFileName = Url::clean($baseName);
        if (empty($cleanFileName)) {
            $cleanFileName = 'logo'; // Si vide, on force le mot "logo"
        }

        // On détermine l'extension du fichier uploadé
        $originalExt = 'jpg';
        if (isset($_FILES[$postKey]['name'])) {
            $ext = strtolower(pathinfo($_FILES[$postKey]['name'], PATHINFO_EXTENSION));
            if (!empty($ext)) {
                $originalExt = $ext;
            }
        }

        $targetDir = ROOT_DIR . 'img' . DS . 'logo' . DS;
        $finalName = $cleanFileName;
        $counter = 1;

        // Tant que le fichier existe déjà, on ajoute "-1", "-2", etc.
        while (file_exists($targetDir . $finalName . '.' . $originalExt)) {
            $finalName = $cleanFileName . '-' . $counter;
            $counter++;
        }

        return $finalName;
    }

    public function upload(): void
    {
        $token = $_POST['hashtoken'] ?? '';
        if (!$this->session->validateToken($token)) {
            $this->jsonResponse(false, 'Session invalide.');
        }

        // Si le nom est vide, on part de la base 'logo'
        $customName = trim($_POST['filename'] ?? '');
        $baseName = !empty($customName) ? $customName : 'logo';

        // 🟢 Utilisation de notre générateur de nom unique
        $uniqueFileName = $this->generateUniqueFilename($baseName, 'logo_file');

        $uploadTool = new UploadTool();
        $uploadResult = $uploadTool->singleImageUpload(
            'logo', 'logo', '', ['img', 'logo'],
            ['postKey' => 'logo_file', 'name' => $uniqueFileName]
        );

        if ($uploadResult['status'] === true) {
            $db = new LogoDb();
            $contents = $_POST['content'] ?? [];
            $db->insertLogo($uploadResult['file'], $contents);

            $this->jsonResponse(true, "Logo uploadé avec succès !", [
                'success' => true,
                'type'    => 'add'
            ]);
        }

        $this->jsonResponse(false, "Erreur upload: " . ($uploadResult['msg'] ?? ''), ['success' => false]);
    }

    public function activate(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        $db = new LogoDb();
        if ($id > 0 && $db->activateLogo($id)) {
            $this->jsonResponse(true, "Logo activé avec succès.");
        }
        $this->jsonResponse(false, "Erreur d'activation.");
    }

    public function delete(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        $db = new LogoDb();

        $filename = $db->getLogoFilename($id);

        if ($filename && $db->deleteLogo($id)) {
            $this->physicalDelete($filename);

            $this->jsonResponse(true, "Logo supprimé.", [
                'success' => true,
                'type'    => 'delete',
                'result'  => ['id' => $id]
            ]);
        }
        $this->jsonResponse(false, "Erreur de suppression.");
    }

    private function physicalDelete(?string $filename): void
    {
        if (empty($filename)) return;

        $dir = ROOT_DIR . 'img' . DS . 'logo' . DS;
        $fileNoExt = pathinfo($filename, PATHINFO_FILENAME);

        $filesToDelete = [
            $dir . $filename,
            $dir . $fileNoExt . '.webp'
        ];

        // On cherche les miniatures exactes (ex: s_logo.png, s_logo.webp)
        $variants = glob($dir . '*_' . $filename);
        if ($variants) $filesToDelete = array_merge($filesToDelete, $variants);

        $variantsWebp = glob($dir . '*_' . $fileNoExt . '.webp');
        if ($variantsWebp) $filesToDelete = array_merge($filesToDelete, $variantsWebp);

        foreach ($filesToDelete as $f) {
            if (file_exists($f) && is_file($f)) {
                @unlink($f);
            }
        }
    }

    public function updateContent(): void
    {
        $idLogo = (int)($_POST['id_logo'] ?? 0);
        $contents = $_POST['content'] ?? [];
        $db = new LogoDb();
        $success = false;

        if ($idLogo > 0) {
            if (!empty($contents)) {
                foreach ($contents as $idLang => $data) {
                    $db->updateLogoContent(
                        $idLogo,
                        (int)$idLang,
                        trim($data['alt_logo'] ?? ''),
                        trim($data['title_logo'] ?? '')
                    );
                }
                $success = true;
            }

            if (isset($_FILES['edit_logo_file']) && $_FILES['edit_logo_file']['error'] === UPLOAD_ERR_OK) {
                $oldFilename = $db->getLogoFilename($idLogo);

                $customName = trim($_POST['edit_filename'] ?? '');
                $baseName = !empty($customName) ? $customName : 'logo';

                // 🟢 On garantit l'unicité même lors d'un remplacement
                $uniqueFileName = $this->generateUniqueFilename($baseName, 'edit_logo_file');

                $uploadTool = new UploadTool();
                $uploadResult = $uploadTool->singleImageUpload(
                    'logo', 'logo', '', ['img', 'logo'],
                    ['postKey' => 'edit_logo_file', 'name' => $uniqueFileName]
                );

                if ($uploadResult['status'] === true) {
                    if ($oldFilename) {
                        $this->physicalDelete($oldFilename);
                    }
                    $db->updateLogoFilename($idLogo, $uploadResult['file']);
                    $success = true;
                }
            }

            if ($success) {
                $this->jsonResponse(true, "Mise à jour effectuée avec succès.", [
                    'success' => true,
                    'type'    => 'update',
                    'id'      => $idLogo
                ]);
            }
        }
        $this->jsonResponse(false, "Erreur lors de la mise à jour.", ['success' => false]);
    }

    public function getContent(): void
    {
        if (ob_get_length()) ob_clean();

        $idLogo = (int)($_GET['id'] ?? 0);
        $db = new LogoDb();
        $data = $db->getLogoContents($idLogo);

        echo json_encode(['status' => true, 'data' => $data]);
        exit;
    }

    public function getImages(): void
    {
        if (ob_get_length()) ob_clean();

        $db = new LogoDb();
        $imageTool = new ImageTool();
        $idLangue = (int)$this->defaultLang['id_lang'];

        $rawLogos = $db->fetchAllLogos($idLangue);
        $formattedLogos = $imageTool->setModuleImages('logo', 'logo', $rawLogos, 0, '/img/logo/');

        $this->view->assign('logos', $formattedLogos);
        $html = $this->view->fetch('appearance/logo/gallery.tpl');

        $this->jsonResponse(true, 'OK', ['result' => $html]);
    }

    public function activateFooter(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        $db = new LogoDb();
        if ($id > 0 && $db->activateFooterLogo($id)) {
            $this->jsonResponse(true, "Logo pour le footer activé avec succès.");
        }
        $this->jsonResponse(false, "Erreur lors de l'activation du logo footer.");
    }

    // ==========================================
    // GESTION DES FAVICONS
    // ==========================================
    public function getFaviconStatus(): array
    {
        $faviconDir = ROOT_DIR . 'img' . DS . 'favicon' . DS;
        $baseUrl = '/img/favicon/';

        $favicons = [
            'standard' => ['file' => 'favicon-32x32.png', 'exists' => false, 'url' => ''],
            'apple'    => ['file' => 'apple-touch-icon.png', 'exists' => false, 'url' => ''],
            'android'  => ['file' => 'android-chrome-192x192.png', 'exists' => false, 'url' => '']
        ];

        foreach ($favicons as $key => $data) {
            if (file_exists($faviconDir . $data['file'])) {
                $favicons[$key]['exists'] = true;
                $favicons[$key]['url'] = $baseUrl . $data['file'] . '?v=' . time();
            }
        }

        return $favicons;
    }

    public function uploadFavicon(): void
    {
        $token = $_POST['hashtoken'] ?? '';
        if (!$this->session->validateToken($token)) {
            $this->jsonResponse(false, 'Session invalide.');
        }

        if (!isset($_FILES['favicon_file']) || $_FILES['favicon_file']['error'] !== UPLOAD_ERR_OK) {
            $this->jsonResponse(false, "Veuillez sélectionner une image valide.");
        }

        $tmpName = $_FILES['favicon_file']['tmp_name'];
        $faviconDir = ROOT_DIR . 'img' . DS . 'favicon' . DS;

        if (!is_dir($faviconDir)) {
            mkdir($faviconDir, 0755, true);
        }

        try {
            $manager = new ImageManager(new Driver());

            $manager->read($tmpName)->scaleDown(192, 192)->save($faviconDir . 'android-chrome-192x192.png');
            $manager->read($tmpName)->scaleDown(180, 180)->save($faviconDir . 'apple-touch-icon.png');
            $manager->read($tmpName)->scaleDown(32, 32)->save($faviconDir . 'favicon-32x32.png');

            $this->jsonResponse(true, "Favicons générés avec succès !", [
                'success' => true,
                'favicons' => $this->getFaviconStatus()
            ]);

        } catch (\Exception $e) {
            $this->logger->log("Erreur création favicon : " . $e->getMessage(), "error");
            $this->jsonResponse(false, "Erreur lors de la génération des favicons.");
        }
    }

    public function deleteFavicons(): void
    {
        $token = $_POST['hashtoken'] ?? '';
        if (!$this->session->validateToken($token)) {
            $this->jsonResponse(false, 'Session invalide.');
        }

        $faviconDir = ROOT_DIR . 'img' . DS . 'favicon' . DS;
        $filesToDelete = ['favicon-32x32.png', 'apple-touch-icon.png', 'android-chrome-192x192.png'];
        $deleted = false;

        foreach ($filesToDelete as $file) {
            if (file_exists($faviconDir . $file)) {
                @unlink($faviconDir . $file);
                $deleted = true;
            }
        }

        if ($deleted) {
            $this->jsonResponse(true, "Favicons supprimés avec succès.", [
                'success' => true,
                'favicons' => $this->getFaviconStatus()
            ]);
        }

        $this->jsonResponse(false, "Aucun favicon à supprimer.");
    }
}