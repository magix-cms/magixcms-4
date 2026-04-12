<?php

declare(strict_types=1);

namespace App\Backend\Db;

use Magepattern\Component\Database\QueryBuilder;

class NewsHomeDb extends BaseDb
{
    /**
     * Récupère l'ID unique de la page d'accueil des News (ou le crée si inexistant)
     */
    public function getOrInsertHomeId(): int
    {
        $qb = new QueryBuilder();
        $qb->select(['id_news_home'])->from('mc_news_home')->limit(1);

        $res = $this->executeRow($qb);

        if ($res && !empty($res['id_news_home'])) {
            return (int)$res['id_news_home'];
        }

        $qbInsert = new QueryBuilder();
        $qbInsert->insert('mc_news_home', ['date_register' => date('Y-m-d H:i:s')]);
        $this->executeInsert($qbInsert);

        $res = $this->executeRow($qb);

        return $res && !empty($res['id_news_home']) ? (int)$res['id_news_home'] : 0;
    }

    /**
     * Récupère tout le contenu de la home news pour toutes les langues
     */
    public function getHomeData(): array
    {
        $id_page = $this->getOrInsertHomeId();

        if ($id_page === 0) {
            return ['id_news_home' => 0, 'content' => []];
        }

        $qb = new QueryBuilder();
        $qb->select(['*'])
            ->from('mc_news_home_content')
            ->where('id_news_home = :id', ['id' => $id_page]);

        $rows = $this->executeAll($qb);

        $arr = ['id_news_home' => $id_page, 'content' => []];
        if ($rows) {
            foreach ($rows as $row) {
                $arr['content'][$row['id_lang']] = $row;
            }
        }
        return $arr;
    }

    /**
     * Sauvegarde (Update ou Insert) le contenu par langue
     */
    public function saveContent(int $idPage, int $idLang, array $data): bool
    {
        $qbCheck = new QueryBuilder();
        $qbCheck->select(['id_content'])
            ->from('mc_news_home_content')
            ->where('id_news_home = :p AND id_lang = :l', ['p' => $idPage, 'l' => $idLang]);

        $exists = $this->executeRow($qbCheck);

        $qb = new QueryBuilder();
        if ($exists && !empty($exists['id_content'])) {
            $qb->update('mc_news_home_content', $data)
                ->where('id_content = :id', ['id' => $exists['id_content']]);
            return $this->executeUpdate($qb);
        } else {
            $data['id_news_home'] = $idPage;
            $data['id_lang'] = $idLang;
            $qb->insert('mc_news_home_content', $data);
            return (bool)$this->executeInsert($qb);
        }
    }
}