<?php

declare(strict_types=1);

namespace App\Backend\Controller;

use App\Backend\Db\SettingDb;
use Magepattern\Component\HTTP\Request;
use Magepattern\Component\Tool\FormTool;

class SettingController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function run(): void
    {
        $action = $_GET['action'] ?? 'index';

        if ($action === 'save' && Request::isMethod('POST')) {
            $this->processSave();
            return;
        }

        $this->index();
    }

    private function index(): void
    {
        $db = new SettingDb();
        $settings = $db->fetchAllSettings();

        $this->view->assign([
            'settings'  => $settings,
            'hashtoken' => $this->session->getToken()
        ]);

        $this->view->display('setting/index.tpl');
    }

    private function processSave(): void
    {
        $token = $_POST['hashtoken'] ?? '';
        if (!$this->session->validateToken($token)) {
            $this->jsonResponse(false, 'Session expirée.');
        }

        $db = new SettingDb();
        $success = true;

        // 🟢 AJOUT ICI : 'product_catalog'
        $booleanKeys = [
            'concat', 'ssl',
            'http2', 'service_worker', 'amp', 'maintenance', 'geminiai', 'product_catalog'
        ];

        $postedSettings = $_POST['settings'] ?? [];

        foreach ($booleanKeys as $key) {
            if (!isset($postedSettings[$key])) {
                $postedSettings[$key] = '0';
            }
        }

        foreach ($postedSettings as $name => $value) {
            $cleanValue = FormTool::simpleClean((string)$value);

            if (!$db->updateSetting($name, $cleanValue)) {
                $success = false;
            }
        }

        if ($success) {
            $this->jsonResponse(true, 'La configuration a été mise à jour avec succès.', [
                'type' => 'update'
            ]);
        } else {
            $this->jsonResponse(false, 'Erreur lors de la mise à jour des paramètres.');
        }
    }
}