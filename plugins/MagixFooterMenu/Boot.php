<?php

declare(strict_types=1);

namespace Plugins\MagixFooterMenu;

use App\Component\Hook\HookManager;
use Magepattern\Component\Tool\SmartyTool;

class Boot
{
    public function register(): void
    {
        HookManager::register(
            'displayFooter',
            'MagixFooterMenu',
            [$this, 'hookDisplayFooter']
        );
    }

    public function hookDisplayFooter(): string
    {
        $view = SmartyTool::getInstance('front');
        return $view->fetch(ROOT_DIR . 'plugins/MagixFooterMenu/views/front/widget.tpl');
    }
}