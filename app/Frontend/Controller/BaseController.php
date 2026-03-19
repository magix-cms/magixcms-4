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
use App\Component\Routing\UrlTool;
use App\Frontend\Model\SeoHelper;

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
        // 🟢 2. ON VÉRIFIE LA MAINTENANCE ICI !
        // Si c'est un visiteur, le script fera un "exit" à l'intérieur de cette méthode
        // et le reste du constructeur ne sera jamais exécuté.
        $this->checkMaintenanceMode();
        $this->initLanguage();

        $this->initGlobalData();
        $this->initMenu();
        $this->initTranslations();
        $this->initCanonicalUrl();
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
    // BaseController.php

    private function initMenu(): void
    {
        $menuDb = new MenuDb();
        $idLang = (int)($this->currentLang['id_lang'] ?? 1);
        $isoLang = $this->currentLang['iso_lang'] ?? 'fr';
        $urlTool = new UrlTool();

        $menuTree = $menuDb->getFrontendTree($idLang, $isoLang);

        foreach ($menuTree as &$item) {

            // 🟢 1. SÉCURISATION DU LIEN PARENT (Niveau 1)
            // On corrige les liens plugins ou custom (ex: /contact/) sans toucher aux liens déjà formatés
            $urlLink = (string)($item['url_link'] ?? '');

            if (!empty($urlLink) && !str_starts_with($urlLink, 'http') && $urlLink !== '#') {
                $urlLink = '/' . ltrim($urlLink, '/'); // Assure qu'on commence par un seul slash

                // Si l'URL ne commence pas DÉJÀ par la langue (ex: /fr/), on l'ajoute !
                if (!str_starts_with($urlLink, "/{$isoLang}/") && $urlLink !== "/{$isoLang}") {
                    $item['url_link'] = "/{$isoLang}{$urlLink}";
                } else {
                    $item['url_link'] = $urlLink;
                }
            }

            // 2. ON TRAITE LES ENFANTS (Niveau 2+) SI DROPDOWN/MEGA
            if (isset($item['mode_link']) && in_array($item['mode_link'], ['dropdown', 'mega'])) {
                $type = $item['type_link'] ?? '';
                $idPageTarget = (int)($item['id_page'] ?? 0);

                // --- MODULE PAGES ---
                if ($type === 'pages' && $idPageTarget > 0) {
                    $subData = $menuDb->getSubPages($idPageTarget, $idLang);
                    foreach ($subData as &$sub) {
                        $sub['url_link'] = $urlTool->buildUrl([
                            'type' => 'pages',
                            'id'   => $sub['id_pages'],
                            'url'  => $sub['url_pages'] ?? $sub['url_link'],
                            'iso'  => $isoLang
                        ]);
                    }
                    $item['subdata'] = $subData;
                }
                // --- MODULE ABOUT ---
                elseif ($type === 'about_page' && $idPageTarget > 0) {
                    $subData = $menuDb->getSubAbout($idPageTarget, $idLang);
                    foreach ($subData as &$sub) {
                        $sub['url_link'] = $urlTool->buildUrl([
                            'type' => 'about',
                            'id'   => $sub['id_about'],
                            'url'  => $sub['url_about'],
                            'iso'  => $isoLang
                        ]);
                    }
                    $item['subdata'] = $subData;
                }
                // --- MODULE CATALOGUE (CATEGORIES) ---
                elseif ($type === 'category' && $idPageTarget > 0) {
                    $subData = $menuDb->getSubCategories($idPageTarget, $idLang);
                    foreach ($subData as &$sub) {
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

                require_once $bootFile; // 🟢 CORRECTION INDISPENSABLE !

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

            // --- Logo Principal (Header) ---
            $rawLogo = $logoDb->getActiveLogo($idLang);
            $activeLogo = null;
            if ($rawLogo) {
                $formattedLogos = $imageTool->setModuleImages('logo', 'logo', [$rawLogo], 0, '/img/logo/');
                $activeLogo = $formattedLogos[0] ?? null;
            }
            $this->view->assign('logo', $activeLogo);

            // --- Logo Secondaire (Footer) ---
            $rawFooterLogo = $logoDb->getActiveFooterLogo($idLang);
            $activeFooterLogo = null;
            if ($rawFooterLogo) {
                $formattedFooterLogos = $imageTool->setModuleImages('logo', 'logo', [$rawFooterLogo], 0, '/img/logo/');
                $activeFooterLogo = $formattedFooterLogos[0] ?? null;
            }
            $this->view->assign('logoFooter', $activeFooterLogo);

            // 3. VARIABLES D'URL POUR LE FRONTEND (Multilingue)
            $baseUrl = $this->view->getTemplateVars('site_url') . '/';
            $langIso = $this->currentLang['iso_lang'] ?? '';

            $langs = $this->view->getTemplateVars('langs');
            $isMultilang = (is_array($langs) && count($langs) > 1);

            $this->view->assign([
                'base_url'     => $baseUrl,
                'lang_iso'     => $langIso,
                'is_multilang' => $isMultilang
            ]);

            // 🟢 4. SEO GLOBAL (JSON-LD WebSite)
            $siteName = $companyInfo['name'] ?? 'Magix CMS';
            $websiteJsonLd = SeoHelper::generateWebSiteJsonLd($siteName, $baseUrl);
            $this->view->assign('website_json_ld', $websiteJsonLd);

            // 5 Share
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
    private function initCanonicalUrl(): void
    {
        $settingDb = new SettingDb(); // Ou DomainDb selon où vous avez placé la méthode SQL
        $canonicalDomain = $settingDb->getCanonicalDomain();

        // Si aucun domaine canonique n'est défini en BDD, on s'arrête là.
        // La balise <link> ne sera pas affichée.
        if (!$canonicalDomain) {
            $this->view->assign('canonical_url', false);
            return;
        }

        // On récupère le protocole (http ou https)
        $isSsl = isset($this->siteSettings['ssl']['value']) ? (int)$this->siteSettings['ssl']['value'] : 0;
        $protocol = ($isSsl === 1) ? 'https://' : 'http://';

        // On nettoie le domaine canonique (au cas où il y aurait un slash à la fin en BDD)
        $cleanDomain = rtrim($canonicalDomain, '/');

        // On récupère le chemin complet demandé par l'utilisateur (ex: /fr/contact.html?source=fb)
        $requestUri = $_SERVER['REQUEST_URI'] ?? '/';

        // Optionnel : En SEO strict, on enlève souvent les paramètres GET (tout ce qui suit le "?")
        // de l'URL canonique pour éviter les duplicatas liés au tracking (ex: ?utm_source=...)
        $uriParts = explode('?', $requestUri);
        $cleanUri = $uriParts[0];

        // On assemble l'URL canonique parfaite !
        $canonicalUrl = $protocol . $cleanDomain . $cleanUri;

        // On l'envoie à Smarty
        $this->view->assign('canonical_url', $canonicalUrl);
    }

    /**
     * Vérifie si le site est en maintenance et bloque l'accès aux visiteurs.
     * Laisse passer les administrateurs connectés avec un avertissement.
     */
    private function checkMaintenanceMode(): void
    {
        $isMaintenance = isset($this->siteSettings['maintenance']['value']) ? (int)$this->siteSettings['maintenance']['value'] : 0;

        if ($isMaintenance === 1) {

            // On utilise bien 'id_admin' comme vous l'avez précisé
            $isAdminLoggedIn = $this->session->get('id_admin') !== null;

            if (!$isAdminLoggedIn) {
                // VISITEUR : On bloque et on affiche la page 503
                header('HTTP/1.1 503 Service Temporarily Unavailable');
                header('Status: 503 Service Temporarily Unavailable');
                header('Retry-After: 3600');

                $this->view->assign('companyData', $this->siteSettings['site_name']['value'] ?? 'MagixCMS');
                $this->view->assign('skin_url', $this->view->getTemplateVars('site_url') . '/skin/default');

                $this->view->display('maintenance.tpl');
                exit;
            } else {
                // 🟢 ADMINISTRATEUR : On laisse passer, mais on prévient Smarty !
                $this->view->assign('admin_maintenance_warning', true);
            }
        } else {
            // Sécurité : on s'assure que la variable est à false en temps normal
            $this->view->assign('admin_maintenance_warning', false);
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