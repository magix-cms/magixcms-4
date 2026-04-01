<?php
declare(strict_types=1);

namespace Plugins\MagixFooterLogo\src;

use Magepattern\Component\Tool\SmartyTool;

class FrontendController
{
    public static function renderWidget(array $params = []): string
    {
        $hookName = $params['name'] ?? '';

        // Si le hook appartient à l'une des 3 colonnes du footer
        if (str_starts_with($hookName, 'displayFooterCol')) {
            $view = SmartyTool::getInstance('front');
            return $view->fetch(ROOT_DIR . 'plugins/MagixFooterLogo/views/front/widget.tpl');
        }

        return '';
    }
}