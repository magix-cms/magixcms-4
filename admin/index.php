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

// Enregistrement des Namespaces (Avec les Majuscules et le DS final obligatoires)
$autoloader->addNamespace('App\\Backend\\', APP_PATH . 'Backend' . DS);
$autoloader->addNamespace('App\\Component\\', APP_PATH . 'Component' . DS);

// 4. Configuration de Smarty (Vue)
SmartyTool::registerContext('admin', [
    'template_dir' => ROOT_DIR . 'admin' . DS . 'templates',
    'compile_dir'  => ROOT_DIR . 'admin' . DS . 'var' . DS . 'templates_c',
    'cache_dir'    => ROOT_DIR . 'admin' . DS . 'var' . DS . 'tpl_caches',
    'plugins_dir'  => ROOT_DIR . 'admin' . DS . 'templates' . DS . 'widgets',
    'config_dir'   => ROOT_DIR . 'admin' . DS . 'templates' . DS . 'i18n',
]);

// 5. Logique de Routage (Le "Front Controller")
// Par défaut, on pointe vers le Dashboard
$requestedController = $_GET['controller'] ?? 'Dashboard';
$actionName = $_GET['action'] ?? 'run';
// Formatage strict pour sécuriser l'URL (ex: "?controller=page" devient "Page")
$cleanName = ucfirst(strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $requestedController)));

// 6. Construction du nom complet de la classe à appeler
$fullClassName = "App\\Backend\\Controller\\" . $cleanName . "Controller";

// 7. Exécution
try {
    if (class_exists($fullClassName)) {
        // L'autoloader trouve le fichier, on instancie la classe
        // Dès l'instanciation, le BaseController prend le relais pour vérifier la session et les droits !
        $app = new $fullClassName();

        // On lance la méthode d'entrée
        $app->run();
    } else {
        throw new \Exception("Le module ou contrôleur '{$cleanName}' est introuvable.");
    }
} catch (\Exception $e) {
    // Gestion propre des erreurs
    header("HTTP/1.0 404 Not Found");
    echo "<div style='font-family: sans-serif; padding: 20px; border: 1px solid red; background: #fee; color: red;'>";
    echo "<h3>Erreur Magix CMS 4</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}