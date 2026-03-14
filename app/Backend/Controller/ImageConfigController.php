<?php

declare(strict_types=1);

namespace App\Backend\Controller;

use App\Backend\Db\ImageConfigDb;
use Magepattern\Component\HTTP\Request;

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

        // Nettoyage et sécurisation des données
        $data = [
            'module_img'    => trim($_POST['module_img'] ?? ''),
            'attribute_img' => trim($_POST['attribute_img'] ?? ''),
            'width_img'     => (float)($_POST['width_img'] ?? 0),
            'height_img'    => (float)($_POST['height_img'] ?? 0),
            'type_img'      => trim($_POST['type_img'] ?? ''),
            'prefix_img'    => trim($_POST['prefix_img'] ?? ''),
            // On s'assure que l'enum est respecté
            'resize_img'    => in_array($_POST['resize_img'] ?? '', ['basic', 'adaptive']) ? $_POST['resize_img'] : 'basic'
        ];

        // Validation basique
        if (empty($data['module_img']) || empty($data['prefix_img']) || $data['width_img'] <= 0) {
            $this->jsonResponse(false, 'Veuillez remplir les champs obligatoires (Module, Préfixe, Largeur).');
        }

        $db = new ImageConfigDb();
        if ($db->saveConfig($data, $id)) {
            $this->jsonResponse(true, 'La configuration des images a été sauvegardée avec succès.');
        } else {
            $this->jsonResponse(false, 'Erreur lors de l\'enregistrement en base de données.');
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
}