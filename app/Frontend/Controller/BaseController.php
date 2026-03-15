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
use App\Frontend\Db\LogoDb;
use App\Frontend\Db\MenuDb;
use App\Frontend\Db\ShareDb;
use App\Component\File\ImageTool;
use Smarty\Smarty;
use Magepattern\Component\HTTP\JSON;

abstract class BaseController
{
    protected Smarty $view;
    protected Session $session;
    protected Logger $logger;
    protected array $siteSettings = [];
    protected array $currentLang = [];
    /**
     * @var JSON
     */
    protected JSON $json;
    /**
     * @throws \Smarty\Exception
     */
    public function __construct()
    {
        $this->view = SmartyTool::getInstance('front');
        $this->logger = Logger::getInstance();
        $this->session = new Session(false);
        $this->json = new JSON();
        // 🟢 CORRECTIF : On utilise une variable statique pour s'assurer
        // que cette portion de code ne s'exécute qu'une seule fois par requête HTTP
        static $pluginsLoaded = false;

        if (!$pluginsLoaded) {
            if (class_exists('\App\Component\Hook\HookManager')) {
                // On enregistre le hook Smarty
                $this->view->registerPlugin('function', 'hook', ['\App\Component\Hook\HookManager', 'smartyHook']);
            }

            // On démarre les plugins Magix
            $this->bootPlugins();

            $pluginsLoaded = true;
        }

        // Le reste des initialisations continue normalement
        $this->initSettings();
        $this->initSiteUrl();
        $this->initSkin();
        $this->initLanguage();

        $this->initGlobalData();
        $this->initMenu();
        $this->initTranslations();
    }

    /**
     * @return void
     */
    /**
     * @return void
     */
    /**
     * @return void
     */
    /**
     * @return void
     */
    private function initMenu(): void
    {
        $menuDb = new MenuDb();
        $idLang = (int)($this->currentLang['id_lang'] ?? 1);
        $isoLang = $this->currentLang['iso_lang'] ?? 'fr';
        $urlTool = new \App\Component\Routing\UrlTool();

        $menuTree = $menuDb->getFrontendTree($idLang, $isoLang);

        foreach ($menuTree as &$item) {
            if (isset($item['mode_link']) && in_array($item['mode_link'], ['dropdown', 'mega'])) {
                $type = $item['type_link'] ?? '';
                $idPageTarget = (int)($item['id_page'] ?? 0);

                // --- MODULE PAGES ---
                if ($type === 'pages' && $idPageTarget > 0) {
                    $subData = $menuDb->getSubPages($idPageTarget, $idLang);
                    foreach ($subData as &$sub) {
                        $sub['url_link'] = $urlTool->buildUrl([
                            'type' => 'pages',
                            'id' => $sub['id_pages'],
                            'url' => $sub['url_pages'] ?? $sub['url_link'],
                            'iso' => $isoLang]
                        );
                    }
                    $item['subdata'] = $subData;
                }

                // --- MODULE ABOUT ---
                elseif ($type === 'about_page' && $idPageTarget > 0) {
                    $subData = $menuDb->getSubAbout($idPageTarget, $idLang);
                    foreach ($subData as &$sub) {
                        $sub['url_link'] = $urlTool->buildUrl([
                            'type' => 'about',
                            'id' => $sub['id_about'],
                            'url' => $sub['url_about'],
                            'iso' => $isoLang]
                        );
                    }
                    $item['subdata'] = $subData;
                }

                // --- MODULE CATALOGUE (CATEGORIES) ---
                elseif ($type === 'category' && $idPageTarget > 0) {
                    $subData = $menuDb->getSubCategories($idPageTarget, $idLang);
                    foreach ($subData as &$sub) {
                        // L'UrlTool utilisera le cas 'category' de son switch match()
                        $sub['url_link'] = $urlTool->buildUrl([
                            'type' => 'category',
                            'id'   => $sub['id_cat'],
                            'url'  => $sub['url_cat'],
                            'iso'  => $isoLang
                        ]);
                    }
                    $item['subdata'] = $subData;
                }
            }
        }
        unset($item);

        $this->view->assign('menuData', $menuTree);
        $this->view->assign('active_link', ['controller' => '', 'ids' => []]);
    }

