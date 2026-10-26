<?php

declare(strict_types=1);

namespace App\Backend\Controller;

use App\Backend\Db\PluginDb;
use App\Backend\Db\DashboardDb;
use App\Backend\Db\LayoutDb;
use App\Backend\Db\ThemeDb;
use Magepattern\Component\File\FileTool;

class PluginController extends BaseController
{
    public function run(): void
    {
        $action = $_GET['action'] ?? 'index';
        if (method_exists($this, $action)) {
            $this->$action();
            return;
        }
        $this->index();
    }

    private function index(): void
    {
        $db = new PluginDb();
        $installed = $db->fetchInstalledPlugins();

        // --- SCAN DU DOSSIER PLUGINS ---
        $pluginDir = ROOT_DIR . 'plugins';
        $folders = array_filter(glob($pluginDir . '/*'), 'is_dir');

        $availablePlugins = [];

        foreach ($folders as $folder) {
            $pluginName = basename($folder);

            $manifestPath = $folder . '/manifest.json';
            $manifest = file_exists($manifestPath) ? json_decode(file_get_contents($manifestPath), true) : [];

            $pluginType = $manifest['type'] ?? 'backend';
            $pluginVersion = $manifest['version'] ?? '1.0.0';

            // ---> AJOUT : On extrait le nom du plugin cible depuis 'plugin_targets' (si c'est un addon)
            $targetName = '';
            if (isset($manifest['plugin_targets']) && is_array($manifest['plugin_targets'])) {
                // array_key_first() récupère la première clé du tableau (ex: "Magixcartpay")
                $targetName = array_key_first($manifest['plugin_targets']) ?: '';
            }

            // Si le plugin est déjà installé, on fusionne les données
            if (isset($installed[$pluginName])) {
                $pluginData = $installed[$pluginName];
                $pluginData['is_installed'] = true;
                $pluginData['type'] = $pluginType;
                $pluginData['version'] = $pluginVersion;
                $pluginData['addon_target'] = $targetName; // <--- NOUVEAU
            } else {
                // Sinon, c'est un nouveau plugin non installé
                $pluginData = [
                    'name'         => $pluginName,
                    'version'      => $pluginVersion,
                    'type'         => $pluginType,
                    'addon_target' => $targetName, // <--- NOUVEAU
                    'is_installed' => false,
                    'home' => 0, 'about' => 0, 'pages' => 0, 'news' => 0, 'catalog' => 0, 'category' => 0, 'product' => 0, 'seo' => 0
                ];
            }
            $availablePlugins[] = $pluginData;
        }

        $this->view->assign([
            'plugins_list' => $availablePlugins,
            'hashtoken'    => $this->session->getToken()
        ]);

        $this->view->display('plugin/index.tpl');
    }

