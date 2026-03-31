<?php
declare(strict_types=1);

namespace App\Install\Controller;

use Magepattern\Component\Tool\SmartyTool;
use Magepattern\Component\Database\Layer;
use Magepattern\Component\Database\QueryBuilder; // Ajout du namespace
use Smarty\Smarty;

class SetupController
{
    protected Smarty $view;

    public function __construct()
    {
        $this->view = SmartyTool::getInstance(BASEINSTALL);

        // 1. Sécurité initiale
        if (!file_exists(APP_PATH . 'init' . DS . 'config.php')) {
            header("Location: index.php?step=2");
            exit;
        }

        try {
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

            if ($pdo) {
                // Vérification Anti-Takeover avec QueryBuilder
                $qbCheck = new QueryBuilder();
                $qbCheck->select('COUNT(*)')
                    ->from('mc_admin_employee');

                $stmtCheck = $pdo->prepare($qbCheck->getSql());
                $stmtCheck->execute($qbCheck->getParams());

                if ($stmtCheck->fetchColumn() > 0) {
                    header('HTTP/1.1 403 Forbidden');
                    die('<div style="font-family:sans-serif; text-align:center; padding:50px; color:#333; background-color:#f8f9fa; height:100vh;">
                            <h1 style="color:#d9534f; margin-bottom:10px;">Accès Refusé</h1>
                            <p>Magix CMS 4 est déjà entièrement configuré.</p>
                            <p>Veuillez supprimer le dossier <strong>/install/</strong> de votre serveur pour des raisons de sécurité.</p>
                         </div>');
                }
            }
        } catch (\Exception $e) {
            // Table non existante, l'installation peut continuer
        }
    }

    public function run(): void
    {
        $this->view->assign('step', 3);
        $this->view->display('step3.tpl');
    }
}