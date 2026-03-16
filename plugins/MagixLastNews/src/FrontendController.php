<?php
declare(strict_types=1);

namespace Plugins\MagixLastNews\src;

use App\Frontend\Db\NewsDb;
use App\Frontend\Model\NewsPresenter;
use Magepattern\Component\Tool\SmartyTool;
use App\Frontend\Db\CompanyDb;

class FrontendController
{
    public static function renderWidget(array $params = []): string
    {
        $currentLang = $params['current_lang'] ?? ['id_lang' => 1, 'iso_lang' => 'fr'];
        $idLang = (int)$currentLang['id_lang'];
        $siteUrl = $params['site_url'] ?? 'http://localhost';

        // 1. On instancie le moteur central des News
        $newsDb = new NewsDb();

        // 2. On récupère le tableau complet (items + pagination)
        $dbResult = $newsDb->getNewsList($idLang, [
            'limit' => 3 // Je ne veux que les 3 dernières !
        ]);

        // 🟢 LA CORRECTION EST ICI : On extrait uniquement les articles (items)
        $rawNews = $dbResult['items'] ?? [];

        if (empty($rawNews)) {
            return ''; // S'il n'y a pas de news, on n'affiche rien
        }

        // 3. Formatage via le Presenter universel
        $lastNews = [];

        // 🟢 (Optionnel) : Si votre NewsPresenter a besoin des infos de l'entreprise
        $companyDb = new CompanyDb();
        $companyInfo = $companyDb->getCompanyInfo();

        foreach ($rawNews as $row) {
            // J'ai rajouté $companyInfo ici pour correspondre à la signature de votre Presenter si vous l'avez modifiée
            $formatted = NewsPresenter::format($row, $currentLang, $siteUrl, $companyInfo);

            // Récupérer les tags pour le widget
            $formatted['tags'] = $newsDb->getNewsTags((int)$formatted['id'], $idLang);

            $lastNews[] = $formatted;
        }

        // 4. Envoi à Smarty
        $view = SmartyTool::getInstance('front');
        $view->assign('last_news', $lastNews);

        return $view->fetch(ROOT_DIR . 'plugins/MagixLastNews/views/front/widget.tpl');
    }
}