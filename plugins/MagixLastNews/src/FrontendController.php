<?php
declare(strict_types=1);

namespace Plugins\MagixLastNews\src;

use App\Frontend\Db\NewsDb;
use App\Frontend\Model\NewsPresenter;
use Magepattern\Component\Tool\SmartyTool;
use App\Frontend\Db\CompanyDb;
use App\Component\Db\PluginDb;

class FrontendController
{
    /**
     * Méthode appelée par défaut par le HookManager
     */
    public static function renderWidget(array $params = []): string
    {
        $hookName = $params['name'] ?? '';

        // Aiguillage pour le footer
        if (str_starts_with($hookName, 'displayFooterCol')) {
            return self::renderFooterWidget($params);
        }

        // =========================================================
        //  1. DÉTECTION DYNAMIQUE BASÉE SUR L'ORDRE DU LAYOUT
        // =========================================================
        static $callCount = [];

        // On initialise le compteur pour ce Hook spécifique
        if (!isset($callCount[$hookName])) {
            $callCount[$hookName] = 0;
        }

        $currentIndex = $callCount[$hookName];
        $callCount[$hookName]++;

        // Appel de la méthode globale pour connaître l'ordre d'affichage
        $pluginDb = new PluginDb();
        $widgetOrder = $pluginDb->getWidgetOrder($hookName, 'MagixLastNews');

        $itemSlug = '';
        if (isset($widgetOrder[$currentIndex])) {
            $itemSlug = strtolower((string)($widgetOrder[$currentIndex]['item_slug'] ?? ''));
        }

        $isEventMode = ($itemSlug === 'calendar');

        // =========================================================
        //  2. RÉCUPÉRATION DES DONNÉES SQL
        // =========================================================
        $currentLang = $params['current_lang'] ?? ['id_lang' => 1, 'iso_lang' => 'fr'];
        $idLang = (int)$currentLang['id_lang'];
        $siteUrl = rtrim($params['site_url'] ?? 'http://localhost', '/');

        $newsDb = new NewsDb();

        $queryFilters = ['limit' => 3];
        $queryFilters['is_event'] = $isEventMode;

        $dbResult = $newsDb->getNewsList($idLang, $queryFilters);
        $rawNews = $dbResult['items'] ?? [];

        if (empty($rawNews)) {
            return '';
        }

        $lastNews = [];
        $companyDb = new CompanyDb();
        $companyInfo = $companyDb->getCompanyInfo();

        foreach ($rawNews as $row) {
            $formatted = NewsPresenter::format($row, $currentLang, $siteUrl, $companyInfo);
            $formatted['tags'] = $newsDb->getNewsTags((int)$formatted['id'], $idLang);
            $lastNews[] = $formatted;
        }

        // =========================================================
        //  3. RENDU SMARTY SÉCURISÉ
        // =========================================================
        $view = SmartyTool::getInstance('front');

        $view->assign('is_event_widget', $isEventMode);
        $view->assign('last_news', $lastNews);

        // On inclut $currentIndex dans le hash pour garantir que Smarty
        // génère bien deux blocs distincts en mémoire cache.
        $cacheId = md5('magixlastnews_' . $hookName . '_' . $itemSlug . '_' . $currentIndex);

        return $view->fetch(ROOT_DIR . 'plugins/MagixLastNews/views/front/widget.tpl', $cacheId);
    }

    /**
     * Méthode spécifique pour le Footer
     */
    public static function renderFooterWidget(array $params = []): string
    {
        $currentLang = $params['current_lang'] ?? ['id_lang' => 1, 'iso_lang' => 'fr'];
        $idLang = (int)$currentLang['id_lang'];
        $siteUrl = rtrim($params['site_url'] ?? 'http://localhost', '/');

        $newsDb = new NewsDb();

        $dbResult = $newsDb->getNewsList($idLang, [
            'limit' => 3,
            'is_event' => false
        ]);

        $rawNews = $dbResult['items'] ?? [];

        if (empty($rawNews)) {
            return '';
        }

        $footerNews = [];
        $companyDb = new CompanyDb();
        $companyInfo = $companyDb->getCompanyInfo();

        foreach ($rawNews as $row) {
            $formatted = NewsPresenter::format($row, $currentLang, $siteUrl, $companyInfo);
            $footerNews[] = $formatted;
        }

        $view = SmartyTool::getInstance('front');
        $view->assign('footer_news', $footerNews);

        $cacheId = md5('magixlastnews_footer_' . $idLang);

        return $view->fetch(ROOT_DIR . 'plugins/MagixLastNews/views/front/widget_footer.tpl', $cacheId);
    }
}