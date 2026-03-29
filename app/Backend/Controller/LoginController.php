<?php

namespace App\Backend\Controller;

use App\Backend\Db\LoginDb;
use Magepattern\Component\HTTP\Request;
use Magepattern\Component\Tool\FormTool;
use Magepattern\Component\Tool\StringTool;
use Magepattern\Component\Tool\MailTool;

class LoginController extends BaseController
{
    // 1. On désactive le Guard pour ce contrôleur spécifique !
    /**
     * @var bool
     */
    protected bool $requireAuth = false;

    /**
     * Construct
     */
    public function __construct() {
        // INDISPENSABLE : Appeler le constructeur parent pour déclencher initTranslations(), initSettings() et initialiser $this->session avec le SSL
        parent::__construct();

        // 2. Règle de redirection : Si déjà connecté et qu'on essaie de voir le login
        if ($this->session->get('id_admin') && !isset($_GET['action'])) {
            header('Location: index.php?controller=Dashboard');
            exit;
        }
    }

    /**
     * @return void
     * @throws \Random\RandomException
     * @throws \Smarty\Exception
     */
    public function run(): void
    {
        // 1. Gestion du routage interne du contrôleur
        if (Request::isGet('action') && $_GET['action'] === 'logout') {
            $this->logout();
            return;
        }

        // 2. Traitement du formulaire
        if (Request::isMethod('POST')) {
            if (Request::isPost('email_forgot')) {
                $this->processForgotPassword();
            } else {
                $this->processLogin();
            }
        } else {
            $this->showForm();
        }
    }

    /**
     * @param string $email
     * @param string $password
     * @return array|null
     */
    private function authenticateAdmin(string $email, string $password): ?array
    {
        $loginDb = new LoginDb();

        $adminData = $loginDb->getAdminCredentials($email);

        if ($adminData !== null && $adminData['active_admin'] == 1) {
            if (password_verify($password, $adminData['passwd_admin'])) {
                unset($adminData['passwd_admin']);
                return $adminData;
            }
        }

        return null;
    }

    /**
     * @return void
     * @throws \Smarty\Exception
     */
    private function processLogin(): void
    {
        $email     = Request::isPost('email_admin') ? FormTool::simpleClean($_POST['email_admin']) : '';
        $password  = Request::isPost('passwd_admin') ? FormTool::simpleClean($_POST['passwd_admin']) : '';
        $csrfToken = Request::isPost('hashtoken') ? FormTool::simpleClean($_POST['hashtoken']) : '';

        // Vérification CSRF avec la session héritée du BaseController
        if (!$this->session->validateToken($csrfToken)) {
            $this->view->assign('error', 'Session expirée ou jeton de sécurité invalide.');
            $this->showForm();
            return;
        }

        $adminData = $this->authenticateAdmin($email, $password);

        if ($adminData !== null) {
            $this->session->regenerate();

            $this->session->set('keyuniqid_admin', $adminData['keyuniqid_admin']);
            $this->session->set('id_admin', $adminData['id_admin']);
            $this->session->set('email_admin', $adminData['email_admin']);

            header('Location: index.php?controller=Dashboard');
            exit;
        }

        $this->view->assign('error', 'Email ou mot de passe incorrect.');
        $this->showForm();
    }

    /**
     * @return void
     * @throws \Smarty\Exception
     */
    private function showForm(): void
    {
        // Utilisation de la session globale
        $this->view->assign('hashtoken', $this->session->getToken());
        $this->view->display('login/login.tpl');
    }

    /**
     * @return void
     */
    private function logout(): void
    {
        // Destruction de la session globale
        $this->session->destroy();

        header('Location: index.php?controller=Login');
        exit;
    }

    /**
     * @return void
     * @throws \Random\RandomException
     */
    private function processForgotPassword(): void
    {
        $email     = Request::isPost('email_forgot') ? FormTool::simpleClean($_POST['email_forgot']) : '';
        $csrfToken = Request::isPost('hashtoken_forgot') ? FormTool::simpleClean($_POST['hashtoken_forgot']) : '';

        if (!$this->session->validateToken($csrfToken)) {
            $this->view->assign('error', 'Session expirée. Veuillez réessayer.');
            $this->showForm();
            return;
        }

        if (!empty($email) && StringTool::isMail($email)) {
            $loginDb = new LoginDb();
            $adminKey = $loginDb->getAdminKeyByEmail($email);

            if ($adminKey !== null) {
                $tempPassword = bin2hex(random_bytes(4));

                if ($loginDb->updateRecoveryPassword($email, $tempPassword)) {
                    try {
                        $mailer = new MailTool('mail');

                        $subject = 'Récupération de votre mot de passe - Magix CMS';
                        $from    = 'noreply@magix-cms.com';
                        $recipients = [$email => 'Administrateur'];

                        $htmlBody  = "<p>Bonjour,</p>";
                        $htmlBody .= "<p>Voici votre mot de passe temporaire : <strong>{$tempPassword}</strong></p>";
                        $htmlBody .= "<p>Veuillez le modifier dès votre prochaine connexion.</p>";

                        $emailMessage = $mailer->createMessage($subject, $from, $from, $recipients, $htmlBody);

                        if ($mailer->send($emailMessage)) {
                            $this->view->assign('success', 'Un e-mail contenant votre nouveau mot de passe a été envoyé.');
                        } else {
                            $this->view->assign('error', 'Erreur lors de l\'envoi de l\'e-mail.');
                        }
                    } catch (\Exception $e) {
                        $this->logger->log("Erreur MailTool : " . $e->getMessage(), "error");
                        $this->view->assign('error', 'Erreur technique lors de l\'envoi de l\'e-mail.');
                    }
                } else {
                    $this->view->assign('error', 'Erreur lors de la mise à jour du mot de passe.');
                }
            } else {
                $this->view->assign('success', 'Si cet e-mail existe, un message vous a été envoyé.');
            }
        } else {
            $this->view->assign('error', 'Format d\'e-mail invalide.');
        }

        $this->showForm();
    }
}