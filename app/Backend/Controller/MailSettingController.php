<?php

declare(strict_types=1);

namespace App\Backend\Controller;

use App\Backend\Db\SettingDb;
use Magepattern\Component\HTTP\Request;
use Magepattern\Component\Tool\FormTool;
use Magepattern\Component\Tool\MailTool;
use Magepattern\Component\Security\CryptTool;

class MailSettingController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function run(): void
    {
        $action = $_GET['action'] ?? 'index';

        if ($action === 'test' && Request::isMethod('POST')) {
            $this->testSmtp();
            return;
        }

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

        // 🛡️ SÉCURITÉ : Masquage du mot de passe pour l'affichage (ne sera jamais envoyé au HTML en clair)
        if (!empty($settings['set_password']['value'])) {
            $settings['set_password']['value'] = '********';
        }

        $this->view->assign([
            'settings'  => $settings,
            'hashtoken' => $this->session->getToken()
        ]);

        $this->view->display('mail_setting/index.tpl');
    }

    private function processSave(): void
    {
        $token = $_POST['hashtoken'] ?? '';
        if (!$this->session->validateToken($token)) {
            $this->jsonResponse(false, 'Session expirée.');
        }

        $db = new SettingDb();
        $success = true;

        $postedSettings = $_POST['settings'] ?? [];

        if (!isset($postedSettings['smtp_enabled'])) {
            $postedSettings['smtp_enabled'] = '0';
        }

        foreach ($postedSettings as $name => $value) {

            // 🛡️ SÉCURITÉ : Interception du mot de passe
            if ($name === 'set_password') {
                if ($value === '********') {
                    // Si la valeur est '********', c'est que l'utilisateur ne l'a pas modifiée
                    continue;
                }

                if (!empty($value)) {
                    // Chiffrement avec Magepattern
                    $cleanValue = CryptTool::encrypt((string)$value);
                } else {
                    $cleanValue = ''; // On autorise le fait de vider le mot de passe
                }
            } else {
                $cleanValue = FormTool::simpleClean((string)$value);
            }

            if (!$db->updateSetting($name, $cleanValue)) {
                $success = false;
            }
        }

        if ($success) {
            $this->jsonResponse(true, 'Configuration e-mail enregistrée avec succès.', [
                'type' => 'update'
            ]);
        } else {
            $this->jsonResponse(false, 'Erreur lors de la sauvegarde.');
        }
    }

    private function testSmtp(): void
    {
        if (ob_get_length()) ob_clean();

        $emailTo = $_POST['test_email'] ?? '';
        if (!filter_var($emailTo, FILTER_VALIDATE_EMAIL)) {
            $this->jsonResponse(false, 'Veuillez saisir une adresse e-mail de réception valide.');
        }

        $settings = $_POST['settings'] ?? [];

        $isSmtp = isset($settings['smtp_enabled']) && $settings['smtp_enabled'] == '1';
        $type = $isSmtp ? 'smtp' : 'mail';

        // 🛡️ SÉCURITÉ : Récupération et Déchiffrement du mot de passe
        $password = $settings['set_password'] ?? '';

        // Si le champ reçu contient '********' ou est vide, on va chercher la vraie valeur en BDD
        if ($password === '********' || empty($password)) {
            $db = new SettingDb();
            $dbSettings = $db->fetchAllSettings();
            $encryptedPassword = $dbSettings['set_password']['value'] ?? '';

            // Déchiffrement avec Magepattern
            $password = CryptTool::decrypt($encryptedPassword);
        }

        $options = [
            'setHost'       => $settings['set_host'] ?? '',
            'setPort'       => (int)($settings['set_port'] ?? 25),
            'setEncryption' => $settings['set_encryption'] ?? '',
            'setUsername'   => $settings['set_username'] ?? '',
            'setPassword'   => $password,
        ];

        $sender = !empty($settings['mail_sender']) ? $settings['mail_sender'] : 'test@magixcms.com';

        try {
            $mailTool = new MailTool($type, $options);

            $htmlBody = '<h2>Test de connexion réussi !</h2>
                         <p>Si vous lisez ceci, c\'est que MagixCMS arrive bien à communiquer avec votre serveur d\'envoi.</p>';

            $email = $mailTool->createMessage(
                'Test SMTP - MagixCMS',
                $sender,
                $sender,
                [$emailTo => 'Administrateur'],
                $htmlBody
            );

            if ($mailTool->send($email)) {
                $this->jsonResponse(true, 'E-mail envoyé avec succès ! Vérifiez votre boîte de réception.');
            } else {
                $this->jsonResponse(false, 'L\'envoi a échoué. Consultez les logs d\'erreurs.');
            }
        } catch (\Exception $e) {
            $this->jsonResponse(false, 'Erreur technique SMTP : ' . $e->getMessage());
        }
    }
}