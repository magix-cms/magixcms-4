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

use Smarty\Exception;
use Smarty\Smarty;
use Magepattern\Bootstrap;

/**
 * SmartyTool - Gestionnaire de moteur de template Smarty v5
 * Permet la gestion de multiples contextes (Frontend, Admin, etc.) avec isolation des caches.
 */
class SmartyTool
{
    /** @var array<string, Smarty> Stockage des instances actives */
    private static array $instances = [];

    /** @var array<string, array> Registre des configurations par contexte */
    private static array $registry = [];

    /**
     * Enregistre la configuration pour un contexte spécifique
     * @param string $context Nom du contexte (ex: 'frontend', 'admin')
     * @param array $config Configuration (template_dir, plugins_dir, etc.)
     */
    public static function registerContext(string $context, array $config): void
    {
        self::$registry[$context] = $config;
    }

    /**
     * Récupère l'instance Smarty pour un contexte donné
     * @param string $context Nom du contexte
     * @return Smarty
     */
    public static function getInstance(string $context = 'frontend'): Smarty
    {
        if (!isset(self::$instances[$context])) {
            $config = self::$registry[$context] ?? [];
            self::$instances[$context] = self::createInstance($context, $config);
        }

        return self::$instances[$context];
    }

    /**
     * Initialisation interne d'une instance Smarty 5
     */
    /**
     * @param string $context
     * @param array $config
     * @return Smarty
     * @throws Exception
     */
    private static function createInstance(string $context, array $config): Smarty
    {
        // 1. Chargement de la librairie via le Bootstrap
        Bootstrap::getInstance()->load('smarty');

        $smarty = new Smarty();

        // --- CONFIGURATION DES RÉPERTOIRES ---
        $rootDir = dirname(__DIR__, 3);
        $templateDir = $config['template_dir'] ?? $rootDir . DIRECTORY_SEPARATOR . 'themes/default/templates';
        $compileDir  = $config['compile_dir']  ?? $rootDir . DIRECTORY_SEPARATOR . 'var/smarty/compile/' . $context;
        $cacheDir    = $config['cache_dir']    ?? $rootDir . DIRECTORY_SEPARATOR . 'var/smarty/cache/' . $context;
        $configDir   = $config['config_dir']   ?? $rootDir . DIRECTORY_SEPARATOR . 'config/smarty';

        $smarty->setTemplateDir($templateDir);
        $smarty->setCompileDir($compileDir);
        $smarty->setCacheDir($cacheDir);
        $smarty->setConfigDir($configDir);

        // --- LA SOLUTION SMARTY 5 POUR LES FONCTIONS PHP ---
        // On déclare explicitement les fonctions natives de PHP comme étant des "modifiers" utilisables dans la vue.
        // Liste des fonctions PHP que tu souhaites autoriser comme modificateurs
        $allowedFunctions = ['print_r', 'var_dump', 'count', 'json_encode', 'trim', 'ucfirst'];

        foreach ($allowedFunctions as $function) {
            if (function_exists($function)) {
                $smarty->registerPlugin('modifier', $function, $function);
            }
        }

        $isDebug = $config['debug'] ?? true;

        if ($isDebug) {
            // 1. Vérifie si le TPL a changé (comparaison des timestamps)
            $smarty->setCompileCheck(true);

            // 2. Si vraiment le timestamp échoue, force la recompilation
            // mais SEULEMENT en mode debug.
            $smarty->setForceCompile(false);

            // 3. Désactive le cache de rendu (différent de la compilation)
            $smarty->setCaching(\Smarty\Smarty::CACHING_OFF);

            // 4. Désactive les sous-dossiers pour voir tes fichiers compilés en vrac
            $smarty->setUseSubDirs(true);
        } else {
            // Mode Production : On verrouille tout pour la performance
            //$smarty->setCompileCheck(false);
            $smarty->setCompileCheck(true);
            $smarty->setForceCompile(false);
            $smarty->setUseSubDirs(true);
        }

        /*if ($isDebug) {
            $smarty->setDebugging(true);
        }*/

        // --- CHARGEMENT DE TES PLUGINS CUSTOM ---
        self::loadPluginsFromDir($smarty, $rootDir . DIRECTORY_SEPARATOR . 'magepattern/package/smarty-plugins');

        if (isset($config['plugins_dir'])) {
            $pluginsDirs = is_array($config['plugins_dir']) ? $config['plugins_dir'] : [$config['plugins_dir']];
            foreach ($pluginsDirs as $dir) {
                self::loadPluginsFromDir($smarty, $dir);
            }
        }

        $smarty->setCompileCheck($isDebug);
        $smarty->setEscapeHtml($config['escape_html'] ?? true);

        self::checkDirectories($compileDir, $cacheDir);

        return $smarty;
    }

    /**
     * Charge dynamiquement les anciens plugins Smarty depuis un dossier.
     * Recrée le comportement de addPluginsDir() pour Smarty 5 via registerPlugin().
     */
    /**
     * @param Smarty $smarty
     * @param string $dir
     * @return void
     * @throws \Smarty\Exception
     */
    private static function loadPluginsFromDir(Smarty $smarty, string $dir): void
    {
        $dir = rtrim($dir, DIRECTORY_SEPARATOR);
        if (!is_dir($dir)) {
            return;
        }

        $files = glob($dir . DIRECTORY_SEPARATOR . '*.php');
        if (!$files) {
            return;
        }

        foreach ($files as $file) {
            $filename = basename($file);

            // Regex pour capter function.name.php, modifier.name.php, block.name.php
            if (preg_match('/^(function|modifier|block|compiler)\.(.+)\.php$/', $filename, $matches)) {
                $type = $matches[1];
                $tag  = $matches[2];

                require_once $file;

                $functionName = 'smarty_' . $type . '_' . $tag;

                if (is_callable($functionName)) {
                    $smarty->registerPlugin($type, $tag, $functionName);
                }
            }
        }
    }

    /**
     * Vérifie et crée les dossiers récursivement si nécessaire
     */
    /**
     * @param string ...$dirs
     * @return void
     */
    private static function checkDirectories(string ...$dirs): void
    {
        foreach ($dirs as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0775, true);
            }
        }
    }
    /**
     * Ajoute un répertoire de templates supplémentaire pour un contexte.
     * Indispensable pour que les plugins puissent utiliser leurs propres vues tout en héritant du layout Core.
     *
     * @param string $context Nom du contexte (ex: 'admin')
     * @param string $dir Chemin absolu vers le dossier des templates du plugin
     */
    public static function addTemplateDir(string $context, string $dir): void
    {
        // 1. Mise à jour du registre (si l'instance n'est pas encore créée)
        if (isset(self::$registry[$context])) {
            $currentDir = self::$registry[$context]['template_dir'] ?? [];
            if (!is_array($currentDir)) {
                $currentDir = [$currentDir];
            }
            // On met le dossier du plugin EN PREMIER (priorité sur le core)
            array_unshift($currentDir, $dir);
            self::$registry[$context]['template_dir'] = $currentDir;
        }

        // 2. Mise à jour de l'instance si elle existe déjà en mémoire
        if (isset(self::$instances[$context])) {
            $smarty = self::$instances[$context];
            // On récupère les dossiers actuels
            $dirs = (array) $smarty->getTemplateDir();
            // On ajoute le dossier du plugin en première position
            array_unshift($dirs, $dir);
            // On réapplique la liste complète
            $smarty->setTemplateDir($dirs);
        }
    }
}