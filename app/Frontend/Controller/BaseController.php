<?php

declare(strict_types=1);

namespace App\Frontend\Controller;

use Magepattern\Component\Tool\SmartyTool;
use Magepattern\Component\HTTP\Session;
use Magepattern\Component\Debug\Logger;
use App\Frontend\Db\SettingDb;
use App\Frontend\Db\LangDb;
use App\Frontend\Db\CompanyDb;
use App\Frontend\Db\ConfigDb;
use Smarty\Smarty; // <--- À AJOUTER ICI

abstract class BaseController
{
    protected Smarty $view;
    protected Session $session;
    protected Logger $logger;

    protected array $siteSettings = [];
    protected array $currentLang = [];

    public function __construct()
    {
        $this->view = SmartyTool::getInstance('front');
        $this->logger = Logger::getInstance();
        $this->session = new Session(false);

        /*if (class_exists('\App\Component\Hook\HookManager')) {
            $this->view->registerPlugin('function', 'hook', ['\App\Component\Hook\HookManager', 'exec']);
        }*/
        if (class_exists('\App\Component\Hook\HookManager')) {
            // CORRECTION ICI : On utilise smartyHook pour lire la Base de Données
            $this->view->registerPlugin('function', 'hook', ['\App\Component\Hook\HookManager', 'smartyHook']);
        }

        // --- NOUVEAU : On réveille les plugins pour qu'ils s'accrochent aux Hooks ---
        $this->bootPlugins();

        $this->initSettings();
        $this->initSiteUrl();
        $this->initSkin();
        $this->initLanguage();
        $this->initGlobalData();
    }

    /**
     * NOUVEAU : Parcourt le dossier plugins et lance les fichiers Boot.php
     */
    private function bootPlugins(): void
    {
        $pluginsDir = ROOT_DIR . 'plugins';

        if (!is_dir($pluginsDir)) {
            return;
        }

        // On scanne tous les dossiers de plugins
        foreach (scandir($pluginsDir) as $pluginFolder) {
            if ($pluginFolder === '.' || $pluginFolder === '..') {
                continue;
            }

            // Si le plugin possède un fichier Boot.php
            $bootFile = $pluginsDir . DS . $pluginFolder . DS . 'Boot.php';
            if (file_exists($bootFile)) {

                // On construit le nom de la classe (ex: \Plugins\MagixFeatured\Boot)
                $bootClass = "\\Plugins\\" . $pluginFolder . "\\Boot";

                if (class_exists($bootClass)) {
                    $bootInstance = new $bootClass();

                    // On exécute la méthode register() qui va remplir le HookManager
                    if (method_exists($bootInstance, 'register')) {
                        $bootInstance->register();
                    }
                }
            }
        }
    }

    private function initSettings(): void
    {
        $settingDb = new SettingDb();
        $this->siteSettings = $settingDb->fetchAllSettings();
        $this->view->assign('mc_settings', $this->siteSettings);
    }

    private function initSiteUrl(): void
    {
        $isSsl = isset($this->siteSettings['ssl']['value']) ? (int)$this->siteSettings['ssl']['value'] : 0;
        $protocol = ($isSsl === 1) ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'];

        $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
        $publicRoot = rtrim($scriptDir, '/');

        $siteUrl = $protocol . $host . $publicRoot;
        $this->view->assign('site_url', $siteUrl);
    }

    /**
     * NOUVEAU : Configure Smarty dynamiquement vers le dossier du skin actif
     */
    private function initSkin(): void
    {
        // On récupère le nom du dossier dans la DB (adaptez la clé 'theme' par 'skin' si nécessaire)
        $skinFolder = $this->siteSettings['theme']['value'] ?? 'default';

        $skinPath = ROOT_DIR . 'skin' . DS . $skinFolder;

        if (is_dir($skinPath)) {
            // On redéfinit le dossier principal des templates Smarty
            $this->view->setTemplateDir($skinPath);

            // Si vous utilisez des traductions spécifiques au skin (.conf)
            $this->view->setConfigDir($skinPath . DS . 'i18n');

            // On assigne une variable magique {$skin_url} pour charger facilement CSS, JS et Images dans le TPL
            $siteUrl = $this->view->getTemplateVars('site_url');
            $this->view->assign('skin_url', $siteUrl . '/skin/' . $skinFolder);
        } else {
            $this->logger->log("Le skin '{$skinFolder}' est introuvable. Fallback sur default.", "error");
        }
    }

    private function initLanguage(): void
    {
        $langDb = new LangDb();
        $defaultLang = $langDb->getDefaultLanguage();
        $this->currentLang = $defaultLang ?: ['id_lang' => 1, 'iso_lang' => 'fr'];

        $this->view->assign('current_lang', $this->currentLang);
        $this->view->assign('langs', $langDb->getFrontendLanguages());
    }

    private function initGlobalData(): void
    {
        try {
            $companyDb = new CompanyDb();
            $this->view->assign('company', $companyDb->getCompanyInfo());

            $configDb = new ConfigDb();
            $this->view->assign('mc_config', $configDb->getGlobalConfig());
        } catch (\Throwable $e) {
            $this->logger->log("Erreur chargement globales front : " . $e->getMessage(), "warning");
        }
    }

    abstract public function run(): void;
}