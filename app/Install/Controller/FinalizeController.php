<?php
declare(strict_types=1);

namespace App\Install\Controller;

use Magepattern\Component\HTTP\Request;
use Magepattern\Component\Tool\SmartyTool;
use Magepattern\Component\Tool\FormTool;
use Magepattern\Component\Tool\StringTool;
use Magepattern\Component\Database\Layer;
use Magepattern\Component\Database\QueryBuilder;
use Smarty\Smarty;

class FinalizeController
{
    protected Smarty $view;

    public function __construct()
    {
        $this->view = SmartyTool::getInstance(BASEINSTALL);

        // Sécurité : Interdiction d'accès direct par l'URL
        if (!Request::isMethod('POST')) {
            header('Location: index.php?step=3');
            exit;
        }
    }

    public function run(): void
    {
        // 1. Récupération et Nettoyage STRICT
        $siteName  = Request::isPost('site_name') ? FormTool::simpleClean($_POST['site_name']) : '';
        $firstName = Request::isPost('admin_firstname') ? FormTool::simpleClean($_POST['admin_firstname']) : '';
        $lastName  = Request::isPost('admin_lastname') ? FormTool::simpleClean($_POST['admin_lastname']) : '';
        $email     = Request::isPost('admin_email') ? FormTool::simpleClean($_POST['admin_email']) : '';
        $password  = Request::isPost('admin_password') ? $_POST['admin_password'] : '';
        $urlDomain = Request::isPost('url_domain') ? FormTool::simpleClean($_POST['url_domain']) : '';

        // Nettoyage de l'URL : on retire "http://", "https://" et le slash de fin s'il y en a un
        $urlDomain = preg_replace('#^https?://#', '', $urlDomain);
        $urlDomain = rtrim($urlDomain, '/');

        try {
            if (!StringTool::isMail($email)) {
                throw new \Exception("Le format de l'adresse e-mail est invalide.");
            }

            // 2. Instanciation du Layer AVEC les constantes générées
            $config = [
                'driver'   => defined('MP_DBDRIVER') ? MP_DBDRIVER : 'mysql',
                'hostname' => defined('MP_DBHOST') ? MP_DBHOST : 'localhost',
                'host'     => defined('MP_DBHOST') ? MP_DBHOST : 'localhost',
                'dbname'   => defined('MP_DBNAME') ? MP_DBNAME : '',
                'username' => defined('MP_DBUSER') ? MP_DBUSER : '',
                'password' => defined('MP_DBPASSWORD') ? MP_DBPASSWORD : '',
                'charset'  => 'utf8mb4'
            ];

            $db = new Layer($config);
            $pdo = $db->connection();

            if (!$pdo) {
                throw new \Exception("Impossible d'établir la connexion PDO. Le fichier config.php est mal généré.");
            }

            // 3. Exécution du fichier SQL d'installation
            $sqlFile = ROOT_DIR . BASEINSTALL . DS . 'sql' . DS . 'install.sql';
            if (!file_exists($sqlFile)) {
                throw new \Exception("Le fichier install.sql est introuvable dans le dossier : " . $sqlFile);
            }

            $sql = file_get_contents($sqlFile);
            $result = $pdo->exec($sql);

            if ($result === false) {
                $error = $pdo->errorInfo();
                if (isset($error[0]) && $error[0] !== '00000') {
                    throw new \Exception("Erreur SQL lors de l'import : " . ($error[2] ?? 'Erreur inconnue'));
                }
            }

            // 4. Mise à jour du nom du site
            $qbInfo = new QueryBuilder();
            $qbInfo->update('mc_company_info', ['value_info' => $siteName])
                ->where('name_info = "name"');
            $db->update($qbInfo->getSql(), $qbInfo->getParams());

            // =================================================================
            // 🟢 NOUVEAU : CRÉATION DU SUPER ADMIN ET ATTRIBUTION DU RÔLE
            // =================================================================

            $hash = password_hash($password, PASSWORD_DEFAULT);
            $keyuniqid = md5(uniqid((string)microtime(true), true));

            // Par précaution, on nettoie la table de liaison au cas où le fichier SQL
            // contiendrait un INSERT résiduel, pour éviter un doublon de clé primaire.
            $db->exec("DELETE FROM mc_admin_access_rel");
            $db->exec("DELETE FROM mc_admin_employee");

            // A) Insertion de l'utilisateur
            $qbAdmin = new QueryBuilder();
            $qbAdmin->insert('mc_admin_employee', [
                'id_admin'        => 1, // On force l'ID 1
                'keyuniqid_admin' => $keyuniqid,
                'title_admin'     => 'm',
                'firstname_admin' => $firstName,
                'lastname_admin'  => $lastName,
                'email_admin'     => $email,
                'passwd_admin'    => $hash,
                'active_admin'    => 1 // Le compte est actif immédiatement
            ]);
            $db->insert($qbAdmin->getSql(), $qbAdmin->getParams());

            // B) Attribution du rôle (id_admin = 1 -> id_role = 1)
            $qbRole = new QueryBuilder();
            $qbRole->insert('mc_admin_access_rel', [
                'id_admin' => 1,
                'id_role'  => 1
            ]);
            $db->insert($qbRole->getSql(), $qbRole->getParams());

            // =================================================================
            // 🟢 CRÉATION DU DOMAINE PRINCIPAL (SANS LIAISON LANGUE)
            // =================================================================

            // Nettoyage de la table des domaines pour partir sur une base saine
            $db->exec("DELETE FROM mc_domain");

            // Insertion du domaine racine
            $qbDomain = new QueryBuilder();
            $qbDomain->insert('mc_domain', [
                'id_domain'        => 1, // On force l'ID 1 pour la cohérence système
                'url_domain'       => $urlDomain,
                'default_domain'   => 1, // C'est le domaine par défaut du CMS
                'canonical_domain' => 1  // C'est l'URL de référence pour le SEO
            ]);

            $db->insert($qbDomain->getSql(), $qbDomain->getParams());
            // =================================================================

            // 6. Affichage du succès
            $this->view->assign([
                'step'      => 4,
                'site_name' => $siteName,
                'email'     => $email
            ]);

            $this->view->display('step4.tpl');

        } catch (\Throwable $e) {
            die("<div style='color:red; font-family:sans-serif; padding:20px; border:1px solid red; background:#ffebeb;'>
                    <strong>Erreur fatale lors de l'installation :</strong><br><br>" . $e->getMessage() . "
                 </div>");
        }
    }
}