    /**
     * @return void
     */
    private function bootPlugins(): void
    {
        $pluginsDir = ROOT_DIR . 'plugins';
        if (!is_dir($pluginsDir)) return;

        foreach (scandir($pluginsDir) as $pluginFolder) {
            if ($pluginFolder === '.' || $pluginFolder === '..') continue;

            $bootFile = $pluginsDir . DS . $pluginFolder . DS . 'Boot.php';
            if (file_exists($bootFile)) {
                $bootClass = "\\Plugins\\" . $pluginFolder . "\\Boot";
                if (class_exists($bootClass)) {
                    $bootInstance = new $bootClass();
                    if (method_exists($bootInstance, 'register')) {
                        $bootInstance->register();
                    }
                }
            }
        }
    }

    /**
     * @return void
     */
    private function initSettings(): void
    {
        $settingDb = new SettingDb();
        $this->siteSettings = $settingDb->fetchAllSettings();
        $this->view->assign('mc_settings', $this->siteSettings);
    }

    /**
     * @return void
     */
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
     * @return void
     */
    private function initSkin(): void
    {
        $skinFolder = $this->siteSettings['theme']['value'] ?? 'default';
        $skinPath = ROOT_DIR . 'skin' . DS . $skinFolder;

        if (is_dir($skinPath)) {
            $this->view->setTemplateDir($skinPath);
            $this->view->setConfigDir($skinPath . DS . 'i18n');

            $siteUrl = $this->view->getTemplateVars('site_url');
            $this->view->assign('skin_url', $siteUrl . '/skin/' . $skinFolder);
        } else {
            $this->logger->log("Le skin '{$skinFolder}' est introuvable. Fallback sur default.", "error");
        }
    }

    /**
     * @return void
     */
    private function initLanguage(): void
    {
        $langDb = new LangDb();
        $allLangs = $langDb->getFrontendLanguages(); // Toutes les langues
        $defaultLang = $langDb->getDefaultLanguage() ?: ['id_lang' => 1, 'iso_lang' => 'fr'];

        $requestedIso = '';

        // 1. Vérifier si l'URL contient une langue (ex: ?lang=en)
        if (!empty($_GET['lang'])) {
            $requestedIso = strtolower(trim($_GET['lang']));
        }
        // 2. Sinon, vérifier la session
        elseif ($this->session->get('user_lang')) {
            $requestedIso = $this->session->get('user_lang');
        }

        // 3. Chercher la langue dans la liste via une variable temporaire (pour respecter le type array)
        $foundLang = [];
        if ($requestedIso) {
            foreach ($allLangs as $lang) {
                if ($lang['iso_lang'] === $requestedIso) {
                    $foundLang = $lang;
                    break;
                }
            }
        }

        // 4. On assigne la langue trouvée, sinon on prend celle par défaut
        $this->currentLang = !empty($foundLang) ? $foundLang : $defaultLang;

        // 5. Sauvegarder en session pour éviter de la perdre en naviguant
        if ($this->session->get('user_lang') !== $this->currentLang['iso_lang']) {
            $this->session->set('user_lang', $this->currentLang['iso_lang']);
        }

        // 6. Assigner à Smarty
        $this->view->assign('current_lang', $this->currentLang);
        $this->view->assign('langs', $allLangs);
        $this->view->assign('default_lang', $defaultLang);
    }

