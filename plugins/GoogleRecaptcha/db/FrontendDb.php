<?php
declare(strict_types=1);

namespace Plugins\GoogleRecaptcha\db;

use App\Frontend\Db\BaseDb;
use Magepattern\Component\Database\QueryBuilder;

class FrontendDb extends BaseDb
{
    public function isLinkedToModule(string $moduleName): bool
    {
        $qb = new QueryBuilder();
        $qb->select('id_module')
            ->from('mc_plugins_module')
            ->where('plugin_name = :plugin AND module_name = :module AND active = 1', [
                'plugin' => 'GoogleRecaptcha',
                'module' => strtolower($moduleName)
            ]);

        return (bool)$this->executeRow($qb);
    }

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
}