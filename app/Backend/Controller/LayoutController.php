<?php

declare(strict_types=1);

namespace App\Backend\Controller;

use App\Backend\Db\LayoutDb;
use Magepattern\Component\HTTP\Request;
use Magepattern\Component\Tool\FormTool;
use Magepattern\Component\Tool\SmartyTool;
use App\Component\Cache\CacheManager; // 🟢 Import du gestionnaire de cache

class LayoutController extends BaseController
{
    protected LayoutDb $layoutDb;

    public function __construct()
    {
        parent::__construct();
        $this->layoutDb = new LayoutDb();
    }

    public function run(): void
    {
        $action = $_GET['action'] ?? 'index';
        if (method_exists($this, $action)) {
            $this->$action();
        } else {
            $this->index();
        }
    }

    public function index(): void
    {
        $view = SmartyTool::getInstance('admin');
        $hooks = $this->layoutDb->getAllHooks() ?: [];

        $groupedLayout = [
            'Accueil'               => [],
            'Général & Pages'       => [],
            'Pied de page (Footer)' => []
        ];

        foreach ($hooks as $hook) {
            $hookName = $hook['name'];
            $zoneData = [
                'info' => $hook,
                'items' => $this->layoutDb->getItemsByHook((int)$hook['id_hook']) ?: []
            ];

            if (str_contains($hookName, 'Home')) {
                $groupedLayout['Accueil'][] = $zoneData;
            } elseif (str_contains($hookName, 'Footer')) {
                $groupedLayout['Pied de page (Footer)'][] = $zoneData;
            } else {
                $groupedLayout['Général & Pages'][] = $zoneData;
            }
        }

        $availablePlugins = $this->getAvailablePlugins();

        $view->assign([
            'layout_groups'    => $groupedLayout,
            'layout'           => $hooks,
            'availablePlugins' => $availablePlugins,
            'hashtoken'        => $this->session->getToken()
        ]);

        $view->display('layout/index.tpl');
    }

    private function getAvailablePlugins(): array
    {
        $pluginsDir = ROOT_DIR . 'plugins';
        $plugins = [];

        if (is_dir($pluginsDir)) {
            $items = scandir($pluginsDir);

            foreach ($items as $folder) {
                if ($folder === '.' || $folder === '..') continue;

                $pluginPath = $pluginsDir . DS . $folder;
                $manifestFile = $pluginPath . DS . 'manifest.json';

                if (is_dir($pluginPath) && file_exists($manifestFile)) {
                    $jsonContent = file_get_contents($manifestFile);
                    $manifest = json_decode($jsonContent, true);

                    if (is_array($manifest)) {
                        $hasDefaultHooks = !empty($manifest['default_hooks']) && is_array($manifest['default_hooks']);
                        $isExplicitlyHookable = isset($manifest['hookable']) && $manifest['hookable'] === true;

                        if ($hasDefaultHooks || $isExplicitlyHookable) {
                            $plugins[] = [
                                'technical_name' => $folder,
                                'display_name'   => $manifest['name'] ?? $folder,
                                'description'    => $manifest['description'] ?? ''
                            ];
                        }
                    }
                }
            }
        }

        usort($plugins, function ($a, $b) {
            return strcmp($a['display_name'], $b['display_name']);
        });

        return $plugins;
    }

    public function add(): void
    {
        if (!Request::isMethod('POST')) $this->jsonResponse(false, 'Méthode non autorisée.');

        $token = $_POST['hashtoken'] ?? '';
        if (!$this->session->validateToken($token)) $this->jsonResponse(false, 'Jeton invalide.');

        $idHook = (int)($_POST['id_hook'] ?? 0);
        $moduleName = FormTool::simpleClean($_POST['module_name'] ?? '');

        $rawSlug = $_POST['item_slug'] ?? '';
        $itemSlug = null;
        if (!empty(trim($rawSlug))) {
            $itemSlug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $rawSlug), '-'));
        }

        if ($idHook > 0 && !empty($moduleName)) {
            if ($this->layoutDb->addItem($idHook, $moduleName, $itemSlug)) {

                // 🟢 PURGE DU CACHE
                CacheManager::clearFrontend('layout');

                $this->jsonResponse(true, 'Widget greffé avec succès.');
            }
        }
        $this->jsonResponse(false, 'Erreur lors de l\'ajout.');
    }

    public function delete(): void
    {
        $token = $_GET['hashtoken'] ?? '';
        if (!$this->session->validateToken($token)) $this->jsonResponse(false, 'Jeton invalide.');

        $id = (int)($_GET['id'] ?? 0);
        if ($id > 0 && $this->layoutDb->deleteItem($id)) {

            // 🟢 PURGE DU CACHE
            CacheManager::clearFrontend('layout');

            $this->jsonResponse(true, 'Widget retiré.');
        }
        $this->jsonResponse(false, 'Erreur de suppression.');
    }

    public function toggle(): void
    {
        $token = $_GET['hashtoken'] ?? '';
        if (!$this->session->validateToken($token)) $this->jsonResponse(false, 'Jeton invalide.');

        $id = (int)($_GET['id'] ?? 0);
        if ($id > 0 && $this->layoutDb->toggleActive($id)) {

            // 🟢 PURGE DU CACHE
            CacheManager::clearFrontend('layout');

            $this->jsonResponse(true, 'Statut mis à jour.');
        }
        $this->jsonResponse(false, 'Erreur de mise à jour.');
    }

    public function move(): void
    {
        $token = $_GET['hashtoken'] ?? '';
        if (!$this->session->validateToken($token)) {
            $this->jsonResponse(false, 'Jeton invalide.');
        }

        $id = (int)($_GET['id'] ?? 0);
        $dir = $_GET['direction'] ?? '';

        if ($id > 0 && in_array($dir, ['up', 'down'])) {
            if ($this->layoutDb->moveItem($id, $dir)) {

                // 🟢 PURGE DU CACHE
                CacheManager::clearFrontend('layout');

                $this->jsonResponse(true, "Déplacement $dir effectué !");
            } else {
                $this->jsonResponse(true, "L'élément est déjà à cette extrémité.");
            }
        }
        $this->jsonResponse(false, "Action impossible.");
    }

    public function sort(): void
    {
        $token = $_POST['hashtoken'] ?? '';
        if (!$this->session->validateToken($token)) {
            $this->jsonResponse(false, 'Jeton invalide.');
        }

        $idHook = (int)($_POST['id_hook'] ?? 0);
        $ids = $_POST['order'] ?? [];

        if ($idHook > 0 && is_array($ids) && !empty($ids)) {
            if ($this->layoutDb->reorder($ids, $idHook)) {

                // 🟢 PURGE DU CACHE
                CacheManager::clearFrontend('layout');

                $this->jsonResponse(true, "L'ordre et les zones ont été mis à jour.");
            }
        }

        $this->jsonResponse(false, "Données invalides ou aucun changement.");
    }
}