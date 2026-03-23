<?php
declare(strict_types=1);

namespace Plugins\GoogleRecaptcha\db;

use App\Backend\Db\BaseDb;
use Magepattern\Component\Database\QueryBuilder;

class BackendDb extends BaseDb
{
    public function getKeys(): array
    {
        $qb = new QueryBuilder();
        $qb->select('site_key, secret_key')->from('mc_googlerecaptcha')->where('id_recaptcha = 1');
        $result = $this->executeRow($qb);

        return [
            'site_key'   => $result['site_key'] ?? '',
            'secret_key' => $result['secret_key'] ?? ''
        ];
    }

    public function saveKeys(string $siteKey, string $secretKey): bool
    {
        $qb = new QueryBuilder();
        $qb->update('mc_googlerecaptcha', [
            'site_key'   => $siteKey,
            'secret_key' => $secretKey
        ])->where('id_recaptcha = 1');

        return $this->executeUpdate($qb);
    }
}