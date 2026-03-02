<?php

declare(strict_types=1);

namespace App\Backend\Db;

use Magepattern\Component\Database\QueryBuilder;
use Magepattern\Component\Database\Layer;

class HomepageDb extends BaseDb
{
    /**
     * Récupère l'ID unique de la page d'accueil (ou le crée si inexistant)
     */
    public function getOrInsertHomeId(): int
    {
        $qb = new QueryBuilder();
        $qb->select(['id_page'])->from('mc_home_page')->limit(1);

        $res = $this->executeRow($qb);

        if ($res) {
            return (int)$res['id_page'];
        }

        // Création de la ligne root si elle n'existe pas
        $qbInsert = new QueryBuilder();
        $qbInsert->insert('mc_home_page', ['date_register' => date('Y-m-d H:i:s')]);
        $this->executeInsert($qbInsert);

        return (int) Layer::getInstance()->lastInsertId();
    }

    /**
     * Récupère tout le contenu de la home pour toutes les langues
     */
    public function getHomeData(): array
    {
        $id_page = $this->getOrInsertHomeId();

        $qb = new QueryBuilder();
        $qb->select(['*'])
            ->from('mc_home_page_content')
            ->where('id_page = :id', ['id' => $id_page]);

        $rows = $this->executeAll($qb);

        $arr = ['id_page' => $id_page, 'content' => []];
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
        // 1. On vérifie si le contenu existe déjà pour ce duo Page/Langue
        $qbCheck = new QueryBuilder();
        $qbCheck->select(['id_content'])
            ->from('mc_home_page_content')
            ->where('id_page = :p AND id_lang = :l', ['p' => $idPage, 'l' => $idLang]);

        $exists = $this->executeRow($qbCheck);

        $qb = new QueryBuilder();
        if ($exists) {
            // UPDATE
            $qb->update('mc_home_page_content', $data)
                ->where('id_content = :id', ['id' => $exists['id_content']]);
            return $this->executeUpdate($qb);
        } else {
            // INSERT
            $data['id_page'] = $idPage;
            $data['id_lang'] = $idLang;
            $qb->insert('mc_home_page_content', $data);
            return $this->executeInsert($qb);
        }
    }
}