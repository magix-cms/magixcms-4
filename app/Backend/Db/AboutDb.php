<?php

declare(strict_types=1);

namespace App\Backend\Db;

use Magepattern\Component\Database\QueryBuilder;
use Magepattern\Component\Tool\DateTool;

class AboutDb extends BaseDb
{
    /**
     * Récupère la liste paginée (Listing principal)
     */
    public function fetchAllAbout(int $page = 1, int $limit = 25, array $search = [], int $idLang = 1): array|false
    {
        $qb = new QueryBuilder();
        $qb->select([
            'a.id_about',
            'c.name_about',
            'c.published_about',
            'pa_c.name_about AS parent_about', // Pour afficher le nom du parent
            'a.menu_about',
            'a.date_register'
        ])
            ->from('mc_about', 'a')
            ->join('mc_about_content', 'c', 'a.id_about = c.id_about')
            ->join('mc_lang', 'lang', 'c.id_lang = lang.id_lang')
            // Jointure pour récupérer le nom du parent
            ->leftJoin('mc_about', 'pa', 'a.id_parent = pa.id_about')
            ->leftJoin('mc_about_content', 'pa_c', 'pa.id_about = pa_c.id_about AND pa_c.id_lang = :id_lang')
            ->where('c.id_lang = :id_lang', ['id_lang' => $idLang]);

        if (!empty($search)) {
            $qb->orderBy('a.id_about', 'DESC');
            // Ajoutez ici vos filtres de recherche si nécessaire (LIKE...)
        } else {
            // Tri par parent puis par ordre (pour visualiser la hiérarchie)
            $qb->orderBy('a.id_parent', 'ASC')
                ->orderBy('a.order_about', 'ASC');
        }

        return $this->executePaginatedQuery($qb, $page, $limit);
    }

    /**
     * C'était la méthode manquante qui causait votre erreur !
     * Récupère la liste simplifiée pour le select parent
     */
    public function fetchAllAboutForSelect(int $idLang): array|false
    {
        $qb = new QueryBuilder();
        $qb->select([
            'a.id_about',
            'a.id_parent', // Important pour l'indentation ou le tri
            'c.name_about'
        ])
            ->from('mc_about', 'a')
            ->join('mc_about_content', 'c', 'a.id_about = c.id_about')
            ->where('c.id_lang = :id_lang', ['id_lang' => $idLang])
            ->orderBy('a.id_parent', 'ASC')
            ->orderBy('a.order_about', 'ASC');

        return $this->executeAll($qb);
    }

    /**
     * Récupère les enfants d'un parent (pour l'onglet "Sous-pages" dans edit)
     */
    public function fetchAboutByParent(int $parentId, int $idLang): array|false
    {
        $qb = new QueryBuilder();
        $qb->select([
            'a.id_about',
            'c.name_about',
            'c.published_about',
            'a.menu_about',
            'a.date_register',
            'a.order_about'
        ])
            ->from('mc_about', 'a')
            ->join('mc_about_content', 'c', 'a.id_about = c.id_about')
            ->where('a.id_parent = :parent', ['parent' => $parentId])
            ->where('c.id_lang = :id_lang', ['id_lang' => $idLang])
            ->orderBy('a.order_about', 'ASC');

        return $this->executeAll($qb);
    }

    /**
     * Récupère une entrée complète par ID
     */
    public function fetchAboutById(int $id): array|false
    {
        $qb = new QueryBuilder();
        $qb->select('*')->from('mc_about')->where('id_about = :id', ['id' => $id]);
        $about = $this->executeRow($qb);

        if (!$about) return false;

        $qbContent = new QueryBuilder();
        $qbContent->select('*')->from('mc_about_content')->where('id_about = :id', ['id' => $id]);
        $contents = $this->executeAll($qbContent);

        $about['content'] = [];
        if ($contents) {
            foreach ($contents as $c) {
                $about['content'][$c['id_lang']] = $c;
            }
        }
        return $about;
    }

    /**
     * Création Structure
     */
    public function insertAboutStructure(array $data): int|false
    {
        $qb = new QueryBuilder();
        $qb->insert('mc_about', $data);
        if ($this->executeInsert($qb)) {
            return $this->getLastInsertId();
        }
        return false;
    }

    /**
     * Mise à jour Structure
     */
    public function updateAboutStructure(int $id, array $data): bool
    {
        $qb = new QueryBuilder();
        $qb->update('mc_about', $data)->where('id_about = :id', ['id' => $id]);
        return $this->executeUpdate($qb);
    }

    /**
     * Mise à jour Ordre (Drag & Drop)
     */
    public function updateOrderAbout(int $idAbout, int $position): bool
    {
        $qb = new QueryBuilder();
        $qb->update('mc_about', ['order_about' => $position])
            ->where('id_about = :id', ['id' => $idAbout]);
        return $this->executeUpdate($qb);
    }

