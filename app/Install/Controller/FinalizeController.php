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
        $lockFile = ROOT_DIR . BASEINSTALL . DS . 'install.lock';
        if (file_exists($lockFile)) {
            header('HTTP/1.1 403 Forbidden');
            die('Opération interdite : L\'installation est verrouillée.');
        }

        $this->view = SmartyTool::getInstance(BASEINSTALL);

        if (!Request::isMethod('POST')) {
            header('Location: index.php?step=3');
            exit;
        }
    }

    public function run(): void
    {
        $siteName  = Request::isPost('site_name') ? FormTool::simpleClean($_POST['site_name']) : '';
        $firstName = Request::isPost('admin_firstname') ? FormTool::simpleClean($_POST['admin_firstname']) : '';
        $lastName  = Request::isPost('admin_lastname') ? FormTool::simpleClean($_POST['admin_lastname']) : '';
        $email     = Request::isPost('admin_email') ? FormTool::simpleClean($_POST['admin_email']) : '';
        $password  = Request::isPost('admin_password') ? $_POST['admin_password'] : '';
        $urlDomain = Request::isPost('url_domain') ? FormTool::simpleClean($_POST['url_domain']) : '';

        $urlDomain = preg_replace('#^https?://#', '', $urlDomain);
        $urlDomain = rtrim($urlDomain, '/');

        try {
            if (!StringTool::isMail($email)) {
                throw new \Exception("Le format de l'adresse e-mail est invalide.");
            }

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

            // SÉCURITÉ 2 : ANTI-TAKEOVER avec QueryBuilder
            try {
                $qbCheck = new QueryBuilder();
                $qbCheck->select('COUNT(*)')->from('mc_admin_employee');

                $stmtCheck = $pdo->prepare($qbCheck->getSql());
                $stmtCheck->execute($qbCheck->getParams());

                if ($stmtCheck->fetchColumn() > 0) {
                    throw new \Exception("Une tentative de réinstallation a été bloquée : Un administrateur existe déjà dans cette base de données.");
                }
            } catch (\PDOException $e) {
                // Table inexistante
            }

            // Exécution du fichier SQL d'installation
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

            // Mise à jour du nom du site
            $qbInfo = new QueryBuilder();
            $qbInfo->update('mc_company_info', ['value_info' => $siteName])
                ->where('name_info = "name"');
            $db->update($qbInfo->getSql(), $qbInfo->getParams());

            // NETTOYAGE DES TABLES ADMIN avec QueryBuilder
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $keyuniqid = md5(uniqid((string)microtime(true), true));

            $qbDelRel = new QueryBuilder();
            $qbDelRel->delete('mc_admin_access_rel');

            // Layer devrait avoir une méthode delete(). Si ce n'est pas le cas,
            // vous pouvez utiliser $pdo->prepare()->execute() comme pour le SELECT.
            $stmtDelRel = $pdo->prepare($qbDelRel->getSql());
            $stmtDelRel->execute($qbDelRel->getParams());

            $qbDelEmp = new QueryBuilder();
            $qbDelEmp->delete('mc_admin_employee');
            $stmtDelEmp = $pdo->prepare($qbDelEmp->getSql());
            $stmtDelEmp->execute($qbDelEmp->getParams());

            // Insertion de l'utilisateur
            $qbAdmin = new QueryBuilder();
            $qbAdmin->insert('mc_admin_employee', [
                'id_admin'        => 1,
                'keyuniqid_admin' => $keyuniqid,
                'title_admin'     => 'm',
                'firstname_admin' => $firstName,
                'lastname_admin'  => $lastName,
                'email_admin'     => $email,
                'passwd_admin'    => $hash,
                'active_admin'    => 1
            ]);
            $db->insert($qbAdmin->getSql(), $qbAdmin->getParams());

            // Attribution du rôle
            $qbRole = new QueryBuilder();
            $qbRole->insert('mc_admin_access_rel', [
                'id_admin' => 1,
                'id_role'  => 1
            ]);
            $db->insert($qbRole->getSql(), $qbRole->getParams());

            // NETTOYAGE ET CRÉATION DU DOMAINE PRINCIPAL avec QueryBuilder
            $qbDelDomain = new QueryBuilder();
            $qbDelDomain->delete('mc_domain');
            $stmtDelDomain = $pdo->prepare($qbDelDomain->getSql());
            $stmtDelDomain->execute($qbDelDomain->getParams());

            $qbDomain = new QueryBuilder();
            $qbDomain->insert('mc_domain', [
                'id_domain'        => 1,
                'url_domain'       => $urlDomain,
                'default_domain'   => 1,
                'canonical_domain' => 1
            ]);
            $db->insert($qbDomain->getSql(), $qbDomain->getParams());

            // Génération du verrou
            $lockFile = ROOT_DIR . BASEINSTALL . DS . 'install.lock';
            file_put_contents($lockFile, date('Y-m-d H:i:s') . ' - Magix CMS 4 installé avec succès par ' . $email);

            // ==========================================================
            // 🟢 LE NETTOYAGE POST-INSTALLATION (PURGE NUCLÉAIRE)
            // On efface les caches SQL et Smarty générés pendant l'installation.
            // Le premier visiteur ou l'administrateur aura ainsi un cache 100% frais.
            // ==========================================================
            if (class_exists('\App\Component\Cache\CacheManager')) {
                \App\Component\Cache\CacheManager::clearFrontend();
            }
            
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