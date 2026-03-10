<?php

declare(strict_types=1);

namespace App\Frontend\Db;

use Magepattern\Component\Database\QueryBuilder;
use Magepattern\Component\Database\Layer;
use Magepattern\Component\Tool\PaginationTool;
use Magepattern\Component\Debug\Logger;
use Magepattern\Component\File\CacheTool;
use Magepattern\Component\File\FileTool;

abstract class BaseDb
{
    /**
     * Fournit une instance configurée de CacheTool pour le SQL côté Front,
     * et s'assure que le dossier de destination existe via FileTool.
     */
    protected function getSqlCache(): CacheTool
    {
        // Utilisation de la constante définie dans l'index.php public (var/cache/)
        $cacheDir = SQLCACHEDIR . 'sql';

        $securePath = FileTool::createSecureCacheDir($cacheDir);

        return new CacheTool($securePath);
    }

    /**
     * Exécute n'importe quel QueryBuilder, le pagine, et gère les erreurs.
     */
    protected function executePaginatedQuery(QueryBuilder $qb, int $page = 1, int $limit = 25): array|false
    {
        try {
            $paginator = new PaginationTool($limit, $page);
            $meta = $paginator->paginate($qb);

            $layer = Layer::getInstance();
            $data = $layer->fetchAll($qb->getSql(), $qb->getParams());

            return [
                'data' => $data,
                'meta' => $meta
            ];

        } catch (\Throwable $e) {
            Logger::getInstance()->log($e, "critical", "database_errors", Logger::LOG_YEAR, Logger::LOG_LEVEL_ERROR);
            return false;
        }
    }

    /**
     * Exécute une requête qui retourne plusieurs lignes (sans pagination).
     */
    protected function executeAll(QueryBuilder $qb): array|false
    {
        try {
            $layer = Layer::getInstance();
            return $layer->fetchAll($qb->getSql(), $qb->getParams());
        } catch (\Throwable $e) {
            Logger::getInstance()->log($e, "critical", "database_errors", Logger::LOG_YEAR, Logger::LOG_LEVEL_ERROR);
            return false;
        }
    }

    /**
     * Exécute une requête qui ne doit retourner qu'une seule ligne.
     */
    protected function executeRow(QueryBuilder $qb): array|false
    {
        try {
            $layer = Layer::getInstance();
            return $layer->fetch($qb->getSql(), $qb->getParams());
        } catch (\Throwable $e) {
            Logger::getInstance()->log($e, "critical", "database_errors", Logger::LOG_YEAR, Logger::LOG_LEVEL_ERROR);
            return false;
        }
    }

    /**
     * Exécute un UPDATE généré par le QueryBuilder
     */
    protected function executeUpdate(QueryBuilder $qb): bool
    {
        try {
            $sql = $qb->getSql();
            $params = $qb->getParams();

            $layer = Layer::getInstance();
            return $layer->update($sql, $params);
        } catch (\Throwable $e) {
            Logger::getInstance()->log($e, "critical", "database_errors", Logger::LOG_YEAR, Logger::LOG_LEVEL_ERROR);
            return false;
        }
    }

    /**
     * Exécute un INSERT généré par le QueryBuilder
     */
    protected function executeInsert(QueryBuilder $qb): bool
    {
        try {
            $sql = $qb->getSql();
            $params = $qb->getParams();

            $layer = Layer::getInstance();
            return $layer->insert($sql, $params);
        } catch (\Throwable $e) {
            Logger::getInstance()->log($e, "critical", "database_errors", Logger::LOG_YEAR, Logger::LOG_LEVEL_ERROR);
            return false;
        }
    }

    /**
     * Exécute un DELETE généré par le QueryBuilder
     */
    protected function executeDelete(QueryBuilder $qb): bool
    {
        try {
            $sql = $qb->getSql();
            $params = $qb->getParams();

            $layer = Layer::getInstance();
            return $layer->delete($sql, $params);
        } catch (\Throwable $e) {
            Logger::getInstance()->log($e, "critical", "database_errors", Logger::LOG_YEAR, Logger::LOG_LEVEL_ERROR);
            return false;
        }
    }

    /**
     * Récupère le dernier ID inséré en interrogeant la classe Layer
     */
    protected function getLastInsertId(): int
    {
        $layer = Layer::getInstance();
        return (int)$layer->lastInsertId();
    }

    /**
     * Récupère la liste des langues actives indexées par leur ID.
     */
    public function fetchLanguages(): array
    {
        $qb = new QueryBuilder();
        $qb->select(['id_lang', 'iso_lang'])
            ->from('mc_lang')
            ->orderBy('id_lang', 'ASC');

        $rows = $this->executeAll($qb);

        $langs = [];
        if ($rows) {
            foreach ($rows as $row) {
                $langs[(int)$row['id_lang']] = $row['iso_lang'];
            }
        }
        return $langs;
    }
}