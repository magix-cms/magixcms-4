<?php

declare(strict_types=1);

namespace App\Backend\Db;

use Magepattern\Component\Database\QueryBuilder;
use Magepattern\Component\Database\Layer;
use Magepattern\Component\Tool\PaginationTool;
use Magepattern\Component\Debug\Logger;
use Magepattern\Component\File\CacheTool;
use Magepattern\Component\File\FileTool;

abstract class BaseDb
{
    /**
     * Fournit une instance configurée de CacheTool pour le SQL,
     * et s'assure que le dossier de destination existe via FileTool.
     */
    protected function getSqlCache(): CacheTool
    {
        $cacheDir = SQLCACHEADMIN . 'var/sql';

        // Utilisation de la méthode spécifique au cache de ton FileTool.
        // Elle va faire le mkdir() ET créer le .htaccess de sécurité.
        $securePath = FileTool::createSecureCacheDir($cacheDir);

        return new CacheTool($securePath);
    }
    /**
     * Exécute n'importe quel QueryBuilder, le pagine, et gère les erreurs.
     */
    protected function executePaginatedQuery(QueryBuilder $qb, int $page = 1, int $limit = 25): array|false
    {
        try {
            // 1. Pagination automatique
            $paginator = new PaginationTool($limit, $page);
            $meta = $paginator->paginate($qb);

            // 2. Exécution SQL
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
     * Exécute une requête qui ne doit retourner qu'une seule ligne (sans pagination).
     */
    protected function executeRow(QueryBuilder $qb): array|false
    {
        try {
            $layer = Layer::getInstance();

            // Correction : on utilise bien fetch() pour une seule ligne
            return $layer->fetch($qb->getSql(), $qb->getParams());

        } catch (\Throwable $e) {
            Logger::getInstance()->log($e, "critical", "database_errors", Logger::LOG_YEAR, Logger::LOG_LEVEL_ERROR);
            return false;
        }
    }
    /**
     * Exécute un UPDATE généré par le QueryBuilder
     */
    /**
     * @param QueryBuilder $qb
     * @return bool
     */
    protected function executeUpdate(QueryBuilder $qb): bool
    {
        // On extrait le SQL et les paramètres
        $sql = $qb->getSql();
        $params = $qb->getParams();
        $layer = Layer::getInstance();
        // On utilise la méthode native de ton Layer
        // Adapte $this->layer par la variable que tu utilises dans BaseDb
        return $layer->update($sql, $params);
    }

    /**
     * Exécute un INSERT généré par le QueryBuilder
     */
    /**
     * @param QueryBuilder $qb
     * @return bool
     */
    protected function executeInsert(QueryBuilder $qb): bool
    {
        $sql = $qb->getSql();
        $params = $qb->getParams();

        $layer = Layer::getInstance();
        return $layer->insert($sql, $params);
    }

    /**
     * Exécute un DELETE généré par le QueryBuilder
     */
    /**
     * @param QueryBuilder $qb
     * @return bool
     */
    protected function executeDelete(QueryBuilder $qb): bool
    {
        $sql = $qb->getSql();
        $params = $qb->getParams();

        $layer = Layer::getInstance();
        return $layer->delete($sql, $params);
    }
    /**
     * Récupère le dernier ID inséré en interrogeant la classe Layer
     */
    /**
     * @return int
     */
    protected function getLastInsertId(): int
    {
        $layer = Layer::getInstance();

        // Note : Si la méthode dans Layer s'appelle 'getLastInsertId' au lieu de 'lastInsertId', adaptez simplement ce nom.
        // (lastInsertId est le nom standard natif de PDO)
        return (int)$layer->lastInsertId();
    }
    /**
     * Récupère la structure d'une table (DESCRIBE)
     */
    /**
     * Récupère la structure d'une table et la formate pour le DataHelperTrait
     */
    public function getTableScheme(string $tableName): array
    {
        try {
            $layer = Layer::getInstance();
            $columns = $layer->fetchAll("SHOW COLUMNS FROM " . $tableName);

            $formattedScheme = [];
            if ($columns) {
                foreach ($columns as $col) {
                    // On mappe 'Field' vers 'column' et 'Type' vers 'type'
                    $formattedScheme[] = [
                        'column' => $col['Field'],
                        'type'   => $col['Type']
                    ];
                }
            }
            return $formattedScheme;
        } catch (\Throwable $e) {
            Logger::getInstance()->log($e, "critical", "database_errors", Logger::LOG_YEAR, Logger::LOG_LEVEL_ERROR);
            return [];
        }
    }
    /**
     * Récupère la liste des langues actives indexées par leur ID.
     * Accessible par toutes les classes qui étendent BaseDb.
     */
    /**
     * @return array
     */
    public function fetchLanguages(): array
    {
        $qb = new QueryBuilder();
        $qb->select(['id_lang', 'iso_lang'])
            ->from('mc_lang')
            ->orderBy('id_lang', 'ASC');

        // executeAll() doit bien sûr exister dans BaseDb
        $rows = $this->executeAll($qb);

        $langs = [];
        if ($rows) {
            foreach ($rows as $row) {
                $langs[(int)$row['id_lang']] = $row['iso_lang'];
            }
        }
        return $langs;
    }
    /**
     * Exécute un script SQL brut (ex: fichiers d'installation .sql avec requêtes multiples)
     */
    public function executeRawSql(string $sql): bool
    {
        try {
            $layer = Layer::getInstance();

            // On utilise la méthode exec() native si elle est exposée par votre Layer
            // (exec est idéal pour les CREATE TABLE, DROP, etc., car il gère les requêtes multiples)
            if (method_exists($layer, 'exec')) {
                $layer->exec($sql);
            } else {
                // Si votre Layer n'expose que query(), on l'utilise à la place
                $layer->query($sql);
            }

            return true;
        } catch (\Throwable $e) {
            Logger::getInstance()->log($e, "critical", "database_errors", Logger::LOG_YEAR, Logger::LOG_LEVEL_ERROR);
            return false;
        }
    }
}