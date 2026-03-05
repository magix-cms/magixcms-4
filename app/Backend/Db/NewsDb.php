<?php

declare(strict_types=1);

namespace App\Backend\Db;

use Magepattern\Component\Database\QueryBuilder;
use Magepattern\Component\Tool\DateTool;

class NewsDb extends BaseDb
{
    /**
     * Récupère la liste des actualités avec gestion de la recherche et pagination.
     */
    public function fetchAllNews(int $page = 1, int $limit = 25, array $search = [], int $idLang = 1): array|false
    {
        $qb = new QueryBuilder();

        $qb->select([
            'n.id_news',
            'c.name_news',
            'c.published_news',
            'IFNULL(ni.default_img, 0) as default_img',
            'n.date_publish',
            'n.date_register'
        ])
            ->from('mc_news', 'n')
            ->join('mc_news_content', 'c', 'n.id_news = c.id_news')
            ->leftJoin('mc_news_img', 'ni', 'n.id_news = ni.id_news AND ni.default_img = 1')
            ->where('c.id_lang = :id_lang', ['id_lang' => $idLang]);

        // GESTION DE LA RECHERCHE
        if (!empty($search)) {
            $qb->orderBy('n.id_news', 'DESC');

            $nbc = 1;
            foreach ($search as $key => $q) {
                if ($q !== '') {
                    $paramName = 'p' . $nbc;
                    $binds = [];
                    switch ($key) {
                        case 'id_news':
                            $binds[$paramName] = $q;
                            $qb->where("n.{$key} = :{$paramName}", $binds);
                            break;
                        case 'published_news':
                            $binds[$paramName] = $q;
                            $qb->where("c.{$key} = :{$paramName}", $binds);
                            break;
                        case 'name_news':
                            $binds[$paramName] = '%' . $q . '%';
                            $qb->where("c.{$key} LIKE :{$paramName}", $binds);
                            break;
                        case 'date_publish':
                        case 'date_register':
                            $formattedDate = DateTool::toSql((string)$q);
                            $binds[$paramName] = '%' . $formattedDate . '%';
                            $qb->where("n.{$key} LIKE :{$paramName}", $binds);
                            break;
                    }
                    $nbc++;
                }
            }
        } else {
            $qb->orderBy('n.date_publish', 'DESC');
        }

        return $this->executePaginatedQuery($qb, $page, $limit);
    }

    /**
     * Récupère une actualité et tous ses contenus associés (toutes langues)
     */
    public function fetchNewsById(int $id): array|false
    {
        $qb = new QueryBuilder();
        $qb->select('*')->from('mc_news')->where('id_news = :id', ['id' => $id]);
        $news = $this->executeRow($qb);

        if (!$news) return false;

        $qbContent = new QueryBuilder();
        $qbContent->select('*')->from('mc_news_content')->where('id_news = :id', ['id' => $id]);
        $contents = $this->executeAll($qbContent);

        $news['content'] = [];
        if ($contents) {
            foreach ($contents as $c) {
                $news['content'][$c['id_lang']] = $c;
            }
        }

        return $news;
    }

    /**
     * Insère la structure d'une nouvelle actualité
     */
    public function insertNewsStructure(array $data): int|false
    {
        $qb = new QueryBuilder();
        $qb->insert('mc_news', $data);

        if ($this->executeInsert($qb)) {
            return $this->getLastInsertId();
        }
        return false;
    }

    /**
     * Met à jour la structure d'une actualité
     */
    public function updateNewsStructure(int $idNews, array $data): bool
    {
        $qb = new QueryBuilder();
        $qb->update('mc_news', $data)->where('id_news = :id', ['id' => $idNews]);
        return $this->executeUpdate($qb);
    }

    /**
     * Sauvegarde le contenu multilingue
     */
    public function saveNewsContent(int $idNews, int $idLang, array $data): bool
    {
        $qbCheck = new QueryBuilder();
        $qbCheck->select(['id_news'])
            ->from('mc_news_content')
            ->where('id_news = :n AND id_lang = :l', ['n' => $idNews, 'l' => $idLang]);

        $exists = $this->executeRow($qbCheck);
        $qb = new QueryBuilder();

        if ($exists) {
            $qb->update('mc_news_content', $data)
                ->where('id_news = :n AND id_lang = :l', ['n' => $idNews, 'l' => $idLang]);
            return $this->executeUpdate($qb);
        } else {
            $data['id_news'] = $idNews;
            $data['id_lang'] = $idLang;
            $qb->insert('mc_news_content', $data);
            return $this->executeInsert($qb);
        }
    }

    public function deleteNews(array $ids): bool
    {
        if (empty($ids)) return false;

        $tables = ['mc_news_tag_rel', 'mc_news_img_content', 'mc_news_img', 'mc_news_content', 'mc_news'];
        $success = true;

        foreach ($tables as $table) {
            $qb = new QueryBuilder();
            // L'image a une structure différente pour le contenu (id_img au lieu de id_news)
            if ($table === 'mc_news_img_content') continue;

            $qb->delete($table)->whereIn('id_news', $ids);
            if (!$this->executeDelete($qb)) {
                $success = false;
            }
        }
        return $success;
    }

    // ==========================================================
    // GESTION DE LA GALERIE D'IMAGES (Identique à PagesDb)
    // ==========================================================

