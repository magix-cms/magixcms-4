<?php

declare(strict_types=1);

namespace App\Frontend\Controller;

use App\Frontend\Db\HomeDb;
use App\Frontend\Db\CompanyDb;
use App\Frontend\Model\SeoHelper;

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

        // 3. Génération du Rich Snippet (WebSite + Organization) pour l'accueil
        $siteUrl = $this->view->getTemplateVars('site_url') ?? '';
        $isoLang = $this->currentLang['iso_lang'] ?? 'fr';

        $companyDb = new CompanyDb();
        // S'assurer qu'on passe bien un tableau même si vide
        $companyInfo = $companyDb->getCompanyInfo() ?: [];

        $jsonLdScript = SeoHelper::generateHomeGraphJsonLd($siteName, $siteUrl, $isoLang, $seoDesc, $companyInfo);

        // 4. Assignation à Smarty
        $this->view->assign([
            'seo_title'       => $seoTitle,
            'seo_desc'        => $seoDesc,
            'home_data'       => $homeData ?: [],
            'current_c'       => 'home',
            'website_json_ld' => $jsonLdScript // 🟢 Le template "home.tpl" l'attend ici !
        ]);

        // 5. Affichage du template
        $this->view->display('home/index.tpl');
    }
}