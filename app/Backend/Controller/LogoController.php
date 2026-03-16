<?php

declare(strict_types=1);

namespace App\Backend\Controller;

use App\Backend\Db\LogoDb;
use App\Component\File\UploadTool;
use App\Component\File\ImageTool;
use App\Backend\Db\CompanyDb;
use Magepattern\Component\HTTP\Url;

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
        $defaultName = $company['name'] ?? $company['name_info'] ?? 'logo-site';

        $rawLogos = $db->fetchAllLogos($idLangue);

        // CORRECTION 1 : On appelle l'attribut 'logo' et non 'image'
        $formattedLogos = $imageTool->setModuleImages('logo', 'logo', $rawLogos, 0, '/img/logo/');

        $this->view->assign([
            'logos'        => $formattedLogos,
            'default_name' => $defaultName,
            'langs'        => $activeLangs,
            'token'        => $this->session->getToken()
        ]);

        $this->view->display('appearance/logo/index.tpl');
    }

    public function upload(): void
    {
        $token = $_POST['hashtoken'] ?? '';
        if (!$this->session->validateToken($token)) {
            $this->jsonResponse(false, 'Session invalide.');
        }

        $companyDb = new CompanyDb();
        $company = $companyDb->getCompanyInfo();

        $customName = trim($_POST['filename'] ?? '');
        $baseName = !empty($customName) ? $customName : ($company['name'] ?? 'logo');

        $cleanFileName = Url::clean($baseName);

        $uploadTool = new UploadTool();

        // CORRECTION 2 : Attribut 'logo' + Retrait du suffixe time()
        $uploadResult = $uploadTool->singleImageUpload(
            'logo', 'logo', '', ['img', 'logo'],
            ['postKey' => 'logo_file', 'name' => $cleanFileName]
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

    private function physicalDelete(string $filename): void
    {
        $dir = ROOT_DIR . 'img' . DS . 'logo' . DS;
        $fileNoExt = pathinfo($filename, PATHINFO_FILENAME);

        $files = glob($dir . '*{' . $filename . ',' . $fileNoExt . '.webp}', GLOB_BRACE);

        if ($files) {
            foreach ($files as $f) {
                if (file_exists($f)) @unlink($f);
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
            // 1. Mise à jour SEO (Multilingue)
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

            // 2. Remplacement Physique
            if (isset($_FILES['edit_logo_file']) && $_FILES['edit_logo_file']['error'] === UPLOAD_ERR_OK) {

                $oldFilename = $db->getLogoFilename($idLogo);
                $cleanFileName = Url::clean($_POST['edit_filename'] ?? 'logo-update');

                $uploadTool = new UploadTool();

                // CORRECTION 3 : Attribut 'logo' + Retrait du suffixe
                $uploadResult = $uploadTool->singleImageUpload(
                    'logo', 'logo', '', ['img', 'logo'],
                    ['postKey' => 'edit_logo_file', 'name' => $cleanFileName]
                );

                if ($uploadResult['status'] === true) {
                    if ($oldFilename) {
                        $this->physicalDelete($oldFilename); // On efface proprement l'ancien !
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

        // CORRECTION 4 : On appelle l'attribut 'logo'
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
}