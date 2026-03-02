<?php

declare(strict_types=1);

namespace App\Backend\Controller;

use App\Backend\Model\DataHelperTrait;
use App\Backend\Db\LangDb; // <-- On importe LangDb
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

        // --- NOUVEAU : Définition du contrôleur actif pour les menus Smarty ---
        $currentController = $_GET['controller'] ?? 'Dashboard';
        // On le nettoie de la même façon que dans index.php
        $cleanController = ucfirst(strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $currentController)));

        // On assigne la variable globale à la vue
        $this->view->assign('controller', $cleanController);

        // On charge la langue par défaut dès l'initialisation du contrôleur
        $this->initDefaultLanguage();

        // 1. Initialisation de la session Magepattern
        $this->session = new Session(false);

        // 2. Le Guard : On vérifie l'accès AVANT de faire quoi que ce soit d'autre
        if ($this->requireAuth) {
            $this->checkAuthentication($this->session);
        }

        // 3. Initialisation des langues (comme on l'a vu précédemment)
        $this->initTranslations($this->session);
        /**
         *
         */
        $this->json = new JSON();
    }
    /**
     * Vérifie si la session admin existe, sinon redirige.
     */
    /**
     * @param Session $session
     * @return void
     */
    private function checkAuthentication(Session $session): void
    {
        // On vérifie par exemple la présence de l'ID et de la clé unique
        if (!$session->get('id_admin') || !$session->get('keyuniqid_admin')) {
            // Destruction par sécurité s'il reste des miettes de session
            $session->destroy();

            // Redirection vers la page de login
            header('Location: index.php?controller=Login');
            exit;
        }
    }
    /**
     * Charge les langues et les assigne à Smarty
     */
    private function initDefaultLanguage(): void
    {
        $langDb = new LangDb();

        // 1. Chargement de la langue par défaut (ton code existant)
        $langData = $langDb->getDefaultLanguage();

        if ($langData !== false) {
            $this->defaultLang = $langData;
            $this->view->assign('default_lang', $this->defaultLang);
        } else {
            // Fallback de sécurité
            $this->defaultLang = ['id_lang' => 1, 'iso_lang' => 'fr'];
        }

        // 2. NOUVEAU : Chargement global du tableau pour les formulaires (dropdown-lang.tpl)
        $allLangs = $langDb->getFrontendLanguages();

        // Sécurité au cas où la table serait vide
        if (empty($allLangs)) {
            $allLangs = [$this->defaultLang['id_lang'] => $this->defaultLang['iso_lang']];
        }

        // On assigne $langs globalement à Smarty !
        $this->view->assign('langs', $allLangs);
    }

    /**
     * @return void
     */
    /**
     * @param $session
     * @return void
     */
    protected function initTranslations($session): void
    {

        // 1. Détection d'un changement explicite via l'URL (?lang=en)
        if (isset($_GET['lang']) && !empty($_GET['lang'])) {
            $newLang = preg_replace('/[^a-z]/', '', $_GET['lang']); // Sécurité

            // On vérifie avec le préfixe "local_"
            if (file_exists(ROOT_DIR. BASEADMIN. DS . 'templates' . DS . 'i18n' . DS . 'local_' . $newLang . '.conf')) {
                $session->set('admin_lang', $newLang);
            }
        }

        // 2. Récupération de la langue finale (Session ou défaut 'fr')
        $adminLocale = $session->get('admin_lang', 'fr');

        // 3. Empilement des fichiers de configuration Smarty
        try {
            // CORRECTION ICI : On ajoute 'local_' au nom du fichier à charger
            $this->view->configLoad('local_' . $adminLocale . '.conf');

            $this->stackPluginsTranslations($adminLocale);

        } catch (\Exception $e) {
            $this->logger->log("Erreur chargement i18n : " . $e->getMessage(), "warning");
        }

        // On passe la locale à la vue pour les attributs HTML
        $this->view->assign('admin_locale', $adminLocale);
    }

    /**
     * @param string $locale
     * @return void
     * @throws \Exception
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
            // Sécurité si le dossier plugins n'est pas lisible
            $this->logger->log($e->getMessage(), "php", "error");
        }
    }
    /**
     * Envoie une réponse JSON proprement formatée et arrête le script.
     */
    protected function jsonResponse(bool $status, string $message, array $data = []): void
    {
        $payload = array_merge([
            'status'  => $status,
            'message' => $message,
            'time'    => time()
        ], $data);

        header('Content-Type: application/json; charset=utf-8');
        echo $this->json->encode($payload);
        exit;
    }
    abstract public function run(): void;
}