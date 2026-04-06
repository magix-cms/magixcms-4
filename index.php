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

// 🟢 4. INTERCEPTION DES REDIRECTIONS SEO (À placer ici !)
// On le fait avant même de configurer Smarty ou de lire les contrôleurs.
$redirectTool = new \App\Component\Routing\RedirectTool();
$redirectTool->checkAndRedirect();

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
        // CAS 1 : C'est un contrôleur natif du Front-end
        $app = new $coreClassName();
        $app->run();

    } elseif (class_exists($pluginClassName)) {
        // CAS 2 : C'est un Plugin qui possède une partie publique
        $pluginRootDir = ROOT_DIR . 'plugins' . DS . $cleanName;

        if (!is_dir($pluginRootDir)) {
            // Si le dossier n'existe pas, c'est une 404, on passe à l'ErrorController
            $app = new \App\Frontend\Controller\ErrorController();
            $app->run();
            exit;
        }

        $app = new $pluginClassName();

        if (!method_exists($app, 'run')) {
            throw new \Exception("Le contrôleur public du plugin '{$cleanName}' est invalide (méthode run manquante).");
        }

        $pluginViewDir = $pluginRootDir . DS . 'views' . DS . 'front';
        if (is_dir($pluginViewDir)) {
            SmartyTool::addTemplateDir('front', $pluginViewDir);
        }

        $app->run();

    } else {
        // 🟢 CAS 3 : CONTRÔLEUR INTROUVABLE -> VRAIE ERREUR 404
        $app = new \App\Frontend\Controller\ErrorController();
        $app->run();
    }

} catch (\Throwable $e) {
    // 🔴 Le catch ne sert plus aux 404, mais aux ERREURS FATALES (500)
    header("HTTP/1.0 500 Internal Server Error");
    echo "<div style='font-family: sans-serif; padding: 20px; border: 1px solid #d32f2f; background: #ffebee; color: #b71c1c;'>";
    echo "<h3>Erreur 500 - Problème critique sur le site</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}