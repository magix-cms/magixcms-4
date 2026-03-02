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

namespace Magepattern\Component\Tool;

use Magepattern\Component\Debug\Logger;
use Exception;
use Throwable;

class ConsoleTool
{
    private static array $commands = [];

    // Codes couleurs ANSI pour le terminal
    private const COLOR_RESET = "\033[0m";
    private const COLOR_RED = "\033[31m";
    private const COLOR_GREEN = "\033[32m";
    private const COLOR_YELLOW = "\033[33m";
    private const COLOR_BLUE = "\033[34m";

    /**
     * Enregistre une commande
     * @param string $signature Le nom de la commande (ex: 'app:import')
     * @param callable $callback La fonction à exécuter
     * @param string $description Description pour l'aide
     */
    public static function register(string $signature, callable $callback, string $description = ''): void
    {
        self::$commands[$signature] = [
            'callback' => $callback,
            'desc'     => $description
        ];
    }

    /**
     * Point d'entrée principal (à appeler depuis bin/console)
     */
    public static function run(array $argv): void
    {
        // 1. Nettoyage des arguments
        $scriptName = array_shift($argv); // Enlève le nom du fichier script
        $commandName = $argv[0] ?? 'help'; // Commande par défaut

        // 2. Gestion de l'aide
        if ($commandName === 'help' || !isset(self::$commands[$commandName])) {
            self::showHelp();
            return;
        }

        // 3. Préparation de l'exécution
        $params = array_slice($argv, 1); // Reste des arguments
        $startTime = microtime(true);

        self::info("Magepattern CLI v3.0");
        self::line("--------------------------------");
        self::info("Exécution de : " . $commandName);

        // 4. Lancement du Timer
        TimerTool::getInstance('console')->start();

        try {
            // 5. Exécution de la commande enregistrée
            $callback = self::$commands[$commandName]['callback'];
            call_user_func($callback, $params);

            // 6. Rapport de fin
            $report = TimerTool::getInstance('console')->stop();

            self::line("--------------------------------");
            self::success("✔ Terminée en " . $report['total_time']);
            self::line("Mémoire Pic : " . $report['peak_memory']);

        } catch (Throwable $e) {
            // Gestion d'erreur propre
            Logger::getInstance()->log($e, "console", "error");
            self::error("✘ Erreur Fatale : " . $e->getMessage());
            self::line("Voir les logs pour la trace complète.");
            exit(1); // Code de sortie erreur
        }
    }

    /**
     * Affiche la liste des commandes disponibles
     */
    private static function showHelp(): void
    {
        self::info("Magepattern Console Tool");
        self::line("Usage: php bin/console [commande] [arguments]");
        self::line("");
        self::line("Commandes disponibles :");

        $maxLength = 0;
        foreach (array_keys(self::$commands) as $cmd) {
            $maxLength = max($maxLength, strlen($cmd));
        }

        foreach (self::$commands as $name => $cmd) {
            $padding = str_repeat(' ', $maxLength - strlen($name) + 2);
            echo self::COLOR_GREEN . $name . self::COLOR_RESET . $padding . $cmd['desc'] . PHP_EOL;
        }
    }

    // --- Helpers de Sortie ---

    public static function line(string $msg): void
    {
        echo $msg . PHP_EOL;
    }

    public static function info(string $msg): void
    {
        echo self::COLOR_BLUE . $msg . self::COLOR_RESET . PHP_EOL;
    }

    public static function success(string $msg): void
    {
        echo self::COLOR_GREEN . $msg . self::COLOR_RESET . PHP_EOL;
    }

    public static function error(string $msg): void
    {
        echo self::COLOR_RED . $msg . self::COLOR_RESET . PHP_EOL;
    }

    public static function warning(string $msg): void
    {
        echo self::COLOR_YELLOW . $msg . self::COLOR_RESET . PHP_EOL;
    }
}