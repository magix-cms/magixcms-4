<?php

declare(strict_types=1);

namespace Plugins\MagixClear;

class Boot
{
    /**
     * Méthode appelée lors de l'initialisation du CMS.
     * Utile si vous vouliez rajouter des hooks (ex: vider le cache automatiquement à la sauvegarde d'une page)
     */
    public function register(): void
    {
        // Pas de hook spécifique requis pour l'interface de base,
        // le menu le détectera automatiquement grâce à votre getValidatedPluginsForMenu() !
    }
}