<?php

declare(strict_types=1);

namespace App\Frontend\Db;

use Magepattern\Component\Database\QueryBuilder;

class CompanyDb extends BaseDb
{
    /**
     * Récupère les informations globales de l'entreprise
     */
    public function getCompanyInfo(): array
    {
        $qb = new QueryBuilder();
        $qb->select('*')->from('mc_company_info')->limit(1);

        $result = $this->executeRow($qb);
        return $result ?: [];
    }
}