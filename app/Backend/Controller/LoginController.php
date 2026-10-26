<?php

namespace App\Backend\Controller;

use App\Backend\Db\LoginDb;
use Magepattern\Component\HTTP\Request;
use Magepattern\Component\Tool\FormTool;
use Magepattern\Component\Tool\StringTool;
use Magepattern\Component\Tool\MailTool;

class LoginController extends BaseController
{
    protected bool $requireAuth = false;

    public function __construct() {
        parent::__construct();

        if ($this->session->get('id_admin') && !isset($_GET['action'])) {
            header('Location: index.php?controller=Dashboard');
            exit;
        }
    }

    public function run(): void
    {
        if (Request::isGet('action') && $_GET['action'] === 'logout') {
            $this->logout();
            return;
        }

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

    private function processLogin(): void
    {
        $email     = Request::isPost('email_admin') ? FormTool::simpleClean($_POST['email_admin']) : '';
        $password  = Request::isPost('passwd_admin') ? FormTool::simpleClean($_POST['passwd_admin']) : '';
        $csrfToken = Request::isPost('hashtoken') ? FormTool::simpleClean($_POST['hashtoken']) : '';

        if (!$this->session->validateToken($csrfToken)) {
            $this->view->assign('error', 'Session expirée ou jeton de sécurité invalide.');
            $this->showForm();
            return;
        }

        $loginDb = new LoginDb();
        $adminData = $loginDb->getAdminCredentials($email);

        if ($adminData !== null) {

            // 1. 🛡️ VERROUILLAGE : Vérification de la date de déblocage
            if (!empty($adminData['locked_until']) && strtotime($adminData['locked_until']) > time()) {
                $remaining = (int)ceil((strtotime($adminData['locked_until']) - time()) / 60);
                $this->view->assign('error', "Compte temporairement bloqué suite à de multiples échecs. Réessayez dans {$remaining} minute(s).");
                $this->showForm();
                return;
            }

            // 2. 🛡️ AUTHENTIFICATION : Vérification du mot de passe
            if ($adminData['active_admin'] == 1 && password_verify($password, $adminData['passwd_admin'])) {
                // Succès : on réinitialise le compteur d'échecs
                $loginDb->resetFailedAttempts($email);

                $this->session->regenerate();
                $this->session->set('keyuniqid_admin', $adminData['keyuniqid_admin']);
                $this->session->set('id_admin', $adminData['id_admin']);
                $this->session->set('email_admin', $adminData['email_admin']);

                header('Location: index.php?controller=Dashboard');
                exit;
            } else {
                // Échec : on incrémente le compteur
                $attempts = $loginDb->recordFailedAttempt($email);

                if ($attempts >= 5) {
                    $this->view->assign('error', "Compte bloqué pour 15 minutes suite à 5 tentatives échouées.");
                } else {
                    $this->view->assign('error', 'Email ou mot de passe incorrect.');
                }
                $this->showForm();
                return;
            }
        }

        // 3. ANTI-ÉNUMÉRATION : On ralentit la réponse si l'email n'existe pas
        // (Empêche un attaquant de deviner quels e-mails existent en mesurant le temps de réponse)
        usleep(400000);

        $this->view->assign('error', 'Email ou mot de passe incorrect.');
        $this->showForm();
    }

    private function showForm(): void
    {
        $this->view->assign('hashtoken', $this->session->getToken());
        $this->view->display('login/login.tpl');
    }

    private function logout(): void
    {
        $this->session->destroy();
        header('Location: index.php?controller=Login');
        exit;
    }

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

            // 🛡️ SÉCURITÉ : Ne pas permettre la récupération si le compte est bloqué pour abus
            $adminData = $loginDb->getAdminCredentials($email);

            if ($adminData !== null && !empty($adminData['locked_until']) && strtotime($adminData['locked_until']) > time()) {
                $this->view->assign('error', 'Action impossible. Ce compte est temporairement verrouillé.');
                $this->showForm();
                return;
            }

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