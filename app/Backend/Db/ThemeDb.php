<?php
declare(strict_types=1);

namespace App\Backend\Db;

use Magepattern\Component\Database\QueryBuilder;

class ThemeDb extends BaseDb
{
    /**
     * Met à jour le thème actif dans les paramètres globaux
     */
    public function setActiveTheme(string $themeName): bool
    {
        $qb = new QueryBuilder();
        $qb->update('mc_setting', ['value' => $themeName])
            ->where("name = 'theme'");

        return $this->executeUpdate($qb);
    }

    /**
     * Récupère le nom du thème actuellement actif
     */
    public function getCurrentTheme(): string
    {
        $qb = new QueryBuilder();
        $qb->select('value')->from('mc_setting')->where("name = 'theme'");
        $res = $this->executeRow($qb);

        return $res['value'] ?? 'default';
    }
}