    public function install(): void
    {
        // 1. PROTECTION CSRF : Vérification du hashtoken
        // Si vous appelez ça via AJAX ou un formulaire, assurez-vous d'envoyer le hashtoken
        // Si l'appel reste en GET, récupérez-le via $_GET['hashtoken']
        $token = $_POST['hashtoken'] ?? ($_GET['hashtoken'] ?? '');
        if (!$this->session->validateToken($token)) {
            $this->jsonResponse(false, 'Session invalide ou expirée (CSRF).');
        }

        $rawPluginName = $_GET['name'] ?? ($_POST['name'] ?? '');
        if (empty($rawPluginName)) {
            $this->jsonResponse(false, 'Nom du plugin manquant.');
        }

        // 2. ASSAINISSEMENT STRICT : Uniquement Alphanumérique + Underscore (Ex: MagixModuleNews)
        if (!preg_match('/^[A-Za-z0-9_]+$/', $rawPluginName)) {
            $this->jsonResponse(false, 'Nom de plugin invalide.');
        }
        $pluginName = $rawPluginName;

        $pluginsBaseDir = realpath(ROOT_DIR . 'plugins');
        $pluginPath = $pluginsBaseDir . DS . $pluginName;

        // 3.️ VÉRIFICATION REALPATH : On s'assure qu'on ne sort pas du dossier plugins/
        $resolvedPluginPath = realpath($pluginPath);
        if ($resolvedPluginPath === false || !str_starts_with($resolvedPluginPath, $pluginsBaseDir)) {
            $this->jsonResponse(false, 'Chemin du plugin non autorisé (Path Traversal bloqué).');
        }

        $manifestPath = $resolvedPluginPath . DS . 'manifest.json';
        $sqlPath = $resolvedPluginPath . DS . 'sql' . DS . 'install.sql';

        if (!is_dir($resolvedPluginPath) || !file_exists($manifestPath)) {
            $this->jsonResponse(false, 'Plugin invalide ou manifest.json manquant.');
        }

        $manifest = json_decode(file_get_contents($manifestPath), true) ?: [];
        $db = new PluginDb();

        // 1. Exécution du SQL d'installation
        if (file_exists($sqlPath)) {
            $sqlContent = file_get_contents($sqlPath);
            if (!$db->executeRawSql($sqlContent)) {
                $this->jsonResponse(false, 'Erreur lors de l\'exécution du script SQL d\'installation.');
            }
        }

        $targets = $manifest['core_targets'] ?? [];
        $hasConfig = isset($manifest['has_config']) && $manifest['has_config'] === false ? 0 : 1;

        $data = [
            'name'       => $pluginName,
            'version'    => $manifest['version'] ?? '1.0.0',
            'has_config' => $hasConfig,
            'home'       => $targets['home'] ?? 0,
            'about'      => $targets['about'] ?? 0,
            'pages'      => $targets['pages'] ?? 0,
            'news'       => $targets['news'] ?? 0,
            'catalog'    => $targets['catalog'] ?? 0,
            'category'   => $targets['category'] ?? 0,
            'product'    => $targets['product'] ?? 0,
            'seo'        => $targets['seo'] ?? 0
        ];

        // 3. Insertion dans mc_plugins
        if ($db->insertPlugin($data)) {

            // 4. Liaison dans mc_plugins_module (Core Modules & Plugins tiers)

            // A. Liaison aux modules natifs du Core (ex: 'news', 'contact')
            if (!empty($targets)) {
                foreach ($targets as $moduleName => $isActive) {
                    if ($isActive == 1) {
                        $db->linkPluginToModule($pluginName, $moduleName);
                    }
                }
            }

            // B. Liaison aux autres plugins (ex: 'Magixcartpay')
            $pluginTargets = $manifest['plugin_targets'] ?? [];
            if (!empty($pluginTargets)) {
                foreach ($pluginTargets as $targetPlugin => $isActive) {
                    if ($isActive == 1) {
                        // On vérifie que le plugin cible est bien installé sur le CMS
                        if ($db->isPluginInstalled($targetPlugin)) {
                            // On lie normalement : plugin_name = GoogleRecaptcha, module_name = magixcartpay
                            $db->linkPluginToModule($pluginName, strtolower($targetPlugin));
                        }
                    }
                }
            }

            // 5. Enregistrement RBAC
            $db->registerModuleRBAC($pluginName);

            // 6. GREFFE AUTOMATIQUE DANS LE LAYOUT FRONTEND
            $defaultHooks = $manifest['default_hooks'] ?? [];
            if (!empty($defaultHooks)) {
                $layoutDb = new LayoutDb();
                $allHooks = $layoutDb->getAllHooks() ?: [];

                $hookMap = [];
                foreach ($allHooks as $h) {
                    $hookMap[$h['name']] = (int)$h['id_hook'];
                }

                foreach ($defaultHooks as $hookName) {
                    if (isset($hookMap[$hookName])) {
                        $layoutDb->addItem($hookMap[$hookName], $pluginName);
                    }
                }
            }

            // 7. COPIE DES ASSETS (CSS & JS) VERS LE SKIN COURANT S'ILS EXISTENT
            $pluginCssDir = $pluginPath . DS . 'public' . DS . 'css';
            $pluginJsDir  = $pluginPath . DS . 'public' . DS . 'js';

            // On vérifie si au moins l'un des deux dossiers existe avant d'interroger la base de données
            if (is_dir($pluginCssDir) || is_dir($pluginJsDir)) {

                // Instanciation de votre DB pour récupérer le thème courant (fait une seule fois)
                $themeDb = new ThemeDb();
                $currentSkin = $themeDb->getCurrentTheme();

                // Copie du CSS
                if (is_dir($pluginCssDir)) {
                    $skinCssDir = ROOT_DIR . 'skin' . DS . $currentSkin . DS . 'css';
                    FileTool::copyDirectory($pluginCssDir, $skinCssDir);
                }

                // Copie du JS
                if (is_dir($pluginJsDir)) {
                    $skinJsDir = ROOT_DIR . 'skin' . DS . $currentSkin . DS . 'js';
                    FileTool::copyDirectory($pluginJsDir, $skinJsDir);
                }
            }

            $hasConfig = $manifest['has_config'] ?? false;

            $this->jsonResponse(true, 'Le plugin a été installé avec succès !', [
                'type' => 'install_success',
                'has_config' => $hasConfig,
                'plugin_name' => $pluginName
            ]);
        }

        $this->jsonResponse(false, 'Erreur lors de l\'enregistrement du plugin dans la base de données.');
    }

