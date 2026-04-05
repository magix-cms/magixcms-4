<?php
declare(strict_types=1);

namespace Plugins\GoogleRecaptcha;

use App\Component\Hook\HookManager;

class Boot
{
    public function register(): void
    {
        // C'est un hook technique/invisible : il DOIT être déclaré ici !
        HookManager::register(
            'displayHead',
            'GoogleRecaptcha',
            [\Plugins\GoogleRecaptcha\src\FrontendController::class, 'injectScript']
        );
    }
}