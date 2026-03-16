<?php
namespace Plugins\MagixLastNews;

use App\Component\Hook\HookManager;

class Boot
{
    public function register(): void
    {
        // 1. Accroche Home
        HookManager::register('displayHome', 'MagixLastNews',
            [\Plugins\MagixLastNews\src\FrontendController::class, 'renderWidget']
        );

        // 2. Accroche Footer (Appelle la nouvelle méthode statique)
        HookManager::register('displayFooter', 'MagixLastNews',
            [\Plugins\MagixLastNews\src\FrontendController::class, 'renderFooterWidget']
        );
    }
}