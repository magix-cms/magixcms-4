<?php
declare(strict_types=1);

// 1. Définition des constantes principales
define('DS', DIRECTORY_SEPARATOR);
define('ROOT_DIR', dirname(__DIR__) . DS);
define('APP_PATH', ROOT_DIR . 'app' . DS);
define('BASEINSTALL', 'install');
define('MP_LOG_DIR',ROOT_DIR.BASEINSTALL.DS.'var'.DS);

$config = APP_PATH.'init'.DS.'config.php';
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