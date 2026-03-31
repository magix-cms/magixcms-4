<?php
declare(strict_types=1);

// 1. Définition des constantes principales
define('DS', DIRECTORY_SEPARATOR);
define('ROOT_DIR', dirname(__DIR__) . DS);
define('APP_PATH', ROOT_DIR . 'app' . DS);
define('BASEINSTALL', 'install');
define('MP_LOG_DIR', ROOT_DIR . BASEINSTALL . DS . 'var' . DS);

// ========================================================================
// 🟢 SÉCURITÉ 1 : VERROUILLAGE GLOBAL PAR FICHIER LOCK
// Bloque instantanément toute tentative d'accès si l'installation est finie
// ========================================================================
$lockFile = ROOT_DIR . BASEINSTALL . DS . 'install.lock';

if (file_exists($lockFile)) {
    // Si c'est une attaque par requête AJAX (comme l'exploit de l'étape 2)
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

    if ($isAjax) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'L\'installation est déjà terminée et verrouillée.']);
        exit;
    }

    // Si c'est un accès direct dans le navigateur
    header('HTTP/1.1 403 Forbidden');
    die('<div style="font-family:sans-serif; text-align:center; padding:50px; color:#333; background-color:#f8f9fa; height:100vh;">
            <h1 style="color:#d9534f; margin-bottom:10px;">Accès Refusé</h1>
            <p>Magix CMS 4 est déjà installé et sécurisé.</p>
            <p>Veuillez supprimer le dossier <strong>/install/</strong> de votre serveur pour réactiver votre site.</p>
         </div>');
}

// Chargement de la config si elle existe (pour les étapes 3 et 4)
$config = APP_PATH . 'init' . DS . 'config.php';
if (file_exists($config)) {
    require $config;
}

// 2. Inclusion de l'Autoloader
require_once APP_PATH . 'Autoloader.php';

use Magix\Autoloader;
use Magepattern\Component\Tool\SmartyTool;

// 3. Instanciation et enregistrement de l'autoloader
$autoloader = new Autoloader();
$autoloader->register();

// Enregistrement des Namespaces
$autoloader->addNamespace('App\\Install\\', APP_PATH . 'Install' . DS);
$autoloader->addNamespace('App\\Component\\', APP_PATH . 'Component' . DS);

// 4. Configuration de Smarty (Vue)
SmartyTool::registerContext(BASEINSTALL, [
    'template_dir' => ROOT_DIR . BASEINSTALL . DS . 'templates',
    'compile_dir'  => ROOT_DIR . BASEINSTALL . DS . 'var' . DS . 'templates_c',
    'cache_dir'    => ROOT_DIR . BASEINSTALL . DS . 'var' . DS . 'tpl_caches',
    'plugins_dir'  => ROOT_DIR . BASEINSTALL . DS . 'templates' . DS . 'widgets',
    'config_dir'   => ROOT_DIR . BASEINSTALL . DS . 'templates' . DS . 'i18n',
]);

use App\Install\Controller\IndexController;
use App\Install\Controller\DatabaseController;
use App\Install\Controller\SetupController;
use App\Install\Controller\FinalizeController;

// 5. Routage basique de l'installation
$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;

// Si config.php existe, on interdit de refaire l'étape 1 ou 2
$configPath = APP_PATH . 'init' . DS . 'config.php';
if (file_exists($configPath) && in_array($step, [1, 2], true)) {
    header('HTTP/1.1 403 Forbidden');
    die('<div style="font-family:sans-serif; text-align:center; padding:50px;">
            <h1 style="color:#d9534f;">Installation déjà configurée</h1>
            <p>Le fichier config.php existe déjà. Veuillez supprimer le dossier /install/.</p>
         </div>');
}

try {
    switch ($step) {
        case 1:
            $controller = new IndexController();
            $controller->run();
            break;
        case 2:
            $controller = new DatabaseController();
            $controller->run();
            break;
        case 3:
            $controller = new SetupController();
            $controller->run();
            break;
        case 4:
            // 🟢 L'Étape finale que nous créons maintenant
            $controller = new FinalizeController();
            $controller->run();
            break;
        // etc...
        default:
            header("Location: index.php?step=1");
            exit;
    }
} catch (\Exception $e) {
    die("Erreur critique d'installation : " . $e->getMessage());
}

?>