    /**
     * Sauvegarde Contenu (Insert ou Update intelligent)
     */
    public function saveAboutContent(int $idAbout, int $idLang, array $data): bool
    {
        $qbCheck = new QueryBuilder();
        $qbCheck->select(['id_about'])->from('mc_about_content')
            ->where('id_about = :a AND id_lang = :l', ['a' => $idAbout, 'l' => $idLang]);

        $exists = $this->executeRow($qbCheck);
        $qb = new QueryBuilder();

        if ($exists) {
            $qb->update('mc_about_content', $data)
                ->where('id_about = :a AND id_lang = :l', ['a' => $idAbout, 'l' => $idLang]);
            return $this->executeUpdate($qb);
        } else {
            $data['id_about'] = $idAbout;
            $data['id_lang'] = $idLang;
            $qb->insert('mc_about_content', $data);
            return $this->executeInsert($qb);
        }
    }

    /**
     * Suppression
     */
    public function deleteAbout(array $ids): bool
    {
        if (empty($ids)) return false;

        // Suppression Contenus (Manuel ou Cascade selon BDD, mieux vaut être sûr)
        $qbC = new QueryBuilder();
        $qbC->delete('mc_about_content')->whereIn('id_about', $ids);
        $this->executeDelete($qbC);

        // Suppression Structure
        $qb = new QueryBuilder();
        $qb->delete('mc_about')->whereIn('id_about', $ids);
        return $this->executeDelete($qb);
    }

    // --- IMAGES ---

    public function insertImage(int $idAbout, string $name): bool
    {
        $qbCount = new QueryBuilder();
        $qbCount->select(['COUNT(id_img) as total'])->from('mc_about_img')->where('id_about = :id', ['id' => $idAbout]);
        $res = $this->executeRow($qbCount);
        $order = (int)($res['total'] ?? 0);
        $default = ($order === 0) ? 1 : 0;

        $qb = new QueryBuilder();
        $qb->insert('mc_about_img', [
            'id_about' => $idAbout,
            'name_img' => $name,
            'order_img' => $order,
            'default_img' => $default
        ]);
        return $this->executeInsert($qb);
    }

    public function getLastImageId(int $idAbout): int
    {
        $qb = new QueryBuilder();
        $qb->select(['MAX(id_img) as max_id'])->from('mc_about_img')->where('id_about = :id', ['id' => $idAbout]);
        $row = $this->executeRow($qb);
        return (int)($row['max_id'] ?? 0);
    }

    public function fetchImagesByAbout(int $id): array
    {
        $qb = new QueryBuilder();
        $qb->select(['*'])->from('mc_about_img')->where('id_about = :id', ['id' => $id])->orderBy('order_img', 'ASC');
        return $this->executeAll($qb) ?: [];
    }

    public function deleteAboutImage(int $idImg): array|false
    {
        $qbSelect = new QueryBuilder();
        $qbSelect->select(['*'])->from('mc_about_img')->where('id_img = :id', ['id' => $idImg]);
        $img = $this->executeRow($qbSelect);
        if ($img) {
            $qbDel = new QueryBuilder();
            $qbDel->delete('mc_about_img')->where('id_img = :id', ['id' => $idImg]);
            if ($this->executeDelete($qbDel)) return $img;
        }
        return false;
    }

    public function setDefaultImage(int $idAbout, int $idImg): bool
    {
        $qb1 = new QueryBuilder();
        $qb1->update('mc_about_img', ['default_img' => 0])->where('id_about = :id', ['id' => $idAbout]);
        $this->executeUpdate($qb1);
        $qb2 = new QueryBuilder();
        $qb2->update('mc_about_img', ['default_img' => 1])->where('id_img = :id', ['id' => $idImg]);
        return $this->executeUpdate($qb2);
    }

    public function reorderImages(array $imageIds): bool
    {
        $pos = 0;
        foreach ($imageIds as $id) {
            $qb = new QueryBuilder();
            $qb->update('mc_about_img', ['order_img' => $pos])->where('id_img = :id', ['id' => (int)$id]);
            $this->executeUpdate($qb);
            $pos++;
        }
        return true;
    }
    /**
     * Récupère les métadonnées d'une image pour toutes les langues
     */
    public function fetchImageMeta(int $idImg): array
    {
        $qb = new QueryBuilder();
        $qb->select('*')->from('mc_about_img_content')->where('id_img = :id', ['id' => $idImg]);
        $results = $this->executeAll($qb);

        $meta = [];
        if ($results) {
            foreach ($results as $row) {
                $meta[$row['id_lang']] = $row;
            }
        }
        return $meta;
    }

    /**
     * Sauvegarde ou met à jour les métadonnées d'une image
     */
    public function saveImageMeta(int $idImg, int $idLang, array $data): bool
    {
        $table = 'mc_about_img_content';

        $qbCheck = new QueryBuilder();
        $qbCheck->select(['id_img'])->from($table)
            ->where('id_img = :img AND id_lang = :lang', ['img' => $idImg, 'lang' => $idLang]);

        $exists = $this->executeRow($qbCheck);
        $qb = new QueryBuilder();

        if ($exists) {
            $qb->update($table, $data)
                ->where('id_img = :img AND id_lang = :lang', ['img' => $idImg, 'lang' => $idLang]);
            return $this->executeUpdate($qb);
        } else {
            $data['id_img']  = $idImg;
            $data['id_lang'] = $idLang;
            $qb->insert($table, $data);
            return $this->executeInsert($qb);
        }
    }
}