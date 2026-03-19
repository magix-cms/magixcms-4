<?php

declare(strict_types=1);

namespace App\Frontend\Controller;

use App\Frontend\Db\HomeDb;

class HomeController extends BaseController
{
    public function run(): void
    {
        $db = new HomeDb();
        $idLang = (int)$this->currentLang['id_lang'];

        // 1. Récupération des données (renverra false si désactivé)
        $homeData = $db->getHomeDataByLang($idLang);

        // 2. Fallbacks SEO par défaut
        $siteName = $this->siteSettings['site_name']['value'] ?? 'MagixCMS';
        $seoTitle = $siteName;
        $seoDesc  = '';

        // Si la page est publiée, on écrase les fallbacks SEO
        if ($homeData) {
            $seoTitle = !empty($homeData['seo_title_page']) ? $homeData['seo_title_page'] : $siteName;
            $seoDesc  = $homeData['seo_desc_page'] ?? '';
        }

        // 3. Assignation à Smarty
        $this->view->assign([
            'seo_title' => $seoTitle,
            'seo_desc'  => $seoDesc,
            'home_data' => $homeData ?: [], // 🟢 Si false, devient un tableau vide
            'current_c' => 'home'
        ]);

        // 4. Affichage du template
        $this->view->display('home/index.tpl');
    }
}