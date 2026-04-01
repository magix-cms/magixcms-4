<?php
namespace Plugins\MagixLastNews;

use App\Component\Hook\HookManager;

class Boot
{
    public function register(): void
    {
        // 1. Accroche Home static
        HookManager::register('displayHome', 'MagixLastNews',
            [\Plugins\MagixLastNews\src\FrontendController::class, 'renderWidget']
        );
    }
}