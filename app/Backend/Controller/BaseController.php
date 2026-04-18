<?php

declare(strict_types=1);

namespace App\Backend\Controller;

use App\Backend\Model\DataHelperTrait;
use App\Backend\Db\LangDb;
use App\Backend\Db\CompanyDb;
use App\Backend\Db\EmployeeDb; // <-- Importation de la classe Db
use App\Backend\Db\ConfigDb;
use Magepattern\Component\Tool\SmartyTool;
use Magepattern\Component\File\FileTool;
use Magepattern\Component\HTTP\Url;
use Smarty\Smarty;
use Magepattern\Component\Debug\Logger;
use Magepattern\Component\HTTP\Session;
use Magepattern\Component\HTTP\JSON;
use App\Backend\Db\PluginDb;
use App\Backend\Db\SettingDb;

abstract class BaseController
{
    use DataHelperTrait;

    protected Smarty $view;
    protected Logger $logger;

    // On stocke la langue globalement pour tous les contrôleurs
    protected array $defaultLang = [];

    /**
     * Par défaut, tous les contrôleurs de l'admin nécessitent d'être connecté.
     */
    protected bool $requireAuth = true;

    /**
     * @var Session
     */
    protected Session $session;

    /**
     * @var JSON
     */
    protected JSON $json;
    /**
     * @var array
     */
    protected array $siteSettings = [];

