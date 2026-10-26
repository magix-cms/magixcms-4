<?php
// FIX : Désactiver l'affichage des erreurs HTML pour ne pas corrompre le JSON d'elFinder
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

// FIX : On sauvegarde l'ID AVANT de charger le CMS (car le framework détruit $_SESSION)
$idAdmin = (int)$_SESSION['id_admin'];
session_write_close();

// =========================================================================
// 2.1 FIX BFLA : Vérification stricte du rôle et des permissions
// =========================================================================
define('DS', DIRECTORY_SEPARATOR);
if (!defined('ROOT_DIR')) {
    define('ROOT_DIR', dirname(__FILE__, 7) . DS);
}
if (!defined('APP_PATH')) {
    define('APP_PATH', ROOT_DIR . 'app' . DS);
}

require_once APP_PATH . 'init' . DS . 'config.php';
require_once APP_PATH . 'Autoloader.php';

$autoloader = new \Magix\Autoloader();
$autoloader->register();
$autoloader->addNamespace('App\\Backend\\', APP_PATH . 'Backend' . DS);

if (file_exists(ROOT_DIR . 'lib' . DS . 'magepattern' . DS . 'Bootstrap.php')) {
    require_once ROOT_DIR . 'lib' . DS . 'magepattern' . DS . 'Bootstrap.php';
}

try {
    $employeeDb = new \App\Backend\Db\EmployeeDb();
    $currentUser = $employeeDb->fetchEmployeeById($idAdmin);

    if (!$currentUser || (int)$currentUser['active_admin'] !== 1) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Accès refusé : Compte inactif ou introuvable.']);
        exit;
    }

    $idRole = (int)$currentUser['id_role'];

    if ($idRole !== 1) {
        $allPerms = $employeeDb->getAllModuleAccess($idAdmin);
        $hasWriteAccess = false;

        if ($allPerms) {
            foreach ($allPerms as $perm) {
                if ((int)$perm['append'] === 1 || (int)$perm['edit'] === 1) {
                    $hasWriteAccess = true;
                    break;
                }
            }
        }

        if (!$hasWriteAccess) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Accès refusé : Vous n\'avez pas les droits d\'édition.']);
            exit;
        }
    }
} catch (\Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Erreur de vérification des droits d\'accès.']);
    exit;
}

// =========================================================================
// 🛡️ NOUVEAU FIX XSS-2 : Fonction de nettoyage (Sanitization) des fichiers SVG
// =========================================================================
function sanitize_svg_on_upload($cmd, $result, $args, $elfinder) {
    // Si la commande n'est pas un upload ou qu'aucun fichier n'a été ajouté, on ignore
    if ($cmd !== 'upload' || empty($result['added'])) {
        return;
    }

    foreach ($result['added'] as $file) {
        // On ne traite que les fichiers identifiés comme SVG
        if ($file['mime'] === 'image/svg+xml') {
            $volume = $elfinder->getVolume($file['hash']);
            $filePath = $volume->getPath($file['hash']);

            if (file_exists($filePath)) {
                $svgContent = file_get_contents($filePath);

                // 1. Suppression stricte des balises <script>
                $svgContent = preg_replace('/<script[\s\S]*?<\/script>/i', '', $svgContent);

                // 2. Suppression de tous les attributs d'événements javascript (onmouseover, onload, onclick...)
                $svgContent = preg_replace('/(?<=\s)on[a-z]+\s*=\s*(["\']).*?\1/i', '', $svgContent);

                // 3. Suppression des URIs "javascript:" (ex: <a href="javascript:alert(1)">)
                $svgContent = preg_replace('/href\s*=\s*(["\'])\s*javascript:.*?\1/i', '', $svgContent);

                // 4. Suppression des balises <foreignObject> (peut contenir du HTML/JS encapsulé)
                $svgContent = preg_replace('/<foreignObject[\s\S]*?<\/foreignObject>/i', '', $svgContent);

                // On sauvegarde le SVG purifié
                file_put_contents($filePath, $svgContent);
            }
        }
    }
}

// 3. Configuration d'elFinder
$rootPath = dirname(__FILE__, 7) . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR;
$scriptPath = $_SERVER['SCRIPT_NAME'];
$webRoot = '';

if (($pos = strpos($scriptPath, '/admin/')) !== false) {
    $webRoot = substr($scriptPath, 0, $pos);
}
$portableUrl = $webRoot . '/media/';

require './autoload.php';

$opts = array(
    // 🛡️ FIX XSS-2 : On bind notre fonction de nettoyage à l'événement 'upload.presave'
    'bind' => array(
        'upload' => 'sanitize_svg_on_upload'
    ),
    'roots' => array(
        array(
            'driver'        => 'LocalFileSystem',
            'path'          => $rootPath,
            'URL'           => $portableUrl,
            'mimeDetect'    => 'internal',
            'uploadDeny'    => array('all'),
            // Le SVG est toujours autorisé, mais maintenant il est nettoyé de force
            'uploadAllow'   => array('image', 'application/pdf', 'image/svg+xml'),
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