    public function insertImage(int $newsId, string $filename): bool
    {
        $qbCount = new QueryBuilder();
        $qbCount->select(['COUNT(id_img) as total'])->from('mc_news_img')->where('id_news = :id', ['id' => $newsId]);
        $countResult = $this->executeRow($qbCount);

        $order = (int)($countResult['total'] ?? 0);
        $isDefault = ($order === 0) ? 1 : 0;

        $qbInsert = new QueryBuilder();
        $qbInsert->insert('mc_news_img', [
            'id_news'     => $newsId,
            'name_img'    => $filename,
            'order_img'   => $order,
            'default_img' => $isDefault
        ]);

        return $this->executeInsert($qbInsert);
    }

    public function reorderImages(array $imageIds): bool
    {
        $success = true;
        foreach ($imageIds as $index => $id) {
            $qb = new QueryBuilder();
            $qb->update('mc_news_img', ['order_img' => $index])->where('id_img = :id', ['id' => (int)$id]);
            if (!$this->executeUpdate($qb)) $success = false;
        }
        return $success;
    }

    public function setDefaultImage(int $newsId, int $imageId): bool
    {
        $qbReset = new QueryBuilder();
        $qbReset->update('mc_news_img', ['default_img' => 0])->where('id_news = :id', ['id' => $newsId]);
        $this->executeUpdate($qbReset);

        $qbSet = new QueryBuilder();
        $qbSet->update('mc_news_img', ['default_img' => 1])->where('id_img = :id', ['id' => $imageId]);
        return $this->executeUpdate($qbSet);
    }

    public function getLastImageId(int $newsId): int
    {
        $qb = new QueryBuilder();
        $qb->select(['MAX(id_img) as max_id'])->from('mc_news_img')->where('id_news = :id', ['id' => $newsId]);
        $result = $this->executeRow($qb);
        return (int)($result['max_id'] ?? 0);
    }

    public function fetchImagesByNews(int $newsId): array
    {
        $qb = new QueryBuilder();
        $qb->select(['*'])->from('mc_news_img')->where('id_news = :id', ['id' => $newsId])->orderBy('order_img', 'ASC');
        return $this->executeAll($qb) ?: [];
    }

    public function deleteImage(int $imageId): array|false
    {
        $qbSelect = new QueryBuilder();
        $qbSelect->select(['*'])->from('mc_news_img')->where('id_img = :id', ['id' => $imageId]);
        $img = $this->executeRow($qbSelect);

        if ($img) {
            // Supprimer le contenu lié à l'image d'abord
            $qbDelContent = new QueryBuilder();
            $qbDelContent->delete('mc_news_img_content')->where('id_img = :id', ['id' => $imageId]);
            $this->executeDelete($qbDelContent);

            $qbDel = new QueryBuilder();
            $qbDel->delete('mc_news_img')->where('id_img = :id', ['id' => $imageId]);
            if ($this->executeDelete($qbDel)) return $img;
        }
        return false;
    }

    public function fetchImageMeta(int $idImg): array
    {
        $qb = new QueryBuilder();
        $qb->select('*')->from('mc_news_img_content')->where('id_img = :id', ['id' => $idImg]);
        $results = $this->executeAll($qb);

        $meta = [];
        if ($results) {
            foreach ($results as $row) {
                $meta[$row['id_lang']] = $row;
            }
        }
        return $meta;
    }

    public function saveImageMeta(int $idImg, int $idLang, array $data): bool
    {
        $qbCheck = new QueryBuilder();
        $qbCheck->select(['id_img'])->from('mc_news_img_content')
            ->where('id_img = :img AND id_lang = :lang', ['img' => $idImg, 'lang' => $idLang]);

        $exists = $this->executeRow($qbCheck);
        $qb = new QueryBuilder();

        if ($exists) {
            $qb->update('mc_news_img_content', $data)
                ->where('id_img = :img AND id_lang = :lang', ['img' => $idImg, 'lang' => $idLang]);
            return $this->executeUpdate($qb);
        } else {
            $data['id_img']  = $idImg;
            $data['id_lang'] = $idLang;
            $qb->insert('mc_news_img_content', $data);
            return $this->executeInsert($qb);
        }
    }

    // ==========================================================
    // GESTION DES TAGS
    // ==========================================================

    public function fetchAllTagsForLang(int $idLang): array
    {
        $qb = new QueryBuilder();
        $qb->select('*')->from('mc_news_tag')->where('id_lang = :lang', ['lang' => $idLang])->orderBy('name_tag', 'ASC');
        return $this->executeAll($qb) ?: [];
    }

    public function fetchNewsTagsIds(int $idNews): array
    {
        $qb = new QueryBuilder();
        $qb->select('id_tag')->from('mc_news_tag_rel')->where('id_news = :id', ['id' => $idNews]);

        $results = $this->executeAll($qb);
        $tags = [];
        if ($results) {
            foreach ($results as $row) {
                $tags[] = (int)$row['id_tag'];
            }
        }
        return $tags;
    }

    public function syncNewsTags(int $idNews, array $tagIds): bool
    {
        $qbDel = new QueryBuilder();
        $qbDel->delete('mc_news_tag_rel')->where('id_news = :id', ['id' => $idNews]);
        $this->executeDelete($qbDel);

        if (empty($tagIds)) return true;

        $success = true;
        foreach ($tagIds as $idTag) {
            $qbIn = new QueryBuilder();
            $qbIn->insert('mc_news_tag_rel', ['id_news' => $idNews, 'id_tag' => (int)$idTag]);
            if (!$this->executeInsert($qbIn)) $success = false;
        }
        return $success;
    }
}