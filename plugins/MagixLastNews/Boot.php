<?php
namespace Plugins\MagixLastNews;

use App\Component\Hook\HookManager;

class Boot
{
    public function register(): void
    {
        // On accroche ce widget au hook de la page d'accueil par exemple
        HookManager::register('displayHome', 'MagixLastNews',
            [\Plugins\MagixLastNews\src\FrontendController::class, 'renderWidget']
        );
    }
}