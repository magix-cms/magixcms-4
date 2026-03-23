<?php

declare(strict_types=1);

// 1. Définition des constantes principales (Adaptées pour la racine)
define('DS', DIRECTORY_SEPARATOR);
define('ROOT_DIR', __DIR__ . DS); // <-- On est déjà à la racine ici
define('APP_PATH', ROOT_DIR . 'app' . DS);
define('MP_LOG_DIR', ROOT_DIR . 'var' . DS. 'log' .DS); // Exemple de dossier de log global
define('SQLCACHEDIR', ROOT_DIR . 'var' . DS);

$config = APP_PATH.'init'.DS.'config.php';
if (file_exists($config)) {
    require $config;
}else {
    header('Location: /install/');
}

// 2. Inclusion de l'Autoloader
require_once APP_PATH . 'Autoloader.php';

use Magix\Autoloader;
use Magepattern\Component\Tool\SmartyTool;

// 3. Instanciation et enregistrement de l'autoloader
$autoloader = new Autoloader();
$autoloader->register();

// Enregistrement des Namespaces pour le Front-end
$autoloader->addNamespace('App\\Frontend\\', APP_PATH . 'Frontend' . DS);
$autoloader->addNamespace('App\\Component\\', APP_PATH . 'Component' . DS);
$autoloader->addNamespace('Plugins\\', ROOT_DIR . 'plugins' . DS);

// 4. Configuration de Smarty (Vue Front-end)
// On utilise le contexte 'front' pour ne pas interférer avec 'admin'
SmartyTool::registerContext('front', [
    'template_dir' => ROOT_DIR . 'templates',          // Dossier de votre thème (public)
    'compile_dir'  => ROOT_DIR . 'var' . DS . 'templates_c',
    'cache_dir'    => ROOT_DIR . 'var' . DS . 'tpl_caches',
    'plugins_dir'  => ROOT_DIR . 'templates' . DS . 'widgets',
    'config_dir'   => ROOT_DIR . 'templates' . DS . 'i18n',
]);

// 5. Logique de Routage (Préparé pour le .htaccess)
// Si le .htaccess n'envoie pas de contrôleur, on charge 'Home' (l'accueil public)
$requestedController = $_GET['controller'] ?? 'Home';
$actionName = $_GET['action'] ?? 'run';
$cleanName = ucfirst(strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $requestedController)));

// 6. Construction des noms de classes possibles
$coreClassName = "App\\Frontend\\Controller\\" . $cleanName . "Controller";
// On cherche la partie publique du plugin
$pluginClassName = "Plugins\\" . $cleanName . "\\src\\FrontendController";


// 7. Exécution
try {
    if (class_exists($coreClassName)) {
        // CAS 1 : C'est un contrôleur natif du Front-end (ex: HomeController, NewsController)
        $app = new $coreClassName();
        $app->run();

    } elseif (class_exists($pluginClassName)) {
        // CAS 2 : C'est un Plugin qui possède une partie publique

        $pluginRootDir = ROOT_DIR . 'plugins' . DS . $cleanName;
        if (!is_dir($pluginRootDir)) {
            throw new \Exception("Le dossier du plugin '{$cleanName}' est introuvable.");
        }

        $app = new $pluginClassName();

        if (!method_exists($app, 'run')) {
            throw new \Exception("Le contrôleur public du plugin '{$cleanName}' est invalide (méthode run manquante).");
        }

        // On définit le chemin des vues publiques du plugin
        $pluginViewDir = $pluginRootDir . DS . 'views' . DS . 'front';

        // On injecte le dossier de vue du plugin dans le contexte 'front' de Smarty
        if (is_dir($pluginViewDir)) {
            SmartyTool::addTemplateDir('front', $pluginViewDir);
        }

        // Lancement du plugin
        $app->run();

    } else {
        // Page 404 si la classe n'existe pas
        // Idéalement, on instanciera ici un ErrorController pour afficher une belle vue 404
        throw new \Exception("Erreur 404 : La page '{$cleanName}' est introuvable.");
    }
} catch (\Exception $e) {
    // Affichage des erreurs (À masquer en production plus tard)
    header("HTTP/1.0 404 Not Found");
    echo "<div style='font-family: sans-serif; padding: 20px; border: 1px solid #ff9800; background: #fff3e0; color: #e65100;'>";
    echo "<h3>Magix CMS 4 - Erreur Front-end</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}