    public function uninstall(): void
    {
        // 1. PROTECTION CSRF
        $token = $_POST['hashtoken'] ?? ($_GET['hashtoken'] ?? '');
        if (!$this->session->validateToken($token)) {
            $this->jsonResponse(false, 'Session invalide ou expirée (CSRF).');
        }

        $rawPluginName = $_GET['name'] ?? ($_POST['name'] ?? '');
        if (empty($rawPluginName)) {
            $this->jsonResponse(false, 'Nom du plugin manquant.');
        }

        // 2. ASSAINISSEMENT STRICT
        if (!preg_match('/^[A-Za-z0-9_]+$/', $rawPluginName)) {
            $this->jsonResponse(false, 'Nom de plugin invalide.');
        }
        $pluginName = $rawPluginName;

        $pluginsBaseDir = realpath(ROOT_DIR . 'plugins');
        $pluginPath = $pluginsBaseDir . DS . $pluginName;

        $resolvedPluginPath = realpath($pluginPath);
        if ($resolvedPluginPath === false || !str_starts_with($resolvedPluginPath, $pluginsBaseDir)) {
            $this->jsonResponse(false, 'Chemin du plugin non autorisé.');
        }

        $sqlPath = $resolvedPluginPath . DS . 'sql' . DS . 'uninstall.sql';

        $db = new PluginDb();

        // 1. Exécution du SQL de désinstallation (Suppression des tables propres au plugin)
        if (file_exists($sqlPath)) {
            $sqlContent = file_get_contents($sqlPath);
            if (!empty(trim($sqlContent))) {
                if (!$db->executeRawSql($sqlContent)) {
                    $this->logger->log("Avertissement : Le script uninstall.sql de {$pluginName} a rencontré une erreur.", 'warning');
                }
            }
        }

        // 2. Nettoyage des tables Core du CMS
        $success = $db->deletePlugin($pluginName); // Supprime de mc_plugins

        if ($success) {
            // A. Nettoyage des liaisons Modules & Permissions
            $db->unlinkPluginFromAllModules($pluginName); // Supprime de mc_plugins_module
            $db->unregisterModuleRBAC($pluginName);       // Nettoie les droits d'accès

            // B. Nettoyage du Dashboard Backend
            $dashDb = new DashboardDb();
            $dashDb->removeWidgetGlobally($pluginName);

            // C. Nettoyage du Layout Frontend
            $db->removePluginFromFrontendLayout($pluginName);

            // D. Nettoyage du dossier Upload associé (Ex: /upload/contact/)
            $uploadDir = ROOT_DIR . 'upload' . DS . strtolower($pluginName);
            if (is_dir($uploadDir)) {
                FileTool::remove($uploadDir);
            }

            $this->jsonResponse(true, 'Le plugin a été désinstallé avec succès.', ['type' => 'uninstall_success']);
        } else {
            $this->jsonResponse(false, 'Erreur lors de la suppression des données du plugin.');
        }
    }
}