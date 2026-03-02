<?php

declare(strict_types=1);

namespace App\Backend\Controller;

use App\Backend\Db\LangDb;

class DashboardController extends BaseController
{
    public function run(): void
    {
        // On peut ajouter des données spécifiques au dashboard ici
        $stats = [
            'version_cms' => '4.0.0-alpha',
            'php_version' => PHP_VERSION,
            'server'      => $_SERVER['SERVER_SOFTWARE']
        ];

        // Assignation à Smarty
        $this->view->assign('stats', $stats);

        // 1. Instanciation du modèle LangDb
        $langDb = new LangDb();

        // 2. Récupération du total
        $totalLangs = $langDb->countActiveLanguages();

        // 3. Assignation à la vue
        $this->view->assign([
            'total_langs' => $totalLangs
        ]);

        // Rendu du template
        // Le template aura aussi accès à $default_lang grâce au BaseController
        $this->view->display('dashboard/index.tpl');
    }
}