<?php

namespace App\Backend\Controller;

use App\Backend\Db\LoginDb;
use Magepattern\Component\HTTP\Request;
use Magepattern\Component\HTTP\Session;
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
        // INDISPENSABLE : Appeler le constructeur parent pour déclencher initTranslations()
        parent::__construct();

        // 2. Règle de redirection : Si déjà connecté et qu'on essaie de voir le login
        $session = new Session(false);
        if ($session->get('id_admin') && !isset($_GET['action'])) {
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
        // Si l'URL contient ?controller=Login&action=logout
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

        // 1. On récupère les données via l'e-mail uniquement
        $adminData = $loginDb->getAdminCredentials($email);

        // 2. On vérifie que l'utilisateur existe ET qu'il est actif
        if ($adminData !== null && $adminData['active_admin'] == 1) {

            // 3. On compare le mot de passe tapé avec le hash de la BDD
            if (password_verify($password, $adminData['passwd_admin'])) {

                // Par sécurité, on retire le hash du tableau avant de le retourner à la session
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

        // ATTENTION : Si tu testes en local sans HTTPS, passe 'false' au constructeur.
        // En production avec HTTPS, tu pourras laisser vide pour utiliser le 'true' par défaut.
        $session = new Session(false);

        // Vérification CSRF native de Magepattern 3
        if (!$session->validateToken($csrfToken)) {
            $this->view->assign('error', 'Session expirée ou jeton de sécurité invalide.');
            $this->showForm();
            return;
        }

        // Authentification via LoginDb
        $adminData = $this->authenticateAdmin($email, $password);

        if ($adminData !== null) {
            // Regénération native de l'ID (Anti-fixation de session)
            $session->regenerate();

            // Stockage des variables
            $session->set('keyuniqid_admin', $adminData['keyuniqid_admin']);
            $session->set('id_admin', $adminData['id_admin']);
            $session->set('email_admin', $adminData['email_admin']);

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
        $session = new Session(false);
        $this->view->assign('hashtoken', $session->getToken());

        // On rend ton template avec le fameux effet "Flip"
        $this->view->display('login/login.tpl');
    }

    /**
     * @return void
     */
    private function logout(): void
    {
        $session = new Session(false);

        // Ta classe gère la destruction complète (serveur + cookie navigateur)
        $session->destroy();

        // Redirection vers l'accueil/login
        header('Location: index.php?controller=Login');
        exit;
    }

    /**
     * @return void
     * @throws \Random\RandomException
     */
    private function processForgotPassword(): void
    {
        $session = new Session(false);

        // 1. Nettoyage strict des inputs via FormTool
        $email     = Request::isPost('email_forgot') ? FormTool::simpleClean($_POST['email_forgot']) : '';
        $csrfToken = Request::isPost('hashtoken_forgot') ? FormTool::simpleClean($_POST['hashtoken_forgot']) : '';

        // 2. Vérification CSRF
        if (!$session->validateToken($csrfToken)) {
            $this->view->assign('error', 'Session expirée. Veuillez réessayer.');
            $this->showForm();
            return;
        }

        // 3. Validation de l'e-mail via StringTool (Méthode Magepattern 3)
        if (!empty($email) && StringTool::isMail($email)) {

            $loginDb = new LoginDb();

            // On récupère la clé unique (string ou null)
            $adminKey = $loginDb->getAdminKeyByEmail($email);

            if ($adminKey !== null) {
                // 4. Génération du mot de passe temporaire
                $tempPassword = bin2hex(random_bytes(4));

                // 5. Mise à jour du champ change_passwd
                if ($loginDb->updateRecoveryPassword($email, $tempPassword)) {

                    // 6. Envoi de l'e-mail avec MailTool
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
                // Message de succès générique (Sécurité)
                $this->view->assign('success', 'Si cet e-mail existe, un message vous a été envoyé.');
            }
        } else {
            $this->view->assign('error', 'Format d\'e-mail invalide.');
        }

        $this->showForm();
    }
}