    public function __construct()
    {
        $this->view = SmartyTool::getInstance('admin');
        $this->logger = Logger::getInstance();

        if (class_exists('\App\Component\Hook\HookManager')) {
            $this->view->registerPlugin('function', 'hook', ['\App\Component\Hook\HookManager', 'exec']);
        }

        $currentController = $_GET['controller'] ?? 'Dashboard';
        $cleanController = ucfirst(strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $currentController)));
        $this->view->assign('controller', $cleanController);

        // 1. Langue par défaut
        $this->initDefaultLanguage();

        // 2. Configuration globale de l'entreprise
        $this->initCompanyData();

        // 3. Configuration globale du site
        $this->initGlobalConfig();

        // ==========================================================
        // 4. CHARGEMENT GLOBAL DES PARAMÈTRES (mc_setting)
        // ==========================================================
        $settingDb = new SettingDb();
        $this->siteSettings = $settingDb->fetchAllSettings();
        $this->view->assign('mc_settings', $this->siteSettings);

        // ==========================================================
        // 🛡️ BOUCLIER DE SÉCURITÉ ADMIN (Host Header Validation)
        // ==========================================================
        $isDevMode = false;

        // A. Vérification du mode dev en BDD
        if (isset($this->siteSettings['dev_mode']['value']) && (int)$this->siteSettings['dev_mode']['value'] === 1) {
            $isDevMode = true;
        }
        // B. Fallback : si on est en local, on autorise d'office
        elseif (in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost', '127.0.0.1', '::1'])) {
            $isDevMode = true;
        }

        // Si on est en production, on vérifie strictement le nom de domaine
        if (!$isDevMode) {
            $currentHost = strtolower(explode(':', $_SERVER['HTTP_HOST'] ?? '')[0]);
            $domainDb = new \App\Backend\Db\DomainDb();
            $allowedDomains = $domainDb->getAllowedHosts();

            if (!empty($allowedDomains) && !in_array($currentHost, $allowedDomains, true)) {
                $this->logger->log("ALERTE SÉCURITÉ ADMIN: Tentative d'accès via un domaine non autorisé : {$currentHost}", "security");
                header('HTTP/1.1 403 Forbidden');
                die("<h1>Accès Interdit</h1><p>L'administration n'est pas autorisée sur ce domaine pour des raisons de sécurité.</p>");
            }
        }

        // ==========================================================
        // 5. INITIALISATION DE LA SESSION & AUTHENTIFICATION
        // ==========================================================
        $isSsl = isset($this->siteSettings['ssl']['value']) ? (int)$this->siteSettings['ssl']['value'] : 0;
        $isSslActive = ($isSsl === 1);

        $this->session = new Session($isSslActive);

        if ($this->requireAuth) {
            $this->checkAuthentication($this->session);
            $this->checkPermissions($cleanController);
            $this->initCurrentUser();
            $this->initMenuPermissions();
            $this->initPlugins();
        }

        $this->initTranslations($this->session);

        $this->json = new JSON();
        $this->view->assign('installed_plugins', $this->getValidatedPluginsForMenu());

        // ==========================================================
        // 6. URL DU SITE GLOBALE POUR SMARTY
        // ==========================================================
        $adminBaseUrl = rtrim(Url::resolve(''), '/');
        $siteUrl = preg_replace('#/' . preg_quote(BASEADMIN, '#') . '$#', '', $adminBaseUrl);

        if ($isSslActive && str_starts_with($siteUrl, 'http://')) {
            $siteUrl = str_replace('http://', 'https://', $siteUrl);
        }

        $this->view->assign('site_url', $siteUrl);
        $this->view->assign('baseadmin', BASEADMIN);
    }

    /**
     * Vérifie si la session admin existe, sinon redirige.
     */
    private function checkAuthentication(Session $session): void
    {
        if (!$session->get('id_admin') || !$session->get('keyuniqid_admin')) {
            $session->destroy();
            header('Location: index.php?controller=Login');
            exit;
        }
    }

    /**
     * Vérifie les permissions du rôle pour le contrôleur en cours.
     */
    private function checkPermissions(string $currentController): void
    {
        // 1. Liste blanche des contrôleurs qui ne nécessitent pas de permissions strictes
        $whitelist = ['Login', 'Dashboard', 'Logout'];
        $controllerLower = ucfirst($currentController);

        if (in_array($controllerLower, $whitelist)) {
            return;
        }

        $idAdmin = (int)$this->session->get('id_admin');
        if ($idAdmin <= 0) {
            $this->redirectForbidden();
        }

        // 2. Requête propre via le fichier DB (MVC respecté)
        $db = new EmployeeDb();
        $access = $db->checkModuleAccess($idAdmin, $controllerLower);
        //var_dump($idAdmin, $controllerLower, $access); die();
        // 3. Vérification du droit d'affichage ("view")
        if (!$access || (int)$access['view'] === 0) {
            $this->redirectForbidden();
        }

        // 4. On assigne les permissions globales à la vue
        $this->view->assign('user_permissions', $access);
    }

    /**
     * Gère la redirection en cas d'accès refusé.
     */
    private function redirectForbidden(): void
    {
        // Enregistrement d'un message d'erreur en session pour le Dashboard
        $this->session->set('admin_error_msg', "Vous n'avez pas la permission d'accéder à ce module.");
        header('Location: index.php?controller=Dashboard');
        exit;
    }

    /**
     * Charge les langues et les assigne à Smarty
     */
    private function initDefaultLanguage(): void
    {
        $langDb = new LangDb();

        $langData = $langDb->getDefaultLanguage();

        if ($langData !== false) {
            $this->defaultLang = $langData;
            $this->view->assign('default_lang', $this->defaultLang);
        } else {
            $this->defaultLang = ['id_lang' => 1, 'iso_lang' => 'fr'];
        }

        $allLangs = $langDb->getFrontendLanguages();

        if (empty($allLangs)) {
            $allLangs = [$this->defaultLang['id_lang'] => $this->defaultLang['iso_lang']];
        }

        $this->view->assign('langs', $allLangs);
    }

    /**
     * Charge les infos de l'entreprise (mc_company_info)
     */
    private function initCompanyData(): void
    {
        try {
            $companyDb = new CompanyDb();
            $infos = $companyDb->getCompanyInfo();
            $this->view->assign('company', $infos);
        } catch (\Throwable $e) {
            $this->logger->log("Erreur chargement CompanyInfo : " . $e->getMessage(), "warning");
        }
    }

    /**
     * Initialise les fichiers de traduction .conf
     */
    protected function initTranslations($session): void
    {
        if (isset($_GET['lang']) && !empty($_GET['lang'])) {
            $newLang = preg_replace('/[^a-z]/', '', $_GET['lang']);

            if (file_exists(ROOT_DIR. BASEADMIN. DS . 'templates' . DS . 'i18n' . DS . 'local_' . $newLang . '.conf')) {
                $session->set('admin_lang', $newLang);
            }
        }

        $adminLocale = $session->get('admin_lang', 'fr');

        try {
            $this->view->configLoad('local_' . $adminLocale . '.conf');
            $this->stackPluginsTranslations($adminLocale);
        } catch (\Exception $e) {
            $this->logger->log("Erreur chargement i18n : " . $e->getMessage(), "warning");
        }

        $this->view->assign('admin_locale', $adminLocale);
    }

    /**
     * Charge les traductions des plugins
     */
    private function stackPluginsTranslations(string $locale): void
    {
        $pluginsPath = ROOT_DIR . 'plugins' . DS;
        if (!is_dir($pluginsPath)) return;

        try {
            foreach (FileTool::getDirectories($pluginsPath) as $item) {
                if ($item->isDir()) {
                    // On cherche spécifiquement dans le sous-dossier admin pour le backend
                    $pluginConf = $item->getPathname() . DS . 'i18n' . DS . 'admin' . DS . $locale . '.conf';

                    if (file_exists($pluginConf)) {
                        $this->view->configLoad($pluginConf);
                    }
                }
            }
        } catch (\Throwable $e) {
            $this->logger->log($e->getMessage(), "php", "error");
        }
    }
    /**
     * Charge la configuration globale (mc_config) et l'assigne à Smarty
     */
    private function initGlobalConfig(): void
    {
        try {
            $configDb = new ConfigDb();
            $configs = $configDb->getGlobalConfig();

            // La variable {$mc_config.nom_du_module} sera dispo partout !
            $this->view->assign('mc_config', $configs);
        } catch (\Throwable $e) {
            $this->logger->log("Erreur chargement mc_config : " . $e->getMessage(), "warning");
        }
    }
    /**
     * Charge les informations de l'administrateur connecté
     */
    private function initCurrentUser(): void
    {
        $idAdmin = (int)$this->session->get('id_admin');
        if ($idAdmin > 0) {
            $db = new \App\Backend\Db\EmployeeDb();
            $user = $db->fetchEmployeeById($idAdmin);
            if ($user) {
                $this->view->assign('current_user', $user);
            }
        }
    }
    /**
     * Initialise les plugins installés :
     * 1. Les assigne à la vue pour la Sidebar.
     * 2. Exécute leur fichier Boot.php pour enregistrer leurs Hooks.
     */
    private function initPlugins(): void
    {
        try {
            $pluginDb = new PluginDb();
            $installedPlugins = $pluginDb->fetchInstalledPlugins();

            // 1. Pour la sidebar (layout.tpl)
            $this->view->assign('installed_plugins', $installedPlugins);

            // 2. Amorçage des plugins (Enregistrement des Hooks)
            foreach ($installedPlugins as $plugin) {
                // Le nom de la classe d'amorçage
                $bootClass = "Plugins\\" . $plugin['name'] . "\\Boot";

                if (class_exists($bootClass)) {
                    $bootInstance = new $bootClass();

                    // Si le plugin déclare une méthode register(), on l'appelle
                    if (method_exists($bootInstance, 'register')) {
                        $bootInstance->register();
                    }
                }
            }
        } catch (\Throwable $e) {
            $this->logger->log("Erreur lors de l'initialisation des plugins : " . $e->getMessage(), "warning");
        }
    }
    /**
     * Prépare les plugins pour la sidebar avec double vérification (DB + FileSystem)
     */
    protected function getValidatedPluginsForMenu(): array
    {
        $db = new PluginDb();
        $rawPlugins = $db->fetchInstalledPlugins(); // Liste issue de mc_plugins
        $validatedPlugins = [];

        foreach ($rawPlugins as $plugin) {
            $name = $plugin['name']; // ex: MagixGuestbook

            // 1. Vérification du dossier physique
            $pluginPath = ROOT_DIR . 'plugins' . DS . $name;
            if (!is_dir($pluginPath)) {
                // Le dossier a disparu ? On ignore pour le menu.
                continue;
            }

            // 2. Vérification de l'existence du contrôleur et de la méthode run
            // Le namespace doit correspondre à votre structure PSR-4
            $controllerClass = "Plugins\\{$name}\\src\\BackendController";

            if (class_exists($controllerClass)) {
                // On utilise la réflexion pour vérifier la méthode 'run' sans instancier
                $reflection = new \ReflectionClass($controllerClass);
                if ($reflection->hasMethod('run')) {
                    $validatedPlugins[] = $plugin;
                }
            }
        }

        return $validatedPlugins;
    }

    /**
     * Charge toutes les permissions du rôle de l'utilisateur pour construire la sidebar.
     */
    private function initMenuPermissions(): void
    {
        $idAdmin = (int)$this->session->get('id_admin');
        if ($idAdmin <= 0) return;

        // On récupère l'utilisateur courant pour vérifier s'il est SuperAdmin (id_role = 1)
        $user = $this->view->getTemplateVars('current_user');
        $isSuperAdmin = ($user && (int)$user['id_role'] === 1);
        $this->view->assign('is_super_admin', $isSuperAdmin);

        if (!$isSuperAdmin) {
            $db = new EmployeeDb();
            // 🟢 Vous devrez ajouter cette méthode dans EmployeeDb (voir étape 2)
            $allPerms = $db->getAllModuleAccess($idAdmin);

            $menuPerms = [];
            if ($allPerms) {
                foreach ($allPerms as $p) {
                    $moduleName = strtolower($p['module_name'] ?? '');
                    if ($moduleName) {
                        $menuPerms[$moduleName] = ((int)$p['view'] === 1);
                    }
                }
            }
            $this->view->assign('menu_perms', $menuPerms);
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

    abstract public function run(): void;
}