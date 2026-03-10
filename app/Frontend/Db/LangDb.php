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
            ->where('default_lang = 1');

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
            ->where('status_lang = 1')
            ->orderBy('order_lang', 'ASC');

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