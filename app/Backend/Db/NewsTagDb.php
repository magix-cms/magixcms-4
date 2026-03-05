<?php

declare(strict_types=1);

namespace App\Backend\Db;

use Magepattern\Component\Database\QueryBuilder;

class NewsTagDb extends BaseDb
{
    /**
     * Récupère la liste paginée des tags
     */
    public function fetchAllTagsPaginated(int $page = 1, int $limit = 25, array $search = []): array|false
    {
        $qb = new QueryBuilder();

        $qb->select([
            't.id_tag',
            't.name_tag',
            't.id_lang',
            'l.iso_lang'
        ])
            ->from('mc_news_tag', 't')
            ->join('mc_lang', 'l', 't.id_lang = l.id_lang');

        // Recherche par nom de tag
        if (!empty($search['name_tag'])) {
            $qb->where('t.name_tag LIKE :search', ['search' => '%' . $search['name_tag'] . '%']);
        }

        $qb->orderBy('t.id_tag', 'DESC');

        return $this->executePaginatedQuery($qb, $page, $limit);
    }

    /**
     * Récupère un tag par son ID
     */
    public function fetchTagById(int $id): array|false
    {
        $qb = new QueryBuilder();
        $qb->select('*')->from('mc_news_tag')->where('id_tag = :id', ['id' => $id]);
        return $this->executeRow($qb);
    }

    /**
     * Insère un nouveau tag
     */
    public function insertTag(array $data): int|false
    {
        $qb = new QueryBuilder();
        $qb->insert('mc_news_tag', $data);

        if ($this->executeInsert($qb)) {
            return $this->getLastInsertId();
        }
        return false;
    }

    /**
     * Met à jour un tag existant
     */
    public function updateTag(int $id, array $data): bool
    {
        $qb = new QueryBuilder();
        $qb->update('mc_news_tag', $data)->where('id_tag = :id', ['id' => $id]);
        return $this->executeUpdate($qb);
    }

    /**
     * Supprime un ou plusieurs tags (et nettoie les liaisons avec les actualités)
     */
    public function deleteTags(array $ids): bool
    {
        if (empty($ids)) return false;

        // 1. On supprime d'abord les liaisons dans mc_news_tag_rel pour éviter les orphelins
        $qbRel = new QueryBuilder();
        $qbRel->delete('mc_news_tag_rel')->whereIn('id_tag', $ids);
        $this->executeDelete($qbRel);

        // 2. On supprime le tag
        $qb = new QueryBuilder();
        $qb->delete('mc_news_tag')->whereIn('id_tag', $ids);

        return $this->executeDelete($qb);
    }
}