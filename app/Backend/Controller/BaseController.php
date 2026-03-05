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
use Smarty\Smarty;
use Magepattern\Component\Debug\Logger;
use Magepattern\Component\HTTP\Session;
use Magepattern\Component\HTTP\JSON;

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

    public function __construct()
    {
        $this->view = SmartyTool::getInstance('admin');
        $this->logger = Logger::getInstance();

        // --- Définition du contrôleur actif pour les menus Smarty ---
        $currentController = $_GET['controller'] ?? 'Dashboard';
        $cleanController = ucfirst(strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $currentController)));

        // On assigne la variable globale à la vue
        $this->view->assign('controller', $cleanController);

        // 1. On charge la langue par défaut
        $this->initDefaultLanguage();

        // 2. On charge la configuration globale de l'entreprise (mc_company_info)
        $this->initCompanyData();

        // 3. NOUVEAU : On charge la configuration globale du site (Feature Toggles)
        $this->initGlobalConfig();

        // 3. Initialisation de la session Magepattern
        $this->session = new Session(false);

        // 4. Le Guard : On vérifie l'accès ET les permissions
        if ($this->requireAuth) {
            $this->checkAuthentication($this->session);

            // --- Vérification stricte des droits du rôle (RBAC) ---
            $this->checkPermissions($cleanController);

            $this->initCurrentUser();
        }

        // 5. Initialisation des traductions (i18n)
        $this->initTranslations($this->session);

        $this->json = new JSON();
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

        if (!is_dir($pluginsPath)) {
            return;
        }

        try {
            foreach (FileTool::getDirectories($pluginsPath) as $item) {
                if ($item->isDir()) {
                    $pluginConf = $item->getPathname() . DS . 'i18n' . DS . $locale . '.conf';

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