<?php

declare(strict_types=1);

namespace App\Backend\Db;

use Magepattern\Component\Database\QueryBuilder;

class CatalogDb extends BaseDb
{
    /**
     * Récupère la structure principale de la page catalogue (ID 1)
     */
    public function getCatalogHome(): array|false
    {
        $qb = new QueryBuilder();
        $qb->select(['*'])->from('mc_catalog_home')->where('id_catalog_home = 1');
        return $this->executeRow($qb);
    }

    /**
     * Récupère tous les contenus multilingues de la page catalogue
     * Indexé par id_lang pour faciliter l'affichage dans Smarty
     */
    public function getCatalogHomeContent(): array
    {
        $qb = new QueryBuilder();
        $qb->select(['*'])->from('mc_catalog_home_content')->where('id_catalog_home = 1');

        $rows = $this->executeAll($qb);
        $content = [];

        if ($rows) {
            foreach ($rows as $row) {
                $content[(int)$row['id_lang']] = $row;
            }
        }
        return $content;
    }

    /**
     * Sauvegarde ou met à jour le contenu pour une langue donnée
     */
    public function saveCatalogContent(int $idLang, array $data): bool
    {
        $qbCheck = new QueryBuilder();
        $qbCheck->select(['id_content'])
            ->from('mc_catalog_home_content')
            ->where('id_catalog_home = 1 AND id_lang = :lang', ['lang' => $idLang]);

        $exists = $this->executeRow($qbCheck);

        $qb = new QueryBuilder();
        if ($exists) {
            $qb->update('mc_catalog_home_content', $data)
                ->where('id_catalog_home = 1 AND id_lang = :lang', ['lang' => $idLang]);
            return $this->executeUpdate($qb);
        } else {
            $data['id_catalog_home'] = 1;
            $data['id_lang']         = $idLang;
            $qb->insert('mc_catalog_home_content', $data);
            return $this->executeInsert($qb);
        }
    }
}