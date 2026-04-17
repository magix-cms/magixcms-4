<?php

declare(strict_types=1);

namespace App\Frontend\Model;

use App\Frontend\Db\SettingDb;

class ThemeManager
{
    /**
     * @var string|null Garde en mémoire le thème pour la durée de la requête
     */
    private static ?string $activeTheme = null;

    /**
     * Récupère le nom du dossier du thème actif
     */
    public static function getThemeFolder(): string
    {
        // Si on l'a déjà cherché pendant cette requête, on le retourne directement
        if (self::$activeTheme !== null) {
            return self::$activeTheme;
        }

        // Sinon, on interroge la base de données (ou son cache)
        $settingDb = new SettingDb();
        $settings = $settingDb->fetchAllSettings();

        // On assigne le thème trouvé, avec 'default' en sécurité
        self::$activeTheme = $settings['theme']['value'] ?? 'default';

        return self::$activeTheme;
    }

    /**
     * Retourne le chemin physique absolu vers le dossier du thème (skin)
     */
    public static function getThemePath(): string
    {
        return ROOT_DIR . 'skin' . DIRECTORY_SEPARATOR . self::getThemeFolder();
    }
}