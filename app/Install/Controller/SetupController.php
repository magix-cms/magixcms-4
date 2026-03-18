<?php
declare(strict_types=1);

namespace App\Install\Controller;

use Magepattern\Component\Tool\SmartyTool;
use Smarty\Smarty;

class SetupController
{
    protected Smarty $view;

    public function __construct()
    {
        $this->view = SmartyTool::getInstance(BASEINSTALL);

        // Sécurité : On vérifie que l'étape 2 a bien été passée
        if (!file_exists(APP_PATH . 'init' . DS . 'config.php')) {
            header("Location: index.php?step=2");
            exit;
        }
    }

    public function run(): void
    {
        $this->view->assign('step', 3);
        $this->view->display('step3.tpl');
    }
}