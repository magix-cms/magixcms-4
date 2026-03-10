<?php

declare(strict_types=1);

// 1. Définition des constantes principales
define('DS', DIRECTORY_SEPARATOR);
define('ROOT_DIR', dirname(__DIR__) . DS);
define('APP_PATH', ROOT_DIR . 'app' . DS);
define('BASEADMIN', 'admin');
define('MP_LOG_DIR',ROOT_DIR.BASEADMIN.DS.'var'.DS);
define('SQLCACHEADMIN', ROOT_DIR.BASEADMIN . DS);
define('EDITOR', '7.6.1');

/*define('MP_DBHOST' , 'localhost');
define('MP_DBNAME' , 'magixcms4');
define('MP_DBUSER' , 'root');
define('MP_DBPASSWORD' , 'root');
define('MP_DBDRIVER'  , 'mysql');
define('MP_LOG_DETAILS','full');*/

// 2. Inclusion de l'Autoloader
require_once APP_PATH . 'Autoloader.php';

use Magix\Autoloader;
use Magepattern\Component\Tool\SmartyTool;

// 3. Instanciation et enregistrement de l'autoloader
$autoloader = new Autoloader();
$autoloader->register();

// Enregistrement des Namespaces
$autoloader->addNamespace('App\\Backend\\', APP_PATH . 'Backend' . DS);
$autoloader->addNamespace('App\\Component\\', APP_PATH . 'Component' . DS);
$autoloader->addNamespace('Plugins\\', ROOT_DIR . 'plugins' . DS);

// 4. Configuration de Smarty (Vue)
SmartyTool::registerContext('admin', [
    'template_dir' => ROOT_DIR . 'admin' . DS . 'templates',
    'compile_dir'  => ROOT_DIR . 'admin' . DS . 'var' . DS . 'templates_c',
    'cache_dir'    => ROOT_DIR . 'admin' . DS . 'var' . DS . 'tpl_caches',
    'plugins_dir'  => ROOT_DIR . 'admin' . DS . 'templates' . DS . 'widgets',
    'config_dir'   => ROOT_DIR . 'admin' . DS . 'templates' . DS . 'i18n',
]);

// 5. Logique de Routage (Le "Front Controller")
$requestedController = $_GET['controller'] ?? 'Dashboard';
$actionName = $_GET['action'] ?? 'run';
//$cleanName = ucfirst(strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $requestedController)));
// On nettoie les caractères spéciaux mais on garde la casse d'origine
$cleanName = ucfirst(preg_replace('/[^a-zA-Z0-9]/', '', $requestedController));

// 6. Construction des noms de classes possibles
$coreClassName = "App\\Backend\\Controller\\" . $cleanName . "Controller";
$pluginClassName = "Plugins\\" . $cleanName . "\\src\\BackendController";

// 7. Exécution
try {
    if (class_exists($coreClassName)) {
        // CAS 1 : C'est un module natif du Core
        $app = new $coreClassName();
        $app->run();

    } elseif (class_exists($pluginClassName)) {
        // CAS 2 : C'est un Plugin Classique

        // SÉCURITÉ 1 : On vérifie que le dossier racine du plugin existe bien physiquement
        $pluginRootDir = ROOT_DIR . 'plugins' . DS . $cleanName;
        if (!is_dir($pluginRootDir)) {
            throw new \Exception("Le dossier du plugin '{$cleanName}' a été supprimé ou est introuvable.");
        }

        $app = new $pluginClassName();

        // SÉCURITÉ 2 : On s'assure que le contrôleur possède bien son point d'entrée
        if (!method_exists($app, 'run')) {
            throw new \Exception("Le contrôleur du plugin '{$cleanName}' est invalide (méthode run manquante).");
        }

        // On définit le chemin des vues du plugin
        $pluginViewDir = $pluginRootDir . DS . 'views' . DS . 'admin';

        // Si le dossier des vues existe, on l'ajoute à Smarty
        if (is_dir($pluginViewDir)) {
            SmartyTool::addTemplateDir('admin', $pluginViewDir);
        }

        // Lancement du plugin
        $app->run();

    } else {
        throw new \Exception("Le module ou plugin '{$cleanName}' est introuvable.");
    }
} catch (\Exception $e) {
    // Gestion propre des erreurs
    header("HTTP/1.0 404 Not Found");
    echo "<div style='font-family: sans-serif; padding: 20px; border: 1px solid red; background: #fee; color: red;'>";
    echo "<h3>Erreur Magix CMS 4</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}