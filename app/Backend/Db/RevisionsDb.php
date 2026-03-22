<?php

declare(strict_types=1);

namespace App\Backend\Db;

use Magepattern\Component\Database\QueryBuilder;

class RevisionsDb extends BaseDb
{
    /**
     * Récupère la liste des révisions pour un champ précis (sans le contenu lourd)
     */
    public function getList(string $itemType, int $itemId, int $idLang, string $editorId): array
    {
        $qb = new QueryBuilder();
        $qb->select(['id', 'date_register'])
            ->from('mc_revisions_editor')
            ->where('item_type = :type AND item_id = :id AND id_lang = :lang AND editor_id = :editor', [
                'type'   => $itemType,
                'id'     => $itemId,
                'lang'   => $idLang,
                'editor' => $editorId
            ])
            ->orderBy('date_register', 'DESC')
            ->limit(30); // Sécurité : on n'affiche que les 30 dernières révisions

        return $this->executeAll($qb) ?: [];
    }

    /**
     * Récupère le contenu d'une révision spécifique par son ID
     */
    public function getContent(int $idRevision): ?string
    {
        $qb = new QueryBuilder();
        $qb->select(['content'])
            ->from('mc_revisions_editor')
            ->where('id = :id', ['id' => $idRevision]);

        $result = $this->executeRow($qb);
        return $result ? $result['content'] : null;
    }

    /**
     * Supprime toutes les révisions pour un champ spécifique (La fameuse corbeille)
     */
    public function clearHistory(string $itemType, int $itemId, int $idLang, string $editorId): bool
    {
        $qb = new QueryBuilder();
        $qb->delete('mc_revisions_editor')
            ->where('item_type = :type AND item_id = :id AND id_lang = :lang AND editor_id = :editor', [
                'type'   => $itemType,
                'id'     => $itemId,
                'lang'   => $idLang,
                'editor' => $editorId
            ]);

        return $this->executeDelete($qb);
    }

    /**
     * Enregistre une nouvelle révision (Utile pour votre fonction d'autosave)
     */
    /**
     * Enregistre une nouvelle révision uniquement si le contenu a changé
     */
    public function saveRevision(string $itemType, int $itemId, int $idLang, string $editorId, string $content): bool
    {
        // 1. On va chercher la toute dernière révision enregistrée pour ce champ
        $qbCheck = new QueryBuilder();
        $qbCheck->select(['content'])
            ->from('mc_revisions_editor')
            ->where('item_type = :type AND item_id = :id AND id_lang = :lang AND editor_id = :editor', [
                'type'   => $itemType,
                'id'     => $itemId,
                'lang'   => $idLang,
                'editor' => $editorId
            ])
            ->orderBy('date_register', 'DESC')
            ->limit(1);

        $lastRevision = $this->executeRow($qbCheck);

        // 2. Si une révision existe ET que le contenu est strictement identique, on ne fait rien !
        // On retourne 'true' pour faire croire au contrôleur que tout s'est bien passé.
        if ($lastRevision && $lastRevision['content'] === $content) {
            return true;
        }

        // 3. Le contenu est nouveau (ou c'est la première fois), on insère dans la BDD
        $qb = new QueryBuilder();
        $qb->insert('mc_revisions_editor', [
            'item_type' => $itemType,
            'item_id'   => $itemId,
            'id_lang'   => $idLang,
            'editor_id' => $editorId,
            'content'   => $content
        ]);

        return $this->executeInsert($qb);
    }
}