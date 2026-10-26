<?php
//  FIX : Désactiver l'affichage des erreurs HTML pour ne pas corrompre le JSON d'elFinder
error_reporting(0);
ini_set('display_errors', '0');

// 1. Initialisation de la session MagixCMS
$sessionName = 'mp_sess_id';

if (isset($_COOKIE[$sessionName])) {
    session_name($sessionName);
    session_set_cookie_params(0, '/');
    @session_start();
} else {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Accès refusé.']);
    exit;
}

// 2. Vérification de base (est-il connecté ?)
if (!isset($_SESSION['id_admin'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Accès refusé : Session invalide.']);
    exit;
}

//  FIX : On sauvegarde l'ID AVANT de charger le CMS (car le framework détruit $_SESSION)
$idAdmin = (int)$_SESSION['id_admin'];

// OPTIMISATION : On ferme la session native pour éviter le verrouillage de session
// (Améliore la vitesse d'upload des gros fichiers)
session_write_close();

// =========================================================================
//  2.1 FIX BFLA : Vérification stricte du rôle et des permissions
// =========================================================================
define('DS', DIRECTORY_SEPARATOR);
if (!defined('ROOT_DIR')) {
    define('ROOT_DIR', dirname(__FILE__, 7) . DS);
}
if (!defined('APP_PATH')) {
    define('APP_PATH', ROOT_DIR . 'app' . DS);
}

// Chargement de l'environnement Magix CMS pour accéder à la BDD
require_once APP_PATH . 'init' . DS . 'config.php';
require_once APP_PATH . 'Autoloader.php';

$autoloader = new \Magix\Autoloader();
$autoloader->register();
$autoloader->addNamespace('App\\Backend\\', APP_PATH . 'Backend' . DS);

// Chargement de la librairie Magepattern (gestion BDD PDO)
if (file_exists(ROOT_DIR . 'lib' . DS . 'magepattern' . DS . 'Bootstrap.php')) {
    require_once ROOT_DIR . 'lib' . DS . 'magepattern' . DS . 'Bootstrap.php';
}

try {
    $employeeDb = new \App\Backend\Db\EmployeeDb();

    // On utilise notre variable $idAdmin sauvegardée plus haut
    $currentUser = $employeeDb->fetchEmployeeById($idAdmin);

    // 1. Sécurité de base : L'utilisateur doit exister ET être actif
    if (!$currentUser || (int)$currentUser['active_admin'] !== 1) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Accès refusé : Compte inactif ou introuvable.']);
        exit;
    }

    $idRole = (int)$currentUser['id_role'];

    // 2. Si ce n'est pas le Super-Admin, on vérifie s'il a des droits d'édition
    if ($idRole !== 1) {
        $allPerms = $employeeDb->getAllModuleAccess($idAdmin);
        $hasWriteAccess = false;

        if ($allPerms) {
            foreach ($allPerms as $perm) {
                // S'il a le droit d'ajouter (append) ou d'éditer (edit) sur n'importe quel module
                if ((int)$perm['append'] === 1 || (int)$perm['edit'] === 1) {
                    $hasWriteAccess = true;
                    break;
                }
            }
        }

        // Si l'utilisateur n'a le droit de rien modifier sur le site, on bloque elFinder
        if (!$hasWriteAccess) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Accès refusé : Vous n\'avez pas les droits d\'édition nécessaires pour gérer les médias.']);
            exit;
        }
    }
} catch (\Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Erreur de vérification des droits d\'accès.']);
    exit;
}
// =========================================================================

// 3. Chemin physique absolu sur le serveur
$rootPath = dirname(__FILE__, 7) . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR;

// Calcul du chemin Web relatif (Portable)
$scriptPath = $_SERVER['SCRIPT_NAME'];
$webRoot = '';

if (($pos = strpos($scriptPath, '/admin/')) !== false) {
    $webRoot = substr($scriptPath, 0, $pos);
}

$portableUrl = $webRoot . '/media/';

// 4. Chargement d'elFinder
require './autoload.php';

$opts = array(
    'roots' => array(
        array(
            'driver'        => 'LocalFileSystem',
            'path'          => $rootPath,
            'URL'           => $portableUrl,
            'mimeDetect'    => 'internal',
            'uploadDeny'    => array('all'),
            'uploadAllow'   => array('image', 'application/pdf'),
            'uploadOrder'   => array('deny', 'allow'),
            'uploadMaxSize' => '8M',
            'alias'         => 'Medias',
            'attributes' => array(
                array(
                    'pattern' => '/\/\./',
                    'read'    => false,
                    'write'   => false,
                    'hidden'  => true,
                    'locked'  => true
                ),
                array(
                    // Fix : Blocage des extensions multiples exécutables
                    'pattern' => '/\.(php[345678]?|phtml|pht|phps|phpt|pgif|inc|hphp|ctp|module|theme|engine|profile|phl|shtml|phar|tpl|js|cgi|py|sh|exe|asp|aspx|jsp)(?:\..*)?$/i',
                    'read'    => false,
                    'write'   => false,
                    'hidden'  => true,
                    'locked'  => true
                )
            )
        )
    )
);

$connector = new elFinderConnector(new elFinder($opts));
$connector->run();