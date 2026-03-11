<?php

declare(strict_types=1);

namespace App\Frontend\Db;

use Magepattern\Component\Database\QueryBuilder;

class LangDb extends BaseDb
{
    /**
     * Récupère la langue définie par défaut dans le système
     */
    public function getDefaultLanguage(): array|false
    {
        $qb = new QueryBuilder();
        $qb->select('*')
            ->from('mc_lang')
            ->where('default_lang = 1'); // <--- Ceci était correct

        return $this->executeRow($qb);
    }

    /**
     * Récupère la liste des langues actives pour le menu front-end
     */
    public function getFrontendLanguages(): array
    {
        $qb = new QueryBuilder();
        $qb->select('*')
            ->from('mc_lang')
            ->where('active_lang = 1') // <--- CORRECTION 1 : Le bon nom de colonne
            ->orderBy('id_lang', 'ASC'); // <--- CORRECTION 2 : order_lang n'existe pas

        $rows = $this->executeAll($qb);
        $langs = [];
        if ($rows) {
            foreach ($rows as $row) {
                $langs[(int)$row['id_lang']] = $row;
            }
        }
        return $langs;
    }
}