<?php

declare(strict_types=1);

namespace Plugins\Contact\src;

use App\Backend\Controller\BaseController;
use Plugins\Contact\db\ContactDb;
use Magepattern\Component\HTTP\Request;
use Magepattern\Component\Tool\FormTool;
use Magepattern\Component\Tool\SmartyTool;
use Magepattern\Component\HTTP\Url;

class BackendController extends BaseController
{
    public function run(): void
    {
        SmartyTool::addTemplateDir('admin', ROOT_DIR . 'plugins' . DS . 'Contact' . DS . 'views' . DS . 'admin');

        $action = $_GET['action'] ?? 'index';

        if ($action === 'savePage' && Request::isMethod('POST')) {
            $this->processSavePage();
            return;
        }

        if ($action === 'saveContact' && Request::isMethod('POST')) {
            $this->processSaveContact();
            return;
        }

        if ($action === 'deleteContact') {
            $this->processDeleteContact();
            return;
        }

        // 🟢 NOUVELLE ROUTE AJAX POUR L'ÉDITION
        if ($action === 'getContact') {
            $this->processGetContact();
            return;
        }

        if (method_exists($this, $action)) {
            $this->$action();
        } else {
            $this->index();
        }
    }

    // 🟢 NOUVELLE MÉTHODE
    private function processGetContact(): void
    {
        if (ob_get_length()) ob_clean(); // Assure un JSON propre

        $idContact = (int)($_GET['id_contact'] ?? 0);
        $db = new ContactDb();

        $data = $db->getContactFull($idContact);

        if (!empty($data)) {
            $this->jsonResponse(true, 'OK', ['contact' => $data]);
        } else {
            $this->jsonResponse(false, 'Contact introuvable.');
        }
    }

    private function index(): void
    {
        $db = new ContactDb();
        $idLangDefault = (int)($this->defaultLang['id_lang'] ?? 1);

        $langs = $db->fetchLanguages();
        $pageData = [];

        if ($langs) {
            foreach ($langs as $idLang => $iso) {
                $pageData[$idLang] = $db->getPageContent((int)$idLang);
            }
        }

        $contactsList = $db->getContactsList($idLangDefault);

        $this->view->assign([
            'langs'        => $langs,
            'pageData'     => $pageData,
            'contactsList' => $contactsList,
            'hashtoken'    => $this->session->getToken()
        ]);

        $this->view->display('index.tpl');
    }

    private function processSavePage(): void
    {
        $token = $_POST['hashtoken'] ?? '';
        if (!$this->session->validateToken($token)) {
            $this->jsonResponse(false, 'Session expirée.');
        }

        $db = new ContactDb();
        $contentData = $_POST['content'] ?? [];

        foreach ($contentData as $idLang => $data) {
            // 🟢 PLUS DE GESTION D'URL ICI
            $cleanData = [
                'name_page'      => FormTool::simpleClean($data['name_page'] ?? ''),
                'seo_title_page' => FormTool::simpleClean($data['seo_title_page'] ?? ''),
                'seo_desc_page'  => FormTool::simpleClean($data['seo_desc_page'] ?? ''),
                'resume_page'    => FormTool::simpleClean($data['resume_page'] ?? ''),
                'content_page'   => $data['content_page'] ?? '',
                'published_page' => isset($data['published_page']) ? 1 : 0
            ];

            $db->savePageContent((int)$idLang, $cleanData);
        }

        $this->jsonResponse(true, 'La page de contact a été mise à jour.', ['type' => 'update']);
    }

    // 🟢 NOUVELLE MÉTHODE
    private function processSaveContact(): void
    {
        $token = $_POST['hashtoken'] ?? '';
        if (!$this->session->validateToken($token)) {
            $this->jsonResponse(false, 'Session expirée.');
        }

        $db = new ContactDb();
        $idContact = (int)($_POST['id_contact'] ?? 0);

        $mainData = [
            'mail_contact' => FormTool::simpleClean($_POST['mail_contact'] ?? ''),
            'is_default'   => isset($_POST['is_default']) ? 1 : 0
        ];

        if ($mainData['is_default'] === 1) {
            $db->resetDefaultContacts();
        }

        $contentData = [];
        if (isset($_POST['contact_content']) && is_array($_POST['contact_content'])) {
            foreach ($_POST['contact_content'] as $idLang => $c) {
                $contentData[$idLang] = [
                    'name_contact'      => FormTool::simpleClean($c['name_contact'] ?? ''),
                    'published_contact' => isset($c['published_contact']) ? 1 : 0
                ];
            }
        }

        if ($db->saveContact($idContact, $mainData, $contentData)) {
            $this->jsonResponse(true, 'Destinataire enregistré avec succès.', ['type' => 'add']);
        } else {
            $this->jsonResponse(false, 'Erreur lors de l\'enregistrement du destinataire.');
        }
    }

    private function processDeleteContact(): void
    {
        $idContact = (int)($_GET['id_contact'] ?? 0);
        $db = new ContactDb();

        if ($idContact > 0 && $db->deleteContact($idContact)) {
            $this->jsonResponse(true, 'Destinataire supprimé avec succès.', ['type' => 'delete']);
        }

        $this->jsonResponse(false, 'Erreur lors de la suppression.');
    }
}