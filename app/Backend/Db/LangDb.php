<?php

declare(strict_types=1);

namespace App\Backend\Db;

use Magepattern\Component\Database\QueryBuilder;
use Magepattern\Component\File\CacheTool;

class LangDb extends BaseDb
{
    /**
     * Récupère la langue par défaut du système.
     * @return array|false Retourne ['id_lang' => X, 'iso_lang' => 'XX'] ou false
     */
    public function getDefaultLanguage(): array|false
    {
        $cache = $this->getSqlCache();
        $qb = new QueryBuilder();

        $qb->select(['id_lang', 'iso_lang'])
            ->from('mc_lang', 'lang')
            ->where('lang.default_lang = 1', []);

        $cacheKey = $cache->generateKey($qb->getSql(), $qb->getParams(), 'lang');
        $cachedData = $cache->get($cacheKey);

        if ($cachedData !== null) {
            return $cachedData;
        }

        $data = $this->executeRow($qb);

        if ($data !== false) {
            $cache->set($cacheKey, $data, 86400); // Mise en cache pour 24h
        }

        return $data;
    }

    /**
     * Définit une nouvelle langue par défaut
     */
    public function updateDefaultLanguage(int $newIdLang): bool
    {
        // 1. On remet toutes les langues à 0
        $qbReset = new QueryBuilder();
        $qbReset->update('mc_lang', ['default_lang' => 0]);
        $this->executeUpdate($qbReset);

        // 2. On met la nouvelle langue à 1
        $qbSet = new QueryBuilder();
        $qbSet->update('mc_lang', ['default_lang' => 1])
            ->where('id_lang = :id', ['id' => $newIdLang]);

        $success = $this->executeUpdate($qbSet);

        if ($success) {
            // Nettoyage du cache
            $cacheDir = SQLCACHEADMIN . 'var/sql';
            $cache = new CacheTool($cacheDir);
            $cache->clearByTag('lang');
        }

        return $success;
    }

    /**
     * Récupère toutes les langues pour le frontend formatées pour le dropdown.
     */
    public function getFrontendLanguages(): array
    {
        $qb = new QueryBuilder();
        $qb->select(['l.id_lang', 'l.iso_lang', 'l.name_lang', 'l.default_lang'])
            ->from('mc_lang', 'l')
            ->orderBy('l.default_lang', 'DESC')
            ->orderBy('l.id_lang', 'ASC');

        $result = $this->executeAll($qb);

        if (!$result) {
            return [];
        }

        $langs = [];
        foreach ($result as $row) {
            $langs[$row['id_lang']] = $row['iso_lang'];
        }

        return $langs;
    }

    /**
     * Compte le nombre de langues disponibles/actives.
     */
    public function countActiveLanguages(): int
    {
        $qb = new QueryBuilder();
        $qb->select(['COUNT(id_lang) AS total'])
            ->from('mc_lang')
            ->where('active_lang = 1');

        $result = $this->executeRow($qb);
        return $result ? (int)$result['total'] : 0;
    }

    /**
     * Récupère toutes les langues actives pour les lier aux domaines
     */
    public function fetchActiveLanguages(): array
    {
        $qb = new QueryBuilder();
        $qb->select('*')
            ->from('mc_lang')
            ->where('active_lang = 1')
            ->orderBy('name_lang', 'ASC');

        return $this->executeAll($qb) ?: [];
    }

    // --- PARTIE CRUD POUR L'ADMINISTRATION (LangController) ---

    /**
     * Récupère la liste paginée des langues pour le tableau de bord
     */
    public function fetchAllAdminLanguages(int $page = 1, int $limit = 25, array $search = []): array|false
    {
        $qb = new QueryBuilder();
        $qb->select('*')->from('mc_lang');

        // Tri par défaut : les langues par défaut en premier, puis actives, puis ordre alphabétique
        $qb->orderBy('default_lang', 'DESC')
            ->orderBy('active_lang', 'DESC')
            ->orderBy('name_lang', 'ASC');

        return $this->executePaginatedQuery($qb, $page, $limit);
    }

    /**
     * Récupère une langue par son ID
     */
    public function fetchLanguageById(int $id): array|false
    {
        $qb = new QueryBuilder();
        $qb->select('*')->from('mc_lang')->where('id_lang = :id', ['id' => $id]);
        return $this->executeRow($qb);
    }

    /**
     * Ajoute une nouvelle langue
     */
    public function insertLanguage(array $data): int|false
    {
        $qb = new QueryBuilder();
        $qb->insert('mc_lang', $data);
        if ($this->executeInsert($qb)) {
            $this->getSqlCache()->clearByTag('lang'); // Nettoyage du cache
            return $this->getLastInsertId();
        }
        return false;
    }

    /**
     * Met à jour une langue existante
     */
    public function updateLanguage(int $id, array $data): bool
    {
        $qb = new QueryBuilder();
        $qb->update('mc_lang', $data)->where('id_lang = :id', ['id' => $id]);

        $success = $this->executeUpdate($qb);
        if ($success) {
            $this->getSqlCache()->clearByTag('lang'); // Nettoyage du cache
        }
        return $success;
    }

    /**
     * Supprime une ou plusieurs langues
     */
    public function deleteLanguage(array $ids): bool
    {
        if (empty($ids)) return false;

        $qb = new QueryBuilder();
        $qb->delete('mc_lang')->whereIn('id_lang', $ids);

        $success = $this->executeDelete($qb);
        if ($success) {
            $this->getSqlCache()->clearByTag('lang'); // Nettoyage du cache
        }
        return $success;
    }
}