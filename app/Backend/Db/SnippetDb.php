<?php

declare(strict_types=1);

namespace App\Backend\Db;

use Magepattern\Component\Database\QueryBuilder;

class SnippetDb extends BaseDb
{
    /**
     * Récupère les données formatées pour la liste JSON de TinyMCE
     */
    public function getSnippetsForTinymce(): array
    {
        $qb = new QueryBuilder();
        $qb->select(['id_snippet', 'title_sp AS title', 'description_sp AS description'])
            ->from('mc_snippet')
            ->orderBy('order_sp', 'ASC');

        return $this->executeAll($qb) ?: [];
    }

    /**
     * Récupère la liste de tous les snippets pour table-forms
     */
    public function fetchAllSnippets(): array
    {
        $qb = new QueryBuilder();
        $qb->select('*')
            ->from('mc_snippet')
            ->orderBy('order_sp', 'ASC');

        return $this->executeAll($qb) ?: [];
    }

    /**
     * Récupère un snippet par son ID
     */
    public function fetchSnippetById(int $idSnippet): ?array
    {
        $qb = new QueryBuilder();
        $qb->select('*')
            ->from('mc_snippet')
            ->where('id_snippet = :id', ['id' => $idSnippet]);

        $result = $this->executeRow($qb);
        return $result ?: null;
    }

    /**
     * Insère un nouveau snippet et gère son ordre automatiquement
     */
    public function insertSnippet(array $data): int|false
    {
        // On calcule le prochain ordre disponible
        $qbOrder = new QueryBuilder();
        $qbOrder->select('MAX(order_sp) as max_order')->from('mc_snippet');
        $res = $this->executeRow($qbOrder);

        $data['order_sp'] = $res ? (int)$res['max_order'] + 1 : 1;

        $qb = new QueryBuilder();
        $qb->insert('mc_snippet', $data);

        return $this->executeInsert($qb) ? $this->getLastInsertId() : false;
    }

    /**
     * Met à jour un snippet existant
     */
    public function updateSnippet(int $idSnippet, array $data): bool
    {
        $qb = new QueryBuilder();
        $qb->update('mc_snippet', $data)
            ->where('id_snippet = :id', ['id' => $idSnippet]);

        return $this->executeUpdate($qb);
    }

    /**
     * Supprime un snippet
     */
    public function deleteSnippet(int $idSnippet): bool
    {
        $qb = new QueryBuilder();
        $qb->delete('mc_snippet')
            ->where('id_snippet = :id', ['id' => $idSnippet]);

        return $this->executeDelete($qb);
    }

    /**
     * Met à jour l'ordre d'un snippet (pour le drag & drop)
     */
    public function updateSnippetOrder(int $idSnippet, int $position): bool
    {
        $qb = new QueryBuilder();
        $qb->update('mc_snippet', ['order_sp' => $position])
            ->where('id_snippet = :id', ['id' => $idSnippet]);

        return $this->executeUpdate($qb);
    }
}