<?php
declare(strict_types=1);

namespace Plugins\GoogleRecaptcha\src;

use App\Backend\Controller\BaseController;
use Magepattern\Component\Tool\SmartyTool;
use Magepattern\Component\HTTP\Request;
use Magepattern\Component\Tool\FormTool;
use Plugins\GoogleRecaptcha\db\BackendDb;

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

        $this->view->assign([
            'title_plugin' => 'Configuration Google reCAPTCHA v3',
            'site_key'     => $keys['site_key'],
            'secret_key'   => $keys['secret_key'],
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
        $secretKey = FormTool::simpleClean($_POST['secret_key'] ?? '');

        $db = new BackendDb();
        if ($db->saveKeys($siteKey, $secretKey)) {
            $this->jsonResponse(true, 'Les clés reCAPTCHA ont été sauvegardées.', ['type' => 'update']);
        } else {
            $this->jsonResponse(false, 'Erreur lors de la sauvegarde.');
        }
    }
}