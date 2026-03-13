<?php

declare(strict_types=1);

namespace App\Backend\Controller;

use App\Backend\Db\PluginDb;
use App\Backend\Db\DashboardDb;
use App\Backend\Db\LayoutDb;

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

            // Si le plugin est déjà installé, on récupère ses données BDD
            if (isset($installed[$pluginName])) {
                $pluginData = $installed[$pluginName];
                $pluginData['is_installed'] = true;
            } else {
                // Sinon, c'est un nouveau plugin. On tente de lire son manifest.json
                $manifestPath = $folder . '/manifest.json';
                $manifest = file_exists($manifestPath) ? json_decode(file_get_contents($manifestPath), true) : null;

                $pluginData = [
                    'name'         => $pluginName,
                    'version'      => $manifest['version'] ?? '1.0.0',
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
        $pluginName = $_GET['name'] ?? '';
        if (empty($pluginName)) {
            $this->jsonResponse(false, 'Nom du plugin manquant.');
        }

        $pluginPath = ROOT_DIR . 'plugins' . DS . $pluginName;
        $manifestPath = $pluginPath . DS . 'manifest.json';
        $sqlPath = $pluginPath . DS . 'sql' . DS . 'install.sql';

        if (!is_dir($pluginPath) || !file_exists($manifestPath)) {
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
        $data = [
            'name'    => $pluginName,
            'version' => $manifest['version'] ?? '1.0.0',
            'home'    => $targets['home'] ?? 0,
            'about'   => $targets['about'] ?? 0,
            'pages'   => $targets['pages'] ?? 0,
            'news'    => $targets['news'] ?? 0,
            'catalog' => $targets['catalog'] ?? 0,
            'category'=> $targets['category'] ?? 0,
            'product' => $targets['product'] ?? 0,
            'seo'     => $targets['seo'] ?? 0
        ];

        // 3. Insertion dans mc_plugins
        if ($db->insertPlugin($data)) {

            // 4. Liaison dans mc_plugins_module
            if (!empty($targets)) {
                foreach ($targets as $moduleName => $isActive) {
                    if ($isActive == 1) {
                        $db->linkPluginToModule($pluginName, $moduleName);
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
        $pluginName = $_GET['name'] ?? ($_POST['name'] ?? '');
        if (empty($pluginName)) {
            $this->jsonResponse(false, 'Nom du plugin manquant.');
        }

        $pluginPath = ROOT_DIR . 'plugins' . DS . $pluginName;
        $sqlPath = $pluginPath . DS . 'sql' . DS . 'uninstall.sql';

        $db = new PluginDb();

        // 2. Exécution du SQL de désinstallation
        if (file_exists($sqlPath)) {
            $sqlContent = file_get_contents($sqlPath);
            if (!empty(trim($sqlContent))) {
                if (!$db->executeRawSql($sqlContent)) {
                    $this->logger->log("Avertissement : Le script uninstall.sql de {$pluginName} a rencontré une erreur.", 'warning');
                }
            }
        }

        // 3. Nettoyage des tables du CMS
        $success = $db->deletePlugin($pluginName);

        $db->unlinkPluginFromAllModules($pluginName);
        $db->unregisterModuleRBAC($pluginName);

        if ($success) {
            // Nettoyage du Dashboard Backend
            $dashDb = new DashboardDb();
            $dashDb->removeWidgetGlobally($pluginName);

            // 🟢 APPEL PROPRE AU MODÈLE
            $db->removePluginFromFrontendLayout($pluginName);

            $this->jsonResponse(true, 'Le plugin a été désinstallé avec succès.', ['type' => 'uninstall_success']);
        } else {
            $this->jsonResponse(false, 'Erreur lors de la suppression des données du plugin.');
        }
    }
}