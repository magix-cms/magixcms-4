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
    // =================================================================
    //  1. LE MOTEUR DE HOOKS INTELLIGENT (Priorité B)
    // =================================================================
    public static function renderWidget(array $params = []): string
    {
        $hookName = $params['name'] ?? '';

        // Aiguillage A : WIDGET FOOTER (Pour les 3 colonnes)
        if (
            strtolower($hookName) === 'displayfooter' ||
            str_starts_with($hookName, 'displayFooterCol')
        ) {
            return self::renderFooterWidget($params);
        }

        // Aiguillage B : WIDGET COLONNE DE GAUCHE
        if ($hookName === 'displayLeftColumn') {
            return self::renderLeftColumnWidget($params);
        }

        return '';
    }

    public static function renderFooterWidget(array $params = []): string
    {
        $view = SmartyTool::getInstance('front');
        return $view->fetch(ROOT_DIR . 'plugins/Contact/views/front/hooks/footer_company.tpl');
    }

    public static function renderLeftColumnWidget(array $params = []): string
    {
        $view = SmartyTool::getInstance('front');
        return $view->fetch(ROOT_DIR . 'plugins/Contact/views/front/hooks/left_company.tpl');
    }

    // =================================================================
    //  2. LE CONTRÔLEUR CLASSIQUE DE LA PAGE DE CONTACT
    // =================================================================
    public function run(): void
    {
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
            $seoTitle = $this->view->getConfigVars('interested_form');
            $seoDesc  = $this->view->getConfigVars('contact_form');
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

        // 1. LE BOUCLIER HONEYPOT (Anti-bot basique)
        // Si le champ invisible est rempli, c'est un bot ! On simule un succès pour le tromper.
        if (!empty($_POST['website_url'])) {
            $this->jsonResponse(true, $this->view->getConfigVars('success_message_sent'), ['type' => 'success']);
        }

        // On nettoie les variables POST une seule fois
        $msg = FormTool::arrayClean($_POST['msg'] ?? [], 'content');

        // 2. LE BOUCLIER ANTI-LIENS (Anti-spam manuel/semi-auto)
        // Les spams contiennent presque toujours des liens (http, https, www).
        // Si le message contient plus de 3 liens, on bloque.
        $linkCount = substr_count(strtolower((string)$msg['content']), 'http') + substr_count(strtolower((string)$msg['content']), 'www.');
        if ($linkCount > 3) {
            $this->jsonResponse(false, 'Votre message ressemble à du spam (trop de liens).');
        }

        // 3. VÉRIFICATION DES CHAMPS REQUIS
        $requiredFields = ['firstname', 'lastname', 'email', 'content', 'rgpd'];
        foreach ($requiredFields as $field) {
            if (empty($msg[$field])) {
                $this->jsonResponse(false, $this->view->getConfigVars('error_empty_fields'));
            }
        }

        if (!StringTool::isMail((string)$msg['email'])) {
            $this->jsonResponse(false, $this->view->getConfigVars('error_invalid_email'));
        }

        // 4. LE BOUCLIER GOOGLE RECAPTCHA
        $isHuman = true;
        if (class_exists('\Plugins\GoogleRecaptcha\src\FrontendController')) {
            $recaptcha = new \Plugins\GoogleRecaptcha\src\FrontendController();
            $isHuman = $recaptcha->verify('contact');
        }

        if (!$isHuman) {
            $this->jsonResponse(false, $this->view->getConfigVars('error_recaptcha_failed'));
        }

        // 5. PRÉPARATION DES DESTINATAIRES
        $idContact = (int)($msg['id_contact'] ?? 0);
        $db = new ContactFrontDb();
        $idLang = (int)$this->currentLang['id_lang'];

        $recipients = [];

        // Si un ID spécifique est demandé
        if ($idContact > 0) {
            $recipientEmail = $db->getContactEmail($idContact);
            if (!empty($recipientEmail)) {
                $recipients[$recipientEmail] = 'Service Web';
            }
        }

        // Si aucun ID n'est demandé (0) ou invalide, on charge toute la liste
        if (empty($recipients)) {
            $activeContacts = $db->getActiveContacts($idLang);
            if (!empty($activeContacts) && is_array($activeContacts)) {
                foreach ($activeContacts as $contact) {
                    $idC = isset($contact['id_contact']) ? (int)$contact['id_contact'] : 0;
                    if ($idC > 0) {
                        $email = $db->getContactEmail($idC);
                        if (!empty($email)) {
                            $name = !empty($contact['name_contact']) ? $contact['name_contact'] : 'Service Web';
                            $recipients[$email] = $name;
                        }
                    }
                }
            }
        }

        if (empty($recipients)) {
            $this->jsonResponse(false, $this->view->getConfigVars('error_no_contact_service'));
        }

        // 6. CONFIGURATION SMTP ET ENVOI
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

        $userSubject = !empty($msg['subject']) ? $msg['subject'] : '...';
        $fullName = trim(($msg['firstname'] ?? '') . ' ' . ($msg['lastname'] ?? ''));
        $prefix = $this->view->getConfigVars('email_contact_new_message');
        $connector = $this->view->getConfigVars('concact_of');

        $finalSubject = $prefix . " " . $connector . " " . $fullName . " : " . $userSubject;

        $senderEmail = !empty($this->siteSettings['mail_sender']['value'])
            ? $this->siteSettings['mail_sender']['value']
            : (string)$msg['email'];

        $fromHeader = '"' . str_replace('"', '', $fullName) . '" <' . $senderEmail . '>';

        // 7. ENVOI SOUS BOUCLIER ANTI-CRASH (Try/Catch)
        try {
            $sent = $mailer->sendTemplate(
                'front',
                'emails/message.tpl',
                $msg,
                $finalSubject,
                $fromHeader,
                $recipients,
                [],
                (string)$msg['email']
            );

            if ($sent) {
                $this->jsonResponse(true, $this->view->getConfigVars('success_message_sent'), ['type' => 'success']);
            } else {
                $this->jsonResponse(false, $this->view->getConfigVars('error_technical_smtp'));
            }

        } catch (\Throwable $e) {
            $this->jsonResponse(false, "Plantage SMTP : " . $e->getMessage());
        }
    }
}