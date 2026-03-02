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

// 3. Chemin absolu
$rootPath = dirname(__FILE__, 7) . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR;

// 4. Chargement d'elFinder
require './autoload.php';

$opts = array(
    // J'AI SUPPRIMÉ LES BLOCS "bind" ET "plugin" QUI FAISAIENT PLANTER L'UPLOAD
    'roots' => array(
        array(
            'driver'        => 'LocalFileSystem',
            'path'          => $rootPath,
            'URL'           => '/media/',
            'mimeDetect'    => 'internal', // Conserve la détection par extension
            'uploadDeny'    => array('all'),
            'uploadAllow'   => array('image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml', 'application/pdf'),
            'uploadOrder'   => array('deny', 'allow'),
            'uploadMaxSize' => '8M',
            'alias'         => 'Medias',
            'attributes' => array(
                // CORRECTION : Cache tous les fichiers cachés (comme .htaccess)
                array(
                    'pattern' => '/\/\./',
                    'read'    => false,
                    'write'   => false,
                    'hidden'  => true,
                    'locked'  => true
                ),
                // Sécurité sur les scripts
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