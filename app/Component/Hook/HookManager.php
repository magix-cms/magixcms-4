<?php

declare(strict_types=1);

namespace App\Component\Hook;

use Magepattern\Component\Database\QueryBuilder;
use Magepattern\Component\Debug\Logger;

class HookManager
{
    /** @var array<string, callable[]> Liste des filtres de données */
    private static array $filters = [];

    /** @var array<string, array<string, callable>> Liste indexée par [NomHook][NomPlugin] */
    private static array $hooks = [];

    /**
     * Un plugin appelle cette méthode pour s'accrocher à un événement
     */
    public static function register(string $hookName, string $pluginName, callable $callback): void
    {
        self::$hooks[$hookName][$pluginName] = $callback;
    }

    /**
     * 1. MÉTHODE POUR SMARTY (Ancienne version / Interne)
     */
    public static function exec(array $params): string
    {
        $hookName = $params['name'] ?? '';

        if (empty($hookName) || !isset(self::$hooks[$hookName])) {
            return '';
        }

        $output = '';
        foreach (self::$hooks[$hookName] as $pluginName => $callback) {
            $output .= call_user_func($callback, $params);
        }

        return $output;
    }

    /**
     * 2. MÉTHODE POUR LES CONTRÔLEURS (Ex: Dashboard)
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
     * NOUVEAU : 3. MÉTHODE SPÉCIFIQUE FRONTEND (Pilotée par la base de données)
     * Récupère les plugins assignés à un hook précis et exécute leur rendu.
     * @param string $hookName
     * @param array $params Variables globales envoyées par Smarty (Langue, URL...)
     */
    public static function execFront(string $hookName, array $params = []): string
    {
        if (empty($hookName)) {
            return '';
        }

        $output = '';
        try {
            $qb = new QueryBuilder();
            $qb->select(['hi.module_name'])
                ->from('mc_hook_item', 'hi')
                ->join('mc_hook', 'h', 'h.id_hook = hi.id_hook')
                ->where('h.name = :hook_name', ['hook_name' => $hookName])
                ->where('hi.active = 1')
                ->orderBy('hi.position', 'ASC');

            // ASTUCE : On crée une classe DB "jetable" à la volée pour utiliser
            // la méthode officielle executeAll($qb) sans générer d'erreurs
            $db = new class extends \App\Frontend\Db\BaseDb {
                public function fetchHookModules(QueryBuilder $qb) {
                    return $this->executeAll($qb);
                }
            };

            $plugins = $db->fetchHookModules($qb);

            if (empty($plugins)) {
                return '';
            }

            /*foreach ($plugins as $plugin) {
                $moduleName = $plugin['module_name'];
                $className = "\\Plugins\\" . $moduleName . "\\src\\FrontendController";

                if (class_exists($className) && method_exists($className, 'renderWidget')) {
                    // On passe précieusement les $params au plugin !
                    $output .= $className::renderWidget($params);
                }
            }*/
            foreach ($plugins as $plugin) {
                $moduleName = $plugin['module_name'];
                $className = "\\Plugins\\" . $moduleName . "\\src\\FrontendController";

                if (class_exists($className)) {
                    if (method_exists($className, 'renderWidget')) {
                        // C'est bon, on exécute !
                        $output .= $className::renderWidget($params);
                    } else {
                        // La classe existe, mais pas la méthode
                        $output .= "\n\n";
                    }
                } else {
                    // La classe n'existe pas (Faute de frappe ou mauvais namespace)
                    $output .= "\n\n";
                }
            }
        } catch (\Throwable $e) {
            Logger::getInstance()->log($e, 'php', 'error');
        }

        return $output;
    }

    /**
     * NOUVEAU : 4. LE PONT POUR SMARTY 5
     * C'est cette fonction qui sera appelée quand Smarty lira {hook name="..."}
     */
    public static function smartyHook(array $params, $template): string
    {
        $hookName = $params['name'] ?? '';

        // 1. On aspire les variables globales du CMS stockées dans Smarty
        $globalVars = [
            'current_lang' => $template->getTemplateVars('current_lang'),
            'site_url'     => $template->getTemplateVars('site_url'),
            'company'      => $template->getTemplateVars('company')
        ];

        // 2. On les fusionne avec les paramètres éventuels du tag {hook}
        $finalParams = array_merge($globalVars, $params);

        // 3. On envoie tout à notre exécuteur de Frontend
        return self::execFront($hookName, $finalParams);
    }
    /**
     * NOUVEAU : 5. ENREGISTRER UN FILTRE
     * Un plugin appelle cette méthode pour modifier des données existantes (ex: Override SQL)
     */
    public static function addFilter(string $filterName, callable $callback): void
    {
        self::$filters[$filterName][] = $callback;
    }

    /**
     * NOUVEAU : 6. DÉCLENCHER UN FILTRE
     * Fait passer une valeur (ex: un tableau vide) à travers tous les plugins accrochés.
     * Chaque plugin modifie la valeur et la retourne pour le plugin suivant.
     * * @param string $filterName Le nom du hook/filtre (ex: 'extendProductList')
     * @param mixed $value La valeur initiale à modifier
     * @param array $params Variables contextuelles éventuelles
     * @return mixed La valeur modifiée par tous les plugins
     */
    public static function triggerFilter(string $filterName, mixed $value, array $params = []): mixed
    {
        if (empty($filterName) || !isset(self::$filters[$filterName])) {
            return $value; // Si aucun plugin n'est accroché, on renvoie la valeur intacte
        }

        // On fait passer la donnée "à la chaîne" dans chaque plugin
        foreach (self::$filters[$filterName] as $callback) {
            $value = call_user_func($callback, $value, $params);
        }

        return $value;
    }
}