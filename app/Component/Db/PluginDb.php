<?php

declare(strict_types=1);

namespace App\Component\Db;

use Magepattern\Component\Database\QueryBuilder;
use Magepattern\Component\Database\Layer;
use Magepattern\Component\Debug\Logger;

class PluginDb
{
    /**
     * Récupère la configuration des cibles (targets) d'un plugin depuis la table mc_plugins.
     *
     * @param string $pluginName Le nom exact du plugin (ex: 'MagixAdvMulti')
     * @return array Retourne un tableau associatif de la ligne, ou un tableau vide
     */
    public function getPluginTargets(string $pluginName): array
    {
        try {
            $qb = new QueryBuilder();
            $qb->select(['*'])
                ->from('mc_plugins')
                ->where('name = :name', ['name' => $pluginName])
                ->limit(1);

            $layer = Layer::getInstance();

            // Utilisation de la méthode fetch() native de votre Layer
            $result = $layer->fetch($qb->getSql(), $qb->getParams());

            // fetch() retourne un array associatif si trouvé, ou false si rien n'est trouvé
            return is_array($result) ? $result : [];

        } catch (\Throwable $e) {
            Logger::getInstance()->log($e, "php", "error", Logger::LOG_MONTH, Logger::LOG_LEVEL_ERROR);
            return [];
        }
    }

    /**
     * Récupère l'ordre d'affichage des widgets pour un hook donné.
     * Permet de synchroniser les instances multiples d'un même plugin (ex: Blog vs Agenda).
     *
     * @param string $hookName Le nom du hook (ex: 'displayHomeTop')
     * @param string $moduleName Le nom du module (ex: 'MagixLastNews')
     * @return array Tableau contenant l'ordre des item_slugs
     */
    public function getWidgetOrder(string $hookName, string $moduleName): array
    {
        try {
            $qb = new QueryBuilder();
            $qb->select(['hi.item_slug'])
                ->from('mc_hook_item', 'hi')
                ->join('mc_hook', 'h', 'hi.id_hook = h.id_hook')
                ->where('h.name = :hook', ['hook' => $hookName])
                ->where('hi.module_name = :module', ['module' => $moduleName])
                ->where('hi.active = 1')
                ->orderBy('hi.position', 'ASC');

            $layer = Layer::getInstance();
            $result = $layer->fetchAll($qb->getSql(), $qb->getParams());

            return is_array($result) ? $result : [];

        } catch (\Throwable $e) {
            Logger::getInstance()->log($e, "php", "error", Logger::LOG_MONTH, Logger::LOG_LEVEL_ERROR);
            return [];
        }
    }
}