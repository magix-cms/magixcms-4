<?php
/*
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Mage Pattern.
# The toolkit PHP for developer
# Copyright (C) 2012 - 2026 Gerits Aurelien contact[at]gerits-aurelien[dot]be
#
# OFFICIAL TEAM MAGE PATTERN:
#
#   * Gerits Aurelien (Author - Developer) contact[at]gerits-aurelien[dot]be
#
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.

# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
# Redistributions of source code must retain the above copyright notice,
# this list of conditions and the following disclaimer.
#
# Redistributions in binary form must reproduce the above copyright notice,
# this list of conditions and the following disclaimer in the documentation
# and/or other materials provided with the distribution.
#
# DISCLAIMER
*/

namespace Magepattern;

use Magepattern\Component\Autoload;
use RuntimeException;

final class Bootstrap
{
    private static ?Bootstrap $instance = null;

    /**
     * @var array<string, string> Définition des chemins des librairies externes
     */
    private static array $libraries = [
        'autoloader'   => __DIR__ . '/component/Autoload.php',
        'smarty'       => __DIR__ . '/package/smarty/vendor/autoload.php',
        'mailer'       => __DIR__ . '/package/mailer/vendor/autoload.php',
        'mobiledetect' => __DIR__ . '/package/mobiledetect/vendor/autoload.php',
        'dompdf'       => __DIR__ . '/package/dompdf/vendor/autoload.php'
    ];

    /**
     * @var array<string> Librairies à charger au démarrage
     */
    private static array $boot_libraries = [
        'autoloader',
        // 'swift', // Décommenter uniquement si une version compatible PHP 8 est installée
        'mobiledetect'
    ];

    /**
     * @var array<string> Librairies déjà chargées en mémoire
     */
    private static array $loaded_libraries = [];

    /**
     * Constructeur privé (Singleton)
     */
    private function __construct() {}

    /**
     * Clone interdit (Singleton)
     */
    private function __clone() {}

    /**
     * Récupération de l'instance unique
     */
    public static function getInstance(): self
    {
        return self::$instance ??= new self();
    }

    /**
     * Charge une librairie spécifique définie dans $libraries
     *
     * @param string $library Clé de la librairie (ex: 'swift')
     * @return bool True si chargé ou déjà chargé, False si introuvable
     */
    public function load(string $library): bool
    {
        // Si déjà chargée, on arrête là
        if (in_array($library, self::$loaded_libraries, true)) {
            return true;
        }

        // Vérification que la clé existe dans la config
        if (!array_key_exists($library, self::$libraries)) {
            // On pourrait logger ici : "Librairie $library non définie dans Bootstrap"
            return false;
        }

        $path = self::$libraries[$library];

        if (file_exists($path)) {
            require_once $path;
            self::$loaded_libraries[] = $library;
            return true;
        }

        // Fichier physique introuvable
        return false;
    }

    /**
     * Charge les dépendances obligatoires définies dans boot_libraries
     */
    private function loadBootLibraries(): void
    {
        foreach (self::$boot_libraries as $library) {
            if (!$this->load($library)) {
                // Si l'autoloader manque, c'est critique => Arrêt du script
                if ($library === 'autoloader') {
                    throw new RuntimeException("CRITICAL: Unable to load Autoloader at " . self::$libraries['autoloader']);
                }
            }
        }
    }

    /**
     * Initialise l'Autoloader et enregistre les namespaces
     */
    public function registerAutoloader(): void
    {
        // 1. Charger les fichiers requis (dont Autoload.php)
        $this->loadBootLibraries();

        // 2. Vérifier que la classe existe bien (sécurité)
        if (!class_exists(Autoload::class)) {
            throw new RuntimeException("Autoload class not found. Check your file structure.");
        }

        // 3. Configuration de l'autoloader
        $autoloader = new Autoload();

        // Définition de la racine des composants
        $baseComponentDir = __DIR__ . '/component';

        $autoloader->addNamespace(
            'Magepattern\Component',
            [
                'Database' => $baseComponentDir . '/database',
                'Debug'    => $baseComponentDir . '/debug',
                'File'     => $baseComponentDir . '/file',
                'HTTP'     => $baseComponentDir . '/http',
                'Security' => $baseComponentDir . '/security',
                'Tool'     => $baseComponentDir . '/tool',
                'XML'      => $baseComponentDir . '/xml'
            ]
        );

        $autoloader->register();
    }
}

// Lancement automatique
Bootstrap::getInstance()->registerAutoloader();