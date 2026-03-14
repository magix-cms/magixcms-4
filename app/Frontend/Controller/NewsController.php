<?php

declare(strict_types=1);

namespace App\Frontend\Controller;

use App\Frontend\Db\NewsDb;
use App\Frontend\Db\CompanyDb;
use App\Frontend\Model\NewsPresenter;
use Magepattern\Component\HTTP\Request;
use App\Frontend\Model\SeoHelper;

class NewsController extends BaseController
{
    private NewsDb $db;

    public function run(): void
    {
        $this->db = new NewsDb();
        $id = Request::isGet('id') ? (int)$_GET['id'] : 0;

        if ($id > 0) {
            $this->renderSingle($id);
        } else {
            $this->renderList();
        }
    }

    private function renderSingle(int $id): void
    {
        $idLang = (int)($this->currentLang['id_lang'] ?? 1);
        $siteUrl = $this->view->getTemplateVars('site_url');

        $rawNews = $this->db->getNewsPage($id, $idLang);

        if (!$rawNews) {
            $this->render404();
            return;
        }

        // 🟢 Ajout pour Single
        $companyDb = new CompanyDb();
        $companyInfo = $companyDb->getCompanyInfo();
        $skinFolder = $this->siteSettings['theme']['value'] ?? 'default';

        $news = NewsPresenter::format($rawNews, $this->currentLang, $siteUrl, $companyInfo, $skinFolder);

        $news['gallery'] = [];
        $images = $this->db->getNewsImages($id, $idLang);
        foreach ($images as $imgRow) {
            $news['gallery'][] = NewsPresenter::format(array_merge($rawNews, $imgRow), $this->currentLang, $siteUrl, $companyInfo, $skinFolder)['img'];
        }

        $news['tags'] = $this->db->getNewsTags($id, $idLang);

        $this->view->assign([
            'news'      => $news,
            'seo_title' => $news['seo']['title'],
            'seo_desc'  => $news['seo']['description']
        ]);

        $this->view->display('news/single.tpl');
    }

    private function renderList(): void
    {
        $idLang = (int)($this->currentLang['id_lang'] ?? 1);
        $siteUrl = $this->view->getTemplateVars('site_url');
        $urlTool = new \App\Component\Routing\UrlTool();

        $filters = [];
        $idTag = Request::isGet('tag') ? (int)$_GET['tag'] : 0;
        $year  = Request::isGet('year') ? (int)$_GET['year'] : 0;
        $month = Request::isGet('month') ? (int)$_GET['month'] : 0;
        $page  = Request::isGet('p') ? (int)$_GET['p'] : 1;

        if ($idTag > 0) $filters['id_tag'] = $idTag;
        if ($year > 0)  $filters['year']   = $year;
        if ($month > 0) $filters['month']  = $month;
        $filters['page'] = $page;

        $dbResult = $this->db->getNewsList($idLang, $filters);
        $rawNewsList = $dbResult['items'] ?? [];
        $paginationData = $dbResult['pagination'] ?? [];

        $companyDb = new CompanyDb();
        $companyInfo = $companyDb->getCompanyInfo();
        $skinFolder = $this->siteSettings['theme']['value'] ?? 'default'; // 🟢 Ajout du skin

        $newsList = [];
        foreach ($rawNewsList as $newsRow) {
            $formatted = NewsPresenter::format($newsRow, $this->currentLang, $siteUrl, $companyInfo, $skinFolder);
            $formatted['tags'] = $this->db->getNewsTags((int)$formatted['id'], $idLang);
            $newsList[] = $formatted;
        }

        $jsonLd = SeoHelper::generateItemListJsonLd($newsList);

        $allTags = $this->db->getAllTags($idLang);
        foreach ($allTags as &$t) {
            $t['url'] = $urlTool->buildUrl([
                'type' => 'tag', 'id' => $t['id_tag'],
                'url' => $t['name_tag'], 'iso' => $this->currentLang['iso_lang']
            ]);
        }
        unset($t);

        $archives = $this->db->getArchives();
        foreach ($archives as &$a) {
            $a['url'] = $urlTool->buildUrl([
                'type' => 'date', 'year' => $a['year'], 'month' => $a['month'],
                'iso' => $this->currentLang['iso_lang']
            ]);
            $a['dummy_date'] = sprintf('%04d-%02d-01', $a['year'], $a['month']);
        }
        unset($a);

        $seoTitle = 'Actualités';
        $tagName = '';
        if ($idTag > 0) {
            $tagName = $this->db->getTagName($idTag, $idLang);
            $seoTitle .= ' - Tag : ' . $tagName;
        }
        if ($year > 0)  $seoTitle .= " - Archives {$year}";
        if ($month > 0) $seoTitle .= "/" . str_pad((string)$month, 2, '0', STR_PAD_LEFT);

        $resetUrl = $urlTool->buildUrl(['type' => 'news', 'iso' => $this->currentLang['iso_lang']]);

        $currentUrl = $_SERVER['REQUEST_URI'] ?? '/';
        $currentUrl = preg_replace('/([?&])p=[0-9]+&?/', '$1', $currentUrl);
        $currentUrl = rtrim($currentUrl, '?&');
        $sep = str_contains($currentUrl, '?') ? '&' : '?';
        $pageUrlBase = $currentUrl . $sep . 'p=';

        $this->view->assign([
            'news_list'     => $newsList,
            'json_ld'       => $jsonLd,
            'all_tags'      => $allTags,
            'archives'      => $archives,
            'current_tag'   => $idTag,
            'current_year'  => $year,
            'current_month' => $month,
            'reset_url'     => $resetUrl,
            'seo_title'     => $seoTitle,
            'seo_desc'      => 'Découvrez toutes nos actualités et événements.',
            'pagination'    => $paginationData,
            'page_url_base' => $pageUrlBase
        ]);

        $this->view->display('news/index.tpl');
    }

    private function render404(): void
    {
        header("HTTP/1.0 404 Not Found");
        $tpl = 'errors/404.tpl';
        if ($this->view->templateExists($tpl)) $this->view->display($tpl);
        else die("Erreur 404 : Template manquant.");
    }
}