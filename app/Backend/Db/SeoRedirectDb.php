<?php

declare(strict_types=1);

namespace App\Backend\Db;

use Magepattern\Component\Database\QueryBuilder;

class SeoRedirectDb extends BaseDb
{
    /**
     * Insertion ou Mise à jour massive de redirections
     */
    public function insertMassiveRedirects(array $redirects): int
    {
        if (empty($redirects)) return 0;

        $processedCount = 0;

        foreach ($redirects as $data) {
            // 1. On vérifie si l'ancienne URL existe déjà
            $qbCheck = new QueryBuilder();
            $qbCheck->select(['id_redirect'])
                ->from('mc_seo_redirect')
                ->where('old_url = :url', ['url' => $data['old_url']]);

            $existing = $this->executeRow($qbCheck);

            if ($existing) {
                // 2. L'URL existe, on la met à jour
                $qbUpdate = new QueryBuilder();
                $qbUpdate->update('mc_seo_redirect', [
                    'new_url'       => $data['new_url'],
                    'type_redirect' => $data['type_redirect']
                ])->where('id_redirect = :id', ['id' => $existing['id_redirect']]);

                if ($this->executeUpdate($qbUpdate)) {
                    $processedCount++;
                }
            } else {
                // 3. L'URL n'existe pas, on l'insère
                $qbInsert = new QueryBuilder();
                $qbInsert->insert('mc_seo_redirect', $data);

                if ($this->executeInsert($qbInsert)) {
                    $processedCount++;
                }
            }
        }

        return $processedCount;
    }

    /**
     * Récupère la liste paginée pour l'affichage dans le tableau du back-office (avec recherche)
     *
     * @param int $page Le numéro de la page en cours
     * @param int $limit Le nombre de résultats par page
     * @param array $search Le tableau des filtres de recherche
     * @return array|false
     */
    public function getPaginatedList(int $page = 1, int $limit = 25, array $search = []): array|false
    {
        $qb = new QueryBuilder();
        $qb->select(['id_redirect', 'old_url', 'new_url', 'type_redirect', 'active'])
            ->from('mc_seo_redirect');

        // GESTION DE LA RECHERCHE
        if (!empty($search)) {
            $nbc = 1;
            foreach ($search as $key => $q) {
                if ($q !== '') {
                    $paramName = 'p' . $nbc;
                    $binds = [];

                    switch ($key) {
                        case 'id_redirect':
                        case 'type_redirect':
                        case 'active':
                            // Recherche stricte pour les ID et statuts
                            $binds[$paramName] = $q;
                            $qb->where("{$key} = :{$paramName}", $binds);
                            break;
                        case 'old_url':
                        case 'new_url':
                            // Recherche partielle pour les URLs
                            $binds[$paramName] = '%' . $q . '%';
                            $qb->where("{$key} LIKE :{$paramName}", $binds);
                            break;
                    }
                    $nbc++;
                }
            }
        }

        $qb->orderBy('id_redirect', 'DESC');

        return $this->executePaginatedQuery($qb, $page, $limit);
    }

    /**
     * Récupère une seule redirection par son ID (pour le formulaire d'édition)
     */
    public function getRedirectById(int $id): array|false
    {
        $qb = new QueryBuilder();
        $qb->select('*')
            ->from('mc_seo_redirect')
            ->where('id_redirect = :id', ['id' => $id]);

        return $this->executeRow($qb);
    }

    /**
     * Ajoute une nouvelle redirection unitaire
     */
    public function insertRedirect(array $data): bool
    {
        $qb = new QueryBuilder();
        $qb->insert('mc_seo_redirect', $data);

        return $this->executeInsert($qb);
    }

    /**
     * Met à jour une redirection existante
     */
    public function updateRedirect(int $id, array $data): bool
    {
        $qb = new QueryBuilder();
        $qb->update('mc_seo_redirect', $data)
            ->where('id_redirect = :id', ['id' => $id]);

        return $this->executeUpdate($qb);
    }

    /**
     * Modifie l'état (actif/inactif) d'une redirection depuis le tableau
     */
    public function updateState(int $id, int $state): bool
    {
        $qb = new QueryBuilder();
        $qb->update('mc_seo_redirect', ['active' => $state])
            ->where('id_redirect = :id', ['id' => $id]);

        return $this->executeUpdate($qb);
    }

    /**
     * Supprime une ou plusieurs redirections
     */
    public function deleteRedirects(array $ids): bool
    {
        if (empty($ids)) {
            return false;
        }

        $qb = new QueryBuilder();
        $qb->delete('mc_seo_redirect')
            ->whereIn('id_redirect', $ids);

        return $this->executeDelete($qb);
    }
}