<?php

declare(strict_types=1);

namespace App\Component\Hook;

class HookManager
{
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
     * 1. MÉTHODE POUR SMARTY {hook name="..."}
     * Concatène tous les retours en une seule chaîne de texte (HTML).
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
     * Retourne un tableau associatif ['NomDuPlugin' => 'HTML'] pour permettre le tri.
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
}