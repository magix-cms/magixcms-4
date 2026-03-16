<?php
declare(strict_types=1);

namespace Plugins\MagixFooterLogo;

use App\Component\Hook\HookManager;
use Magepattern\Component\Tool\SmartyTool;

class Boot
{
    public function register(): void
    {
        HookManager::register('displayFooter', 'MagixFooterLogo', [$this, 'hookDisplayFooter']);
    }

    public function hookDisplayFooter(): string
    {
        $view = SmartyTool::getInstance('front');
        return $view->fetch(ROOT_DIR . 'plugins/MagixFooterLogo/views/front/widget.tpl');
    }
}