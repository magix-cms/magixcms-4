<?php
declare(strict_types=1);

namespace Plugins\MagixSocial\src;

use Magepattern\Component\Tool\SmartyTool;

class FrontendController
{
    /**
     * Méthode appelée automatiquement par le HookManager (Priorité B)
     */
    public static function renderWidget(array $params = []): string
    {
        $hookName = $params['name'] ?? '';

        // Aiguillage : Si le hook appartient à l'une des 3 colonnes du footer
        if (str_starts_with($hookName, 'displayFooterCol')) {
            $view = SmartyTool::getInstance('front');
            return $view->fetch(ROOT_DIR . 'plugins/MagixSocial/views/front/widget.tpl');
        }

        return '';
    }
}