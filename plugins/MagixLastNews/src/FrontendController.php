<?php
declare(strict_types=1);

namespace Plugins\MagixLastNews\src;

use App\Frontend\Db\NewsDb;
use App\Frontend\Model\NewsPresenter;
use Magepattern\Component\Tool\SmartyTool;
use App\Frontend\Db\CompanyDb;

class FrontendController
{
    /**
     * Méthode appelée par défaut par le HookManager (Priorité B)
     */
    public static function renderWidget(array $params = []): string
    {
        // =========================================================
        // 🟢 1. L'AIGUILLAGE INTELLIGENT
        // On regarde quel Hook est en train d'appeler ce module
        // =========================================================
        $hookName = $params['name'] ?? '';

        // Si le nom du hook commence par 'displayFooterCol', on redirige vers le design Footer
        if (str_starts_with($hookName, 'displayFooterCol')) {
            return self::renderFooterWidget($params);
        }

        // =========================================================
        // 🟢 2. SINON, RENDU NORMAL (Ex: Page d'accueil - displayHome)
        // =========================================================
        $currentLang = $params['current_lang'] ?? ['id_lang' => 1, 'iso_lang' => 'fr'];
        $idLang = (int)$currentLang['id_lang'];
        $siteUrl = $params['site_url'] ?? 'http://localhost';

        // On instancie le moteur central des News
        $newsDb = new NewsDb();

        // On récupère le tableau complet (items + pagination)
        $dbResult = $newsDb->getNewsList($idLang, [
            'limit' => 3 // Je ne veux que les 3 dernières !
        ]);

        // On extrait uniquement les articles (items)
        $rawNews = $dbResult['items'] ?? [];

        if (empty($rawNews)) {
            return ''; // S'il n'y a pas de news, on n'affiche rien
        }

        // Formatage via le Presenter universel
        $lastNews = [];
        $companyDb = new CompanyDb();
        $companyInfo = $companyDb->getCompanyInfo();

        foreach ($rawNews as $row) {
            $formatted = NewsPresenter::format($row, $currentLang, $siteUrl, $companyInfo);
            // Récupérer les tags pour le widget
            $formatted['tags'] = $newsDb->getNewsTags((int)$formatted['id'], $idLang);
            $lastNews[] = $formatted;
        }

        // Envoi à Smarty
        $view = SmartyTool::getInstance('front');
        $view->assign('last_news', $lastNews);

        return $view->fetch(ROOT_DIR . 'plugins/MagixLastNews/views/front/widget.tpl');
    }

    /**
     * Méthode spécifique pour le Footer
     */
    public static function renderFooterWidget(array $params = []): string
    {
        $currentLang = $params['current_lang'] ?? ['id_lang' => 1, 'iso_lang' => 'fr'];
        $idLang = (int)$currentLang['id_lang'];
        $siteUrl = $params['site_url'] ?? 'http://localhost';

        $newsDb = new NewsDb();

        // On limite strictement à 3 pour le footer
        $dbResult = $newsDb->getNewsList($idLang, [
            'limit' => 3
        ]);

        $rawNews = $dbResult['items'] ?? [];

        if (empty($rawNews)) {
            return ''; // Pas de news = on n'affiche pas la colonne
        }

        $footerNews = [];
        $companyDb = new CompanyDb();
        $companyInfo = $companyDb->getCompanyInfo();

        foreach ($rawNews as $row) {
            // Utilisation de votre Presenter pour un formatage parfait et sécurisé
            $formatted = NewsPresenter::format($row, $currentLang, $siteUrl, $companyInfo);
            // Note : On a ignoré les tags ici pour la performance (non nécessaires dans le footer)
            $footerNews[] = $formatted;
        }

        $view = SmartyTool::getInstance('front');
        // On assigne sous un nom de variable différent pour éviter tout conflit
        $view->assign('footer_news', $footerNews);

        return $view->fetch(ROOT_DIR . 'plugins/MagixLastNews/views/front/widget_footer.tpl');
    }
}