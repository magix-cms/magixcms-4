<?php

declare(strict_types=1);

namespace Plugins\MagixSocial;

use App\Component\Hook\HookManager;
use Magepattern\Component\Tool\SmartyTool;

class Boot
{
    public function register(): void
    {
        // On déclare le widget pour le footer
        HookManager::register(
            'displayFooter',
            'MagixSocial',
            [$this, 'hookDisplayFooter']
        );
    }

    public function hookDisplayFooter(): string
    {
        $view = SmartyTool::getInstance('front');
        return $view->fetch(ROOT_DIR . 'plugins/MagixSocial/views/front/widget.tpl');
    }
}