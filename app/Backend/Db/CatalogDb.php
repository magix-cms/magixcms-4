<?php

declare(strict_types=1);

namespace App\Backend\Db;

use Magepattern\Component\Database\QueryBuilder;

class CatalogDb extends BaseDb
{
    /**
     * Récupère l'ID unique de la racine du catalogue (ou le crée si inexistant)
     */
    public function getOrInsertCatalogHomeId(): int
    {
        $qb = new QueryBuilder();
        $qb->select(['id_catalog_home'])
            ->from('mc_catalog_home')
            ->where('id_catalog_home = 1')
            ->limit(1);

        $res = $this->executeRow($qb);

        // 1. Si la racine existe, on retourne l'ID direct (qui sera toujours 1)
        if ($res && !empty($res['id_catalog_home'])) {
            return (int)$res['id_catalog_home'];
        }

        // 2. Création de la ligne root forcée à l'ID 1 si elle n'existe pas
        $qbInsert = new QueryBuilder();
        $qbInsert->insert('mc_catalog_home', [
            'id_catalog_home' => 1,
            'date_register'   => date('Y-m-d H:i:s')
        ]);
        $this->executeInsert($qbInsert);

        return 1;
    }

    /**
     * Récupère tout le contenu de la racine pour toutes les langues
     */
    public function getCatalogHomeData(): array
    {
        $id_page = $this->getOrInsertCatalogHomeId();

        $qb = new QueryBuilder();
        $qb->select(['*'])
            ->from('mc_catalog_home_content')
            ->where('id_catalog_home = :id', ['id' => $id_page]);

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
    public function saveCatalogContent(int $idPage, int $idLang, array $data): bool
    {
        // 1. On vérifie si le contenu existe déjà
        $qbCheck = new QueryBuilder();
        $qbCheck->select(['id_content'])
            ->from('mc_catalog_home_content')
            ->where('id_catalog_home = :p AND id_lang = :l', ['p' => $idPage, 'l' => $idLang]);

        $exists = $this->executeRow($qbCheck);

        $qb = new QueryBuilder();
        if ($exists && !empty($exists['id_content'])) {
            // UPDATE
            $qb->update('mc_catalog_home_content', $data)
                ->where('id_content = :id', ['id' => $exists['id_content']]);
            return $this->executeUpdate($qb);
        } else {
            // INSERT
            $data['id_catalog_home'] = $idPage;
            $data['id_lang']         = $idLang;
            $qb->insert('mc_catalog_home_content', $data);
            return $this->executeInsert($qb);
        }
    }
}