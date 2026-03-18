<?php
declare(strict_types=1);

namespace App\Install\Controller;

use Magepattern\Component\Tool\SmartyTool;
use Smarty\Smarty;

class IndexController
{
    protected Smarty $view;

    public function __construct()
    {
        // On charge l'instance Smarty dédiée à l'installation
        $this->view = SmartyTool::getInstance(BASEINSTALL);
    }

    public function run(): void
    {
        // 1. Définition des dossiers à vérifier (droits d'écriture)
        $folders = [
            'app/init/' => is_writable(APP_PATH . 'init'),
            'install/var/templates_c/' => is_writable(ROOT_DIR . BASEINSTALL . DS . 'var' . DS . 'templates_c')
        ];

        // 2. Vérification des extensions PHP requises
        $extensions = [
            'PDO MySQL' => extension_loaded('pdo_mysql'),
            'mbstring'  => extension_loaded('mbstring'),
            'cURL'      => extension_loaded('curl'),
            'GD ou Imagick' => extension_loaded('gd') || extension_loaded('imagick'),
            'JSON'      => extension_loaded('json')
        ];

        // 3. Vérification de la version de PHP (ex: 8.2 minimum)
        $phpVersionOk = version_compare(PHP_VERSION, '8.2.0', '>=');

        // 4. Déterminer si on peut passer à l'étape suivante
        $canContinue = $phpVersionOk && !in_array(false, $folders, true) && !in_array(false, $extensions, true);

        // Assignation à Smarty
        $this->view->assign([
            'step'          => 1,
            'php_version'   => PHP_VERSION,
            'php_ok'        => $phpVersionOk,
            'folders'       => $folders,
            'extensions'    => $extensions,
            'can_continue'  => $canContinue
        ]);

        $this->view->display('step1.tpl');
    }
}