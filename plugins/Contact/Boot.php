<?php

declare(strict_types=1);

namespace Plugins\Contact;

use App\Component\Hook\HookManager;
use Magepattern\Component\Tool\SmartyTool;

class Boot
{
    public function register(): void
    {
        // 1. Accroche pour le Footer
        HookManager::register('displayFooter', 'Contact', [$this, 'hookDisplayFooter']);

        // 2. Accroche pour la Colonne de gauche (si le client l'y déplace)
        HookManager::register('displayLeftColumn', 'Contact', [$this, 'hookDisplayLeftColumn']);
    }

    public function hookDisplayFooter(): string
    {
        $view = SmartyTool::getInstance('front');
        return $view->fetch(ROOT_DIR . 'plugins/Contact/views/front/hooks/footer_company.tpl');
    }

    public function hookDisplayLeftColumn(): string
    {
        $view = SmartyTool::getInstance('front');
        // Ici on appelle un TPL différent, adapté à une colonne étroite !
        return $view->fetch(ROOT_DIR . 'plugins/Contact/views/front/hooks/left_company.tpl');
    }
}