<?php
declare(strict_types=1);

namespace App\Install\Controller;

use Magepattern\Component\HTTP\Request;
use Magepattern\Component\Tool\SmartyTool;
use Magepattern\Component\Tool\FormTool;
use Magepattern\Component\Database\Layer;
use Smarty\Smarty;

class DatabaseController
{
    protected Smarty $view;

    public function __construct()
    {
        $this->view = SmartyTool::getInstance(BASEINSTALL);
    }

    public function run(): void
    {
        if (Request::isMethod('POST')) {
            // Nettoyage de l'action demandée
            $action = Request::isPost('action') ? FormTool::simpleClean($_POST['action']) : '';

            if ($action === 'test') {
                $this->testConnection();
                return;
            }
            if ($action === 'save') {
                $this->saveConfig();
                return;
            }
        }

        $this->view->assign('step', 2);
        $this->view->display('step2.tpl');
    }

    /**
     * Tente une connexion en utilisant la classe Layer de Magix CMS
     */
    private function testConnection(): void
    {
        // 1. Récupération et Nettoyage STRICT des inputs
        $host = Request::isPost('db_host') ? FormTool::simpleClean($_POST['db_host']) : 'localhost';
        $name = Request::isPost('db_name') ? FormTool::simpleClean($_POST['db_name']) : '';
        $user = Request::isPost('db_user') ? FormTool::simpleClean($_POST['db_user']) : '';

        // Sécurité : On ne nettoie PAS le mot de passe avec simpleClean pour ne pas altérer
        // les caractères spéciaux légitimes (&, <, >) qu'il pourrait contenir.
        $pass = Request::isPost('db_pass') ? $_POST['db_pass'] : '';

        try {
            // Définition temporaire des constantes pour que l'Adapter MySQL puisse les lire
            if (!defined('MP_DBDRIVER'))   define('MP_DBDRIVER', 'mysql');
            if (!defined('MP_DBHOST'))     define('MP_DBHOST', $host);
            if (!defined('MP_DBNAME'))     define('MP_DBNAME', $name);
            if (!defined('MP_DBUSER'))     define('MP_DBUSER', $user);
            if (!defined('MP_DBPASSWORD')) define('MP_DBPASSWORD', $pass);

            // On instancie le Layer
            $dbLayer = new Layer([
                'driver'   => 'mysql',
                'host'     => $host,
                'hostname' => $host,
                'dbname'   => $name,
                'username' => $user,
                'password' => $pass,
                'charset'  => 'utf8mb4'
            ]);

            $pdo = $dbLayer->connection();

            if ($pdo) {
                echo json_encode(['success' => true, 'message' => 'Connexion à la base de données réussie !']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Échec de connexion. Vérifiez vos identifiants ou consultez les logs de Magix.']);
            }
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'message' => 'Erreur critique : ' . $e->getMessage()]);
        }
        exit;
    }

    /**
     * Duplique le fichier .in, remplace les constantes et génère le fichier final
     */
    private function saveConfig(): void
    {
        // 1. Récupération et Nettoyage
        $host = Request::isPost('db_host') ? FormTool::simpleClean($_POST['db_host']) : 'localhost';
        $name = Request::isPost('db_name') ? FormTool::simpleClean($_POST['db_name']) : '';
        $user = Request::isPost('db_user') ? FormTool::simpleClean($_POST['db_user']) : '';
        $pass = Request::isPost('db_pass') ? $_POST['db_pass'] : '';

        $templatePath = APP_PATH . 'init' . DS . 'config.php.in';
        $configPath   = APP_PATH . 'init' . DS . 'config.php';

        if (!file_exists($templatePath)) {
            echo json_encode(['success' => false, 'message' => 'Le fichier modèle config.php.in est introuvable.']);
            exit;
        }

        $content = file_get_contents($templatePath);

        // 2. Protection des apostrophes et antislashs pour le fichier PHP
        // Si le mot de passe est "l'admin", il deviendra "l\'admin" dans le define() pour éviter de casser le PHP.
        $safeHost = addcslashes($host, "'\\");
        $safeUser = addcslashes($user, "'\\");
        $safePass = addcslashes($pass, "'\\");
        $safeName = addcslashes($name, "'\\");

        // 3. Remplacement sécurisé via expressions régulières (preg_replace_callback pour éviter les bugs avec les symboles $ dans le mot de passe)
        $content = preg_replace_callback("/define\('MP_DBHOST','.*?'\);/", fn() => "define('MP_DBHOST','{$safeHost}');", $content);
        $content = preg_replace_callback("/define\('MP_DBUSER','.*?'\);/", fn() => "define('MP_DBUSER','{$safeUser}');", $content);
        $content = preg_replace_callback("/define\('MP_DBPASSWORD','.*?'\);/", fn() => "define('MP_DBPASSWORD','{$safePass}');", $content);
        $content = preg_replace_callback("/define\('MP_DBNAME','.*?'\);/", fn() => "define('MP_DBNAME','{$safeName}');", $content);

        // 4. Écriture du fichier final
        if (file_put_contents($configPath, $content) !== false) {
            echo json_encode(['success' => true, 'message' => 'Configuration générée avec succès.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Impossible d\'écrire le fichier config.php.']);
        }
        exit;
    }
}