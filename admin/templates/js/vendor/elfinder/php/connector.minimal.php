<?php
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

// 2. Vérification de l'administrateur
if (!isset($_SESSION['id_admin'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Accès refusé : Session invalide.']);
    exit;
}

// 3. Chemin physique absolu sur le serveur (Pour qu'elFinder puisse lire les fichiers)
$rootPath = dirname(__FILE__, 7) . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR;

// 🟢 LE FIX : Calcul du chemin Web relatif (Portable)
$scriptPath = $_SERVER['SCRIPT_NAME'];
$webRoot = '';

// On coupe la chaîne proprement juste avant le dossier "admin"
if (($pos = strpos($scriptPath, '/admin/')) !== false) {
    $webRoot = substr($scriptPath, 0, $pos);
}

// URL portable finale (ex: /magixcms4/media/ ou /media/)
$portableUrl = $webRoot . '/media/';

// 4. Chargement d'elFinder
require './autoload.php';

$opts = array(
    'roots' => array(
        array(
            'driver'        => 'LocalFileSystem',
            'path'          => $rootPath,
            'URL'           => $portableUrl, // 🟢 On injecte l'URL portable ici
            'mimeDetect'    => 'internal',
            'uploadDeny'    => array('all'),
            'uploadAllow'   => array('image', 'application/pdf'), // Fix pour le bug "grisé"
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
                    'pattern' => '/\.(php|phtml|html|js|cgi|py|sh|exe)$/i',
                    'read'    => false,
                    'write'   => false,
                    'hidden'  => true,
                    'locked'  => true
                )
            )
        )
    )
);

error_reporting(0);
ini_set('display_errors', 0);

$connector = new elFinderConnector(new elFinder($opts));
$connector->run();