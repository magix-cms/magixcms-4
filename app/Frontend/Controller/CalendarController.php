<?php

declare(strict_types=1);

namespace App\Frontend\Controller;

use App\Frontend\Db\NewsDb;
use Magepattern\Component\HTTP\Request;
use App\Component\Routing\UrlTool;

class CalendarController extends BaseController
{
    private NewsDb $db;

    public function run(): void
    {
        // 1. CHECK D'ACTIVATION (Basé sur mc_setting)
        $isCalendarActive = $this->siteSettings['calendar_enabled']['value'] ?? '0';
        if ($isCalendarActive !== '1') {
            $this->render404();
            return;
        }

        $this->db = new NewsDb();
        $idLang = (int)($this->currentLang['id_lang'] ?? 1);
        $iso = strtolower($this->currentLang['iso_lang'] ?? 'fr');

        // Initialisation des dates
        $year  = Request::isGet('year') ? (int)$_GET['year'] : (int)date('Y');
        $month = Request::isGet('month') ? (int)$_GET['month'] : (int)date('n');

        if ($month < 1 || $month > 12) {
            $month = (int)date('n');
        }

        $events = $this->db->getCalendarEvents($idLang, $year, $month);

        // TRANSFORMATION DES URLS VIA URLTOOL
        $urlTool = new UrlTool();
        $siteUrl = rtrim((string)$this->view->getTemplateVars('site_url'), '/');

        foreach ($events as &$event) {
            $urlDate = null;
            if (!empty($event['date_publish'])) {
                $urlDate = date('Y-m-d', strtotime($event['date_publish']));
            }

            $relativeUrl = $urlTool->buildUrl([
                'type' => 'news',
                'id'   => $event['id_news'],
                'url'  => $event['slug'],
                'iso'  => $iso,
                'date' => $urlDate
            ]);

            $event['slug'] = $siteUrl . $relativeUrl;
        }
        unset($event);

        // MODE API (AJAX)
        if (Request::isGet('ajax') || (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'year'   => $year,
                'month'  => $month,
                'events' => $events
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        }

        // MODE CLASSIQUE (SEO)
        $cacheId = md5('calendar_page_' . $idLang . '_' . $year . '_' . $month);

        if (!$this->view->isCached('news/calendar.tpl', $cacheId)) {

            $siteName = $this->siteSettings['site_name']['value'] ?? 'Magix CMS';

            // Nom du mois dynamique (Ex: Avril 2026)
            $formatter = new \IntlDateFormatter($this->currentLang['iso_lang'] ?? 'fr', \IntlDateFormatter::FULL, \IntlDateFormatter::NONE, null, null, 'MMMM yyyy');
            $monthName = ucfirst($formatter->format(mktime(0, 0, 0, $month, 1, $year)));

            // Jours de la semaine
            $dayFormatter = new \IntlDateFormatter($this->currentLang['iso_lang'] ?? 'fr', \IntlDateFormatter::NONE, \IntlDateFormatter::NONE, null, null, 'ccc');
            $daysOfWeek = [];
            for ($i = 0; $i < 7; $i++) {
                $timestamp = mktime(0, 0, 0, 1, 1 + $i, 2024);
                $daysOfWeek[] = ucfirst($dayFormatter->format($timestamp));
            }

            // 🟢 RÉCUPÉRATION DES TRADUCTIONS STATIC (.CONF) VIA SMARTY
            $seoPatternTitle = $this->view->getConfigVars('calendar_seo_title');
            if (empty($seoPatternTitle)) {
                $seoPatternTitle = 'Agenda : %s - ' . $siteName; // Fallback
            }

            $seoPatternDesc = $this->view->getConfigVars('calendar_seo_desc');
            if (empty($seoPatternDesc)) {
                $seoPatternDesc = 'Consultez notre calendrier des évènements pour %s.'; // Fallback
            }

            $seoTitle = sprintf($seoPatternTitle, $monthName);
            $seoDesc  = sprintf($seoPatternDesc, $monthName);

            $this->view->assign([
                'events'        => $events,
                'current_year'  => $year,
                'current_month' => $month,
                'days_of_week'  => $daysOfWeek,
                'seo_title'     => $seoTitle,
                'seo_desc'      => $seoDesc,
                'is_root'       => true
            ]);
        }

        $this->view->display('news/calendar.tpl', $cacheId);
    }
}