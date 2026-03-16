<?php

declare(strict_types=1);

namespace App\Frontend\Db;

use Magepattern\Component\Database\QueryBuilder;

class LogoDb extends BaseDb
{
    // Dans App\Frontend\Db\LogoDb.php (à créer si ce n'est pas fait)
    public function getActiveLogo(int $idLang): ?array
    {
        $qb = new QueryBuilder();
        $qb->select('l.id_logo, l.img_logo AS name_img, c.alt_logo, c.title_logo')
            ->from('mc_logo', 'l')
            ->leftJoin('mc_logo_content', 'c', 'l.id_logo = c.id_logo AND c.id_lang = ' . $idLang)
            ->where('l.active_logo = 1');

        return $this->executeRow($qb) ?: null;
    }
    /**
     * Récupère le logo assigné au footer (active_footer = 1)
     */
    public function getActiveFooterLogo(int $idLang): ?array
    {
        $qb = new QueryBuilder();
        $qb->select('l.id_logo, l.img_logo AS name_img, c.alt_logo, c.title_logo')
            ->from('mc_logo', 'l')
            ->leftJoin('mc_logo_content', 'c', 'l.id_logo = c.id_logo AND c.id_lang = ' . $idLang)
            ->where('l.active_footer = 1');

        $result = $this->executeRow($qb);
        return $result ?: null;
    }
}