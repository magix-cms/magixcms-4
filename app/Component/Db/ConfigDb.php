<?php

declare(strict_types=1);

namespace App\Component\Db;

use Magepattern\Component\Database\QueryBuilder;
use Magepattern\Component\Database\Layer;
use Magepattern\Component\Debug\Logger;

class ConfigDb
{
    /**
     * Exécute une requête et gère les erreurs silencieusement via le Logger
     */
    protected function executeAll(QueryBuilder $qb): array
    {
        try {
            $layer = Layer::getInstance();
            $result = $layer->fetchAll($qb->getSql(), $qb->getParams());
            return is_array($result) ? $result : [];
        } catch (\Throwable $e) {
            Logger::getInstance()->log($e, "php", "error", Logger::LOG_MONTH, Logger::LOG_LEVEL_ERROR);
            return [];
        }
    }

    /**
     * Remplace : context='all', type='imgSize'
     * Récupère les configurations de taille d'image pour un module et attribut précis.
     */
    public function fetchImageSizes(string $module, string $attribute): array
    {
        $qb = new QueryBuilder();
        $qb->select([
            'module_img as module',
            'attribute_img as attribute',
            'width_img as width',
            'height_img as height',
            'type_img as type',
            'prefix_img as prefix',
            'resize_img as resize'
        ])
            ->from('mc_config_img')
            ->where('module_img = :module AND attribute_img = :attribute', [
                'module'    => $module,
                'attribute' => $attribute
            ])
            ->orderBy('width_img', 'ASC');

        return $this->executeAll($qb);
    }

    /**
     * Remplace : context='all', type='configImages'
     * Récupère la liste complète de toutes les configurations d'images.
     */
    public function fetchAllImageConfigs(): array
    {
        $qb = new QueryBuilder();
        $qb->select([
            'module_img as module',
            'attribute_img as attribute',
            'width_img as width',
            'height_img as height',
            'type_img as type',
            'prefix_img as prefix',
            'resize_img as resize'
        ])
            ->from('mc_config_img')
            ->orderBy('module_img', 'ASC')
            ->orderBy('attribute_img', 'ASC')
            ->orderBy('width_img', 'ASC');

        return $this->executeAll($qb);
    }

    /**
     * Remplace : context='all', type='attribute'
     * Récupère la configuration d'un module en excluant un attribut spécifique.
     */
    public function fetchOtherAttributes(string $module, string $excludeAttribute): array
    {
        $qb = new QueryBuilder();
        $qb->select([
            'module_img as module',
            'attribute_img as attribute',
            'width_img as width',
            'height_img as height',
            'type_img as type',
            'resize_img as resize'
        ])
            ->from('mc_config_img')
            ->where('module_img = :module AND attribute_img != :attribute', [
                'module'    => $module,
                'attribute' => $excludeAttribute
            ]);

        return $this->executeAll($qb);
    }

    /**
     * Remplace : context='all', type='config'
     * Récupère la configuration globale du CMS.
     */
    public function fetchGlobalConfig(): array
    {
        $qb = new QueryBuilder();
        $qb->select(['*'])->from('mc_config');
        return $this->executeAll($qb);
    }
}