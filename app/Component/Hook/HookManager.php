<?php

declare(strict_types=1);

namespace App\Component\Hook;

use Magepattern\Component\Database\QueryBuilder;
use Magepattern\Component\Debug\Logger;
use App\Component\Cache\CacheManager;

class HookManager
{
    /** @var array<string, callable[]> Liste des filtres de données */
    private static array $filters = [];

    /** @var array<string, array<string, callable>> Liste indexée par [NomHook][NomPlugin] */
    private static array $hooks = [];

    /** @var array Cache mémoire interne pour la durée de l'exécution de la page */
    private static array $dbCache = [];

    /**
     * Un plugin appelle cette méthode pour s'accrocher à un événement
     */
    public static function register(string $hookName, string $pluginName, callable $callback): void
    {
        self::$hooks[$hookName][$pluginName] = $callback;
    }

    /**
     * MÉTHODE POUR SMARTY / INTERNE
     */
    public static function exec(array $params): string
    {
        $hookName = $params['name'] ?? '';

        if (empty($hookName) || !isset(self::$hooks[$hookName])) {
            return '';
        }

        $output = '';
        foreach (self::$hooks[$hookName] as $pluginName => $callback) {
            $output .= (string)call_user_func($callback, $params);
        }

        return $output;
    }

    /**
     * MÉTHODE POUR LES CONTRÔLEURS (Ex: Dashboard)
     */
    public static function execToArray(string $hookName, array $params = []): array
    {
        if (empty($hookName) || !isset(self::$hooks[$hookName])) {
            return [];
        }

        $results = [];
        foreach (self::$hooks[$hookName] as $pluginName => $callback) {
            $results[$pluginName] = call_user_func($callback, $params);
        }

        return $results;
    }

    /**
     * MÉTHODE SPÉCIFIQUE FRONTEND
     * Gère le cache (mémoire + SQL) et la transmission du slug d'instance.
     */
    public static function execFront(string $hookName, array $params = []): string
    {
        if (empty($hookName)) {
            return '';
        }

        $output = '';
        $executedPlugins = [];

        try {
            // 1. GESTION DU CACHE
            if (!isset(self::$dbCache[$hookName])) {
                $cacheKey = 'layout_hook_' . $hookName;
                $cache = CacheManager::get();
                $plugins = null;

                // Tentative de lecture du cache persistant (SQL/Files)
                if ($cache) {
                    $plugins = $cache->get($cacheKey);
                }

                // Si pas de cache, on interroge la base de données
                if ($plugins === null || !is_array($plugins)) {
                    $qb = new QueryBuilder();
                    $qb->select(['hi.module_name', 'hi.active', 'hi.item_slug'])
                        ->from('mc_hook_item', 'hi')
                        ->join('mc_hook', 'h', 'h.id_hook = hi.id_hook')
                        ->where('h.name = :hook_name', ['hook_name' => $hookName])
                        ->orderBy('hi.position', 'ASC');

                    // Utilisation d'une classe anonyme pour l'exécution DB isolée
                    $db = new class extends \App\Frontend\Db\BaseDb {
                        public function fetchHookModules(QueryBuilder $qb) {
                            return $this->executeAll($qb);
                        }
                    };

                    $plugins = $db->fetchHookModules($qb) ?: [];

                    // Mise en cache persistant pour les prochains visiteurs
                    if ($cache) {
                        $cache->set($cacheKey, $plugins);
                    }
                }

                // Stockage dans le cache mémoire (durée de vie du script PHP actuel)
                self::$dbCache[$hookName] = $plugins;
            }

            $plugins = self::$dbCache[$hookName];

            // 2. EXÉCUTION DES MODULES
            if (!empty($plugins)) {
                foreach ($plugins as $plugin) {
                    $moduleName = $plugin['module_name'];
                    $executedPlugins[] = $moduleName;

                    if ((int)$plugin['active'] === 1) {
                        // On prépare les paramètres avec le slug d'instance
                        $pluginParams = array_merge($params, [
                            'instance_slug' => !empty($plugin['item_slug']) ? $plugin['item_slug'] : 'default'
                        ]);

                        // A. Priorité au callback enregistré (Boot.php)
                        if (isset(self::$hooks[$hookName][$moduleName])) {
                            $output .= (string)call_user_func(self::$hooks[$hookName][$moduleName], $pluginParams);
                        }
                        // B. Sinon, appel automatique au Controller du plugin
                        else {
                            $className = "\\Plugins\\" . $moduleName . "\\src\\FrontendController";
                            if (class_exists($className) && method_exists($className, 'renderWidget')) {
                                $output .= (string)$className::renderWidget($pluginParams);
                            }
                        }
                    }
                }
            }

            // 3. EXÉCUTION DES PLUGINS STATIQUES (Non présents en DB)
            if (isset(self::$hooks[$hookName])) {
                foreach (self::$hooks[$hookName] as $moduleName => $callback) {
                    if (!in_array($moduleName, $executedPlugins)) {
                        $output .= (string)call_user_func($callback, $params);
                    }
                }
            }

        } catch (\Throwable $e) {
            Logger::getInstance()->log($e, 'php', 'error');
        }

        return $output;
    }

    /**
     * PONT POUR SMARTY
     */
    public static function smartyHook(array $params, $template): string
    {
        $hookName = $params['name'] ?? '';

        $globalVars = [
            'current_lang' => $template->getTemplateVars('current_lang'),
            'site_url'     => $template->getTemplateVars('site_url'),
            'company'      => $template->getTemplateVars('company'),
            'mc_settings'  => $template->getTemplateVars('mc_settings')
        ];

        $finalParams = array_merge($globalVars, $params);

        return self::execFront($hookName, $finalParams);
    }

    /**
     * ENREGISTRER UN FILTRE (Hooks de données)
     */
    public static function addFilter(string $filterName, callable $callback): void
    {
        self::$filters[$filterName][] = $callback;
    }

    /**
     * DÉCLENCHER UN FILTRE
     */
    public static function triggerFilter(string $filterName, mixed $value, array $params = []): mixed
    {
        if (empty($filterName) || !isset(self::$filters[$filterName])) {
            return $value;
        }

        foreach (self::$filters[$filterName] as $callback) {
            $value = call_user_func($callback, $value, $params);
        }

        return $value;
    }
}