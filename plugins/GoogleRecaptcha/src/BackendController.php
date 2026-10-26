<?php
declare(strict_types=1);

namespace Plugins\GoogleRecaptcha\src;

use App\Backend\Controller\BaseController;
use Magepattern\Component\Tool\SmartyTool;
use Magepattern\Component\HTTP\Request;
use Magepattern\Component\Tool\FormTool;
use Plugins\GoogleRecaptcha\db\BackendDb;
use Magepattern\Component\Security\CryptTool;

class BackendController extends BaseController
{
    public function run(): void
    {
        SmartyTool::addTemplateDir('admin', ROOT_DIR . 'plugins' . DS . 'GoogleRecaptcha' . DS . 'views' . DS . 'admin');

        $action = $_GET['action'] ?? 'index';

        if ($action === 'saveKeys' && Request::isMethod('POST')) {
            $this->processSaveKeys();
            return;
        }

        if (method_exists($this, $action)) {
            $this->$action();
        } else {
            $this->index();
        }
    }

    private function index(): void
    {
        $db = new BackendDb();
        $keys = $db->getKeys();

        // Masquage de la clé secrète pour l'affichage (ne sera jamais envoyée au HTML en clair)
        // La clé 'site_key' (clé publique) peut rester visible.
        $displaySecretKey = '';
        if (!empty($keys['secret_key'])) {
            $displaySecretKey = '********';
        }

        $this->view->assign([
            'title_plugin' => 'Configuration Google reCAPTCHA v3',
            'site_key'     => $keys['site_key'] ?? '',
            'secret_key'   => $displaySecretKey,
            'hashtoken'    => $this->session->getToken()
        ]);

        $this->view->display('config.tpl');
    }

    private function processSaveKeys(): void
    {
        $token = $_POST['hashtoken'] ?? '';
        if (!$this->session->validateToken($token)) {
            $this->jsonResponse(false, 'Session expirée.');
        }

        $siteKey = FormTool::simpleClean($_POST['site_key'] ?? '');
        $postedSecretKey = $_POST['secret_key'] ?? '';

        $db = new BackendDb();

        // Interception de la clé secrète
        if ($postedSecretKey === '********') {
            // Si la valeur est '********', c'est que l'utilisateur ne l'a pas modifiée.
            // On récupère donc l'ancienne valeur (déjà chiffrée) en base de données
            // pour ne pas l'écraser avec des astérisques.
            $existingKeys = $db->getKeys();
            $secretKey = $existingKeys['secret_key'] ?? '';
        } elseif (!empty($postedSecretKey)) {
            // Si l'utilisateur a tapé une nouvelle clé, on la nettoie et on la chiffre avant de la sauvegarder.
            $cleanKey = FormTool::simpleClean($postedSecretKey);
            $secretKey = CryptTool::encrypt($cleanKey);
        } else {
            // Si le champ a été vidé volontairement par l'utilisateur, on sauvegarde une chaîne vide.
            $secretKey = '';
        }

        if ($db->saveKeys($siteKey, $secretKey)) {
            $this->jsonResponse(true, 'Les clés reCAPTCHA ont été sauvegardées.', ['type' => 'update']);
        } else {
            $this->jsonResponse(false, 'Erreur lors de la sauvegarde.');
        }
    }
}