    /**
     * Centralise le chargement de TOUTES les données transversales
     */
    private function initGlobalData(): void
    {
        try {
            // 1. ENTREPRISE ET CONFIG
            $companyDb = new CompanyDb();
            $companyInfo = $companyDb->getCompanyInfo();
            // J'assigne les deux noms pour ne pas casser vos anciens templates
            $this->view->assign('company', $companyInfo);
            $this->view->assign('companyData', $companyInfo);

            $configDb = new ConfigDb();
            $this->view->assign('mc_config', $configDb->getGlobalConfig());

            // 2. LOGO
            $logoDb = new LogoDb();
            $imageTool = new ImageTool();

            $idLang = (int)($this->currentLang['id_lang'] ?? 1);
            $rawLogo = $logoDb->getActiveLogo($idLang);
            $activeLogo = null;

            if ($rawLogo) {
                $formattedLogos = $imageTool->setModuleImages('logo', 'logo', [$rawLogo], 0, '/img/logo/');
                $activeLogo = $formattedLogos[0] ?? null;
            }
            $this->view->assign('logo', $activeLogo);

            // 3. VARIABLES D'URL POUR LE FRONTEND (Multilingue)
            // On récupère le site_url généré juste avant
            // On récupère le site_url généré juste avant
            $baseUrl = $this->view->getTemplateVars('site_url') . '/';
            $langIso = $this->currentLang['iso_lang'] ?? '';

            // On vérifie le multilingue en comptant le tableau envoyé par initLanguage
            $langs = $this->view->getTemplateVars('langs');
            $isMultilang = (is_array($langs) && count($langs) > 1);

            $this->view->assign([
                'base_url'     => $baseUrl,
                'lang_iso'     => $langIso, // <--- CORRECTION ICI : On utilise lang_iso
                'is_multilang' => $isMultilang
            ]);
            // 4 Share
            $shareDb = new ShareDb();
            $this->view->assign('shareNetworks', $shareDb->getActiveNetworks());

        } catch (\Throwable $e) {
            $this->logger->log("Erreur chargement globales front : " . $e->getMessage(), "warning");
        }
    }
    /**
     * Charge les fichiers de traduction (.conf) pour le thème et les plugins
     */
    /**
     * Charge les fichiers de traduction (.conf) pour le thème et les plugins
     */
    protected function initTranslations(): void
    {
        $locale = $this->currentLang['iso_lang'] ?? 'fr';

        // 1. PLUGINS D'ABORD (Priorité basse)
        // On charge les traductions de tous les plugins actifs.
        $this->stackPluginsTranslations($locale);

        // 2. SKIN EN DERNIER (Priorité haute : écrase les clés identiques des plugins)
        $skinFolder = $this->siteSettings['theme']['value'] ?? 'default';
        $skinConfFile = ROOT_DIR . 'skin' . DS . $skinFolder . DS . 'i18n' . DS . $locale . '.conf';

        try {
            if (file_exists($skinConfFile)) {
                $this->view->configLoad($skinConfFile);
            } else {
                // Fallback sur le français si la langue demandée n'existe pas dans le skin
                $fallbackConfFile = ROOT_DIR . 'skin' . DS . $skinFolder . DS . 'i18n' . DS . 'fr.conf';
                if (file_exists($fallbackConfFile)) {
                    $this->view->configLoad($fallbackConfFile);
                }
            }
        } catch (\Exception $e) {
            $this->logger->log("Erreur chargement i18n front (Skin) : " . $e->getMessage(), "warning");
        }
    }

    /**
     * Empile les traductions frontend des plugins
     */
    private function stackPluginsTranslations(string $locale): void
    {
        $pluginsPath = ROOT_DIR . 'plugins' . DS;
        if (!is_dir($pluginsPath)) return;

        try {
            foreach (scandir($pluginsPath) as $pluginFolder) {
                if ($pluginFolder === '.' || $pluginFolder === '..') continue;

                $pluginConf = $pluginsPath . $pluginFolder . DS . 'i18n' . DS . 'front' . DS . $locale . '.conf';

                if (file_exists($pluginConf)) {
                    $this->view->configLoad($pluginConf);
                }
            }
        } catch (\Throwable $e) {
            $this->logger->log("Erreur chargement i18n front (Plugins) : " . $e->getMessage(), "warning");
        }
    }
    /**
     * Envoie une réponse JSON proprement formatée et arrête le script.
     */
    protected function jsonResponse(bool $status, string $message, array $data = []): void
    {
        $payload = array_merge([
            'status'  => $status,
            'success' => $status,
            'message' => $message,
            'time'    => time()
        ], $data);

        header('Content-Type: application/json; charset=utf-8');
        echo $this->json->encode($payload);
        exit;
    }
    /**
     * @return void
     */
    abstract public function run(): void;
}