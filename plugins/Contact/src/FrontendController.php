<?php

declare(strict_types=1);

namespace Plugins\Contact\src;

use App\Frontend\Controller\BaseController;
use Plugins\Contact\db\ContactFrontDb;
use Magepattern\Component\Tool\FormTool;
use Magepattern\Component\Tool\MailTool;
use Magepattern\Component\Tool\SmartyTool;
use Magepattern\Component\Tool\StringTool;
use Magepattern\Component\HTTP\Request;

class FrontendController extends BaseController
{
    public function run(): void
    {
        // 🟢 On utilise bien 'front' (le nom officiel de l'instance du CMS)
        SmartyTool::addTemplateDir('front', ROOT_DIR . 'plugins' . DS . 'Contact' . DS . 'views' . DS . 'front');

        $action = $_GET['action'] ?? 'index';

        if ($action === 'send' && Request::isMethod('POST')) {
            $this->processSend();
            return;
        }

        $this->index();
    }

    private function index(): void
    {
        $db = new ContactFrontDb();
        $idLang = (int)$this->currentLang['id_lang'];

        $pageData = $db->getPageContent($idLang);

        if (empty($pageData)) {
            $pageData = [
                'name_page'    => 'Contact',
                'resume_page'  => '',
                'content_page' => ''
            ];
            $seoTitle = 'Contactez-nous';
            $seoDesc  = 'Formulaire de contact';
        } else {
            $seoTitle = !empty($pageData['seo_title_page']) ? $pageData['seo_title_page'] : ($pageData['name_page'] ?? 'Contact');
            $seoDesc  = $pageData['seo_desc_page'] ?? '';
        }

        $activeContacts = $db->getActiveContacts($idLang);

        $this->view->assign([
            'seo_title'        => $seoTitle,
            'seo_desc'         => $seoDesc,
            'page'             => $pageData,
            'contact_services' => $activeContacts
        ]);

        $this->view->display('index.tpl');
    }

    private function processSend(): void
    {
        if (ob_get_length()) ob_clean();

        $msg = FormTool::arrayClean($_POST['msg'] ?? [], 'content');

        $requiredFields = ['firstname', 'lastname', 'email', 'content', 'rgpd'];
        foreach ($requiredFields as $field) {
            if (empty($msg[$field])) {
                $this->jsonResponse(false, 'Veuillez remplir tous les champs obligatoires.');
            }
        }

        if (!StringTool::isMail((string)$msg['email'])) {
            $this->jsonResponse(false, 'L\'adresse e-mail fournie est invalide.');
        }

        // =================================================================
        // 🟢 INTÉGRATION GOOGLE RECAPTCHA (Couplage faible)
        // =================================================================
        $isHuman = true;

        if (class_exists('\Plugins\GoogleRecaptcha\src\FrontendController')) {
            $recaptcha = new \Plugins\GoogleRecaptcha\src\FrontendController();
            // On vérifie le jeton spécifiquement pour le module 'contact'
            $isHuman = $recaptcha->verify('contact');
        }

        if (!$isHuman) {
            $this->jsonResponse(false, 'Erreur de sécurité : Validation reCAPTCHA échouée. Veuillez réessayer.');
        }
        // =================================================================

        // Si on arrive ici, c'est un humain valide, on continue le script !
        $idContact = (int)($msg['id_contact'] ?? 0);
        $db = new ContactFrontDb();

        $recipientEmail = $db->getContactEmail($idContact);

        if (empty($recipientEmail)) {
            $this->jsonResponse(false, 'Le service sélectionné n\'est pas disponible.');
        }

        // Configuration mail depuis les Settings globaux du site
        $isSmtp = isset($this->siteSettings['smtp_enabled']['value']) && $this->siteSettings['smtp_enabled']['value'] == '1';
        $type = $isSmtp ? 'smtp' : 'mail';

        $options = [
            'setHost'       => $this->siteSettings['set_host']['value'] ?? '',
            'setPort'       => (int)($this->siteSettings['set_port']['value'] ?? 25),
            'setEncryption' => $this->siteSettings['set_encryption']['value'] ?? '',
            'setUsername'   => $this->siteSettings['set_username']['value'] ?? '',
            'setPassword'   => $this->siteSettings['set_password']['value'] ?? '',
        ];

        $mailer = new MailTool($type, $options);
        $msg['content'] = nl2br((string)$msg['content']);
        $subject = $msg['subject'] ?? 'Demande de contact';

        // L'expéditeur officiel du site
        $sender = !empty($this->siteSettings['mail_sender']['value']) ? $this->siteSettings['mail_sender']['value'] : (string)$msg['email'];

        $sent = $mailer->sendTemplate(
            'front', // 🟢 CORRECTION VITALE : 'front' au lieu de 'frontend' !
            'emails/message.tpl',
            $msg,
            "Nouveau message : " . $subject,
            $sender,
            [$recipientEmail => 'Service Web']
        );

        if ($sent) {
            $this->jsonResponse(true, 'Votre message a bien été envoyé. Nous vous répondrons dans les plus brefs délais !', ['type' => 'success']);
        } else {
            $this->jsonResponse(false, 'Une erreur technique est survenue lors de l\'envoi du message (Vérifiez la configuration SMTP).');
        }
    }
}