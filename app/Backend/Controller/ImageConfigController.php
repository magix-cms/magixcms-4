<?php

declare(strict_types=1);

namespace App\Backend\Controller;

use App\Backend\Db\ImageConfigDb;
use Magepattern\Component\HTTP\Request;
use App\Component\File\UploadTool;

class ImageConfigController extends BaseController
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

    /**
     * Affiche la liste complète des configurations d'images
     */
    public function index(): void
    {
        $db = new ImageConfigDb();

        $this->view->assign([
            'configs' => $db->getAllConfigs(),
            'token'   => $this->session->getToken()
        ]);

        // J'ai mis un chemin logique, adaptez-le selon votre structure de dossiers
        $this->view->display('appearance/imageconfig/index.tpl');
    }

    /**
     * Ajoute ou modifie une configuration (via requête POST / AJAX)
     */
    /**
     * Ajoute ou modifie une configuration (via requête POST / AJAX)
     */
    public function save(): void
    {
        if (!Request::isMethod('POST')) {
            return;
        }

        $token = $_POST['hashtoken'] ?? '';
        if (!$this->session->validateToken($token)) {
            $this->jsonResponse(false, 'Session expirée.');
        }

        $id = (int)($_POST['id_config_img'] ?? 0);

        $data = [
            'module_img'    => trim($_POST['module_img'] ?? ''),
            'attribute_img' => trim($_POST['attribute_img'] ?? ''),
            'width_img'     => (float)($_POST['width_img'] ?? 0),
            'height_img'    => (float)($_POST['height_img'] ?? 0),
            'type_img'      => trim($_POST['type_img'] ?? ''),
            'prefix_img'    => trim($_POST['prefix_img'] ?? ''),
            'resize_img'    => in_array($_POST['resize_img'] ?? '', ['basic', 'adaptive']) ? $_POST['resize_img'] : 'basic'
        ];

        if (empty($data['module_img']) || empty($data['prefix_img']) || $data['width_img'] <= 0) {
            $this->jsonResponse(false, 'Veuillez remplir les champs obligatoires (Module, Préfixe, Largeur).');
        }

        $db = new ImageConfigDb();
        if ($db->saveConfig($data, $id)) {
            // Au lieu de traiter ici, on dit au JS : "Sauvegardé, tu peux lancer le job !"
            $this->jsonResponse(true, 'Configuration sauvegardée. Lancement de la mise à jour des images...', [
                'action' => 'start_batch',
                'module' => $data['module_img'],
                'attribute' => $data['attribute_img']
            ]);
        } else {
            $this->jsonResponse(false, 'Erreur lors de l\'enregistrement.');
        }
    }

    /**
     * Récupère les données d'une configuration pour remplir le formulaire Modal
     */
    public function edit(): void
    {
        $id = (int)($_GET['id'] ?? 0);

        if ($id > 0) {
            $db = new ImageConfigDb();
            $config = $db->getConfigById($id);

            if ($config) {
                $this->jsonResponse(true, 'OK', ['data' => $config]);
            }
        }

        $this->jsonResponse(false, 'Configuration introuvable.');
    }

    /**
     * Supprime une configuration d'image
     */
    public function delete(): void
    {
        if (!Request::isMethod('POST')) {
            return;
        }

        $token = $_POST['hashtoken'] ?? '';
        if (!$this->session->validateToken($token)) {
            $this->jsonResponse(false, 'Session expirée.');
        }

        $id = (int)($_POST['id'] ?? 0);

        if ($id > 0) {
            $db = new ImageConfigDb();
            if ($db->deleteConfig($id)) {
                $this->jsonResponse(true, 'La configuration a été supprimée.', [
                    'success' => true, 'type' => 'delete', 'result' => ['id' => $id]
                ]);
            }
        }

        $this->jsonResponse(false, 'Erreur lors de la suppression.');
    }
    /**
     * Prépare le terrain : Compte le nombre d'images à traiter
     */
    public function initBatch(): void
    {
        $module = $_POST['module'] ?? '';
        $attribute = $_POST['attribute'] ?? '';

        $uploadTool = new UploadTool();
        $files = $uploadTool->getOriginalImagesList($module, $attribute);

        $total = count($files);

        // On stocke temporairement la liste en session pour éviter de rescanner le dossier à chaque appel AJAX
        $_SESSION['batch_images_list'] = $files;

        $this->jsonResponse(true, 'Initialisation terminée', [
            'total' => $total
        ]);
    }

    /**
     * Traite un lot (ex: 10 images)
     */
    public function processBatch(): void
    {
        $module = $_POST['module'] ?? '';
        $attribute = $_POST['attribute'] ?? '';
        $offset = (int)($_POST['offset'] ?? 0);
        $limit = 10; // On traite 10 images par requête AJAX

        $files = $_SESSION['batch_images_list'] ?? [];
        $total = count($files);

        if (empty($files) || $offset >= $total) {
            unset($_SESSION['batch_images_list']); // Nettoyage
            $this->jsonResponse(true, 'Toutes les images ont été traitées !', ['finished' => true]);
        }

        $uploadTool = new UploadTool();
        $uploadTool->processImageBatch($files, $module, $attribute, $offset, $limit);

        // On calcule le prochain départ
        $nextOffset = $offset + $limit;

        $this->jsonResponse(true, 'Lot traité', [
            'finished' => false,
            'next_offset' => $nextOffset,
            'progress' => min(100, round(($nextOffset / $total) * 100))
        ]);
    }
}