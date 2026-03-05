<?php

declare(strict_types=1);

namespace App\Backend\Db;

use Magepattern\Component\Database\QueryBuilder;

class DomainDb extends BaseDb
{
    /**
     * Récupère la liste paginée des domaines (pour le tableau générique)
     */
    public function fetchAllDomains(int $page = 1, int $limit = 25, array $search = []): array|false
    {
        $qb = new QueryBuilder();
        $qb->select('*')->from('mc_domain');

        // Note: pas de tri 'order' spécifique car pas de drag&drop ici, on trie par ID
        $qb->orderBy('id_domain', 'DESC');

        return $this->executePaginatedQuery($qb, $page, $limit);
    }

    /**
     * Récupère un domaine par son ID
     */
    public function fetchDomainById(int $id): array|false
    {
        $qb = new QueryBuilder();
        $qb->select('*')->from('mc_domain')->where('id_domain = :id', ['id' => $id]);
        return $this->executeRow($qb);
    }

    /**
     * Insertion d'un nouveau domaine
     */
    public function insertDomain(array $data): int|false
    {
        $qb = new QueryBuilder();
        $qb->insert('mc_domain', $data);
        if ($this->executeInsert($qb)) {
            return $this->getLastInsertId();
        }
        return false;
    }

    /**
     * Mise à jour d'un domaine
     */
    public function updateDomain(int $id, array $data): bool
    {
        $qb = new QueryBuilder();
        $qb->update('mc_domain', $data)->where('id_domain = :id', ['id' => $id]);
        return $this->executeUpdate($qb);
    }

    /**
     * Suppression d'un ou plusieurs domaines
     */
    public function deleteDomain(array $ids): bool
    {
        if (empty($ids)) return false;

        // On supprime d'abord les liaisons de langues pour éviter les orphelins
        $qbLangs = new QueryBuilder();
        $qbLangs->delete('mc_domain_language')->whereIn('id_domain', $ids);
        $this->executeDelete($qbLangs);

        $qb = new QueryBuilder();
        $qb->delete('mc_domain')->whereIn('id_domain', $ids);
        return $this->executeDelete($qb);
    }

    // --- MODULES CONFIG (mc_config) ---

    /**
     * Récupère l'état d'activation des modules sous forme de tableau clé => valeur
     */
    public function fetchModulesConfig(): array
    {
        $qb = new QueryBuilder();
        $qb->select('*')->from('mc_config');
        $results = $this->executeAll($qb);

        $configs = [];
        if ($results) {
            foreach ($results as $row) {
                $configs[$row['attr_name']] = (int)$row['status'];
            }
        }
        return $configs;
    }

    /**
     * Met à jour le statut d'un module
     */
    public function updateModuleConfig(string $attrName, int $status): bool
    {
        $qb = new QueryBuilder();
        $qb->update('mc_config', ['status' => $status])->where('attr_name = :attr', ['attr' => $attrName]);
        return $this->executeUpdate($qb);
    }

    // --- GESTION DES LANGUES DU DOMAINE (mc_domain_language) ---

    /**
     * Récupère les langues associées à un domaine précis
     */
    public function fetchDomainLanguages(int $idDomain): array
    {
        $qb = new QueryBuilder();
        $qb->select('*')->from('mc_domain_language')->where('id_domain = :id', ['id' => $idDomain]);
        $results = $this->executeAll($qb);

        $assoc = [];
        if ($results) {
            foreach ($results as $row) {
                // Indexé par id_lang pour faciliter l'affichage dans Smarty
                $assoc[$row['id_lang']] = $row;
            }
        }
        return $assoc;
    }

    /**
     * Synchronise les langues d'un domaine (Supprime les anciennes, insère les nouvelles)
     */
    public function syncDomainLanguages(int $idDomain, array $selectedLangs, int $defaultLangId): bool
    {
        // 1. Nettoyage des anciennes liaisons
        $qbDel = new QueryBuilder();
        $qbDel->delete('mc_domain_language')->where('id_domain = :id', ['id' => $idDomain]);
        $this->executeDelete($qbDel);

        if (empty($selectedLangs)) return true;

        $success = true;

        // 2. Insertion des nouvelles liaisons
        foreach ($selectedLangs as $idLang) {
            $isDefault = ((int)$idLang === $defaultLangId) ? 1 : 0;

            $qbIn = new QueryBuilder();
            $qbIn->insert('mc_domain_language', [
                'id_domain'    => $idDomain,
                'id_lang'      => (int)$idLang,
                'default_lang' => $isDefault
            ]);

            if (!$this->executeInsert($qbIn)) {
                $success = false;
            }
        }

        return $success;
    }
}