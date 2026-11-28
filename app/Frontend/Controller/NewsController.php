<?php

declare(strict_types=1);

namespace App\Frontend\Controller;

use App\Frontend\Db\NewsDb;
use App\Frontend\Db\CompanyDb;
use App\Frontend\Model\NewsPresenter;
use Magepattern\Component\HTTP\Request;
use App\Frontend\Model\SeoHelper;
use App\Component\Routing\UrlTool;
use Magepattern\Component\HTTP\Url;

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

        // 1. VÉRIFICATION STRICTE DE L'URL ET REDIRECTION 301
        $rawNews = $this->db->getNewsPage($id, $idLang);

        if (!$rawNews) {
            $this->render404();
        }

        $urlTool = new UrlTool();
        $isoLang = strtolower($this->currentLang['iso_lang'] ?? 'fr');

        // On utilise la clé 'news' et on passe la date pour que UrlTool ajoute les slashes /YYYY/MM/DD/
        $officialUrl = $urlTool->buildUrl([
            'type' => 'news',
            'id'   => $id,
            'url'  => $rawNews['url_news'] ?? '',
            'iso'  => $isoLang,
            'date' => $rawNews['date_publish'] ?? ''
        ]);

        $currentUri = explode('?', $_SERVER['REQUEST_URI'])[0];
        $officialUri = parse_url($officialUrl, PHP_URL_PATH);

        if ($currentUri !== $officialUri) {
            header("HTTP/1.1 301 Moved Permanently");
            header("Location: " . $officialUrl);
            exit;
        }

        // 2. GESTION DU CACHE
        $cacheId = md5('news_single_' . $idLang . '_' . $_SERVER['REQUEST_URI']);

        if (!$this->view->isCached('news/single.tpl', $cacheId)) {

            $siteUrl = rtrim((string)$this->view->getTemplateVars('site_url'), '/');
            $companyDb = new CompanyDb();
            $companyInfo = $companyDb->getCompanyInfo() ?: [];
            $skinFolder = $this->siteSettings['theme']['value'] ?? 'default';

            $news = NewsPresenter::format($rawNews, $this->currentLang, $siteUrl, $companyInfo, $skinFolder);

            $news['gallery'] = [];
            if (!empty($rawNews['name_img']) && !empty($news['img'])) {
                $news['gallery'][] = $news['img'];
            }

            $images = $this->db->getNewsImages($id, $idLang);
            if ($images) {
                foreach ($images as $imgRow) {
                    $formattedImg = NewsPresenter::format(array_merge($rawNews, $imgRow), $this->currentLang, $siteUrl, $companyInfo, $skinFolder);
                    if (!empty($formattedImg['img'])) {
                        $news['gallery'][] = $formattedImg['img'];
                    }
                }
            }

            $tags = $this->db->getNewsTags($id, $idLang);
            if ($tags) {
                foreach ($tags as &$tag) {
                    $tag['slug'] = Url::clean($tag['name_tag']);
                }
                unset($tag);
            }
            $news['tags'] = $tags;

            $allLangs = $this->view->getTemplateVars('langs');
            $hreflangUrls = [];

            if ($allLangs && is_array($allLangs)) {
                foreach ($allLangs as $l) {
                    $lId = (int)$l['id_lang'];
                    $lIso = strtolower($l['iso_lang']);

                    $translatedNews = $this->db->getNewsPage($id, $lId);

                    if ($translatedNews && !empty($translatedNews['url_news'])) {
                        // Utilisation correcte de UrlTool pour les traductions aussi
                        $hreflangUrls[$lId] = $urlTool->buildUrl([
                            'type' => 'news',
                            'id'   => $id,
                            'url'  => $translatedNews['url_news'],
                            'iso'  => $lIso,
                            'date' => $translatedNews['date_publish'] ?? ''
                        ]);

                        if (isset($l['is_default']) && $l['is_default'] == 1) {
                            $this->view->assign('x_default_url', $hreflangUrls[$lId]);
                        }
                    }
                }
            }

            $this->view->assign('canonical_url', $officialUrl);

            $this->view->assign([
                'news'      => $news,
                'seo_title' => $news['seo']['title'] ?? '',
                'seo_desc'  => $news['seo']['description'] ?? '',
                'hreflang'  => $hreflangUrls
            ]);
        }

        $this->view->display('news/single.tpl', $cacheId);
    }

    private function renderList(): void
    {
        // PROTECTION SEO : Si le .htaccess a envoyé des paramètres parasites sur la liste
        if (isset($_GET['action']) || isset($_GET['hash']) || isset($_GET['id'])) {
            $this->render404();
        }

        $idLang = (int)($this->currentLang['id_lang'] ?? 1);
        $cacheId = md5('news_list_' . $idLang . '_' . $_SERVER['REQUEST_URI']);

        if (!$this->view->isCached('news/index.tpl', $cacheId)) {

            $idLang = (int)($this->currentLang['id_lang'] ?? 1);
            $siteUrl = rtrim((string)$this->view->getTemplateVars('site_url'), '/');
            $iso = strtolower($this->currentLang['iso_lang'] ?? 'fr');
            $baseNewsUrl = $siteUrl . '/' . $iso . '/news/';

            $page  = Request::isGet('p') ? (int)$_GET['p'] : 1;
            $idTag = Request::isGet('tag') ? (int)$_GET['tag'] : null;
            $year  = Request::isGet('year') ? (int)$_GET['year'] : null;
            $month = Request::isGet('month') ? (int)$_GET['month'] : null;

            $data = $this->db->getNewsList($idLang, ['page' => $page, 'limit' => 12, 'tag' => $idTag, 'year' => $year, 'month' => $month]);
            $rawList = $data['items'] ?? [];
            $pagination = $data['pagination'] ?? [];

            $companyDb = new CompanyDb();
            $companyInfo = $companyDb->getCompanyInfo() ?: [];

            $siteName = $companyInfo['name'] ?? 'MagixCMS';

            $skinFolder = $this->siteSettings['theme']['value'] ?? 'default';

            $newsList = [];
            $itemListElements = [];
            $position = 1;

            foreach ($rawList as $raw) {
                $formatted = NewsPresenter::format($raw, $this->currentLang, $siteUrl, $companyInfo, $skinFolder);

                $tags = $this->db->getNewsTags((int)$raw['id_news'], $idLang);
                if ($tags) {
                    foreach ($tags as &$tag) {
                        $tag['slug'] = Url::clean($tag['name_tag']);
                    }
                    unset($tag);
                }
                $formatted['tags'] = $tags;

                $newsList[] = $formatted;
                if (!empty($formatted['schema_raw'])) {
                    $itemListElements[] = ['@type' => 'ListItem', 'position' => $position++, 'item' => $formatted['schema_raw']];
                }
            }

            $jsonLd = '';
            if (!empty($itemListElements)) {
                $itemListSchema = ['@context' => 'https://schema.org', '@type' => 'ItemList', 'itemListElement' => $itemListElements];
                $jsonLd = '<script type="application/ld+json">' . "\n" . json_encode($itemListSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) . "\n" . '</script>';
            }

            $allTags = $this->db->getAllTags($idLang);
            if ($allTags) {
                foreach ($allTags as &$t) {
                    $slug = Url::clean($t['name_tag']);
                    $t['url'] = $baseNewsUrl . 'tag/' . $t['id_tag'] . '-' . $slug . '/';
                }
                unset($t);
            }

            $archives = $this->db->getArchives();
            if ($archives) {
                foreach ($archives as &$a) {
                    $monthPad = str_pad((string)$a['month'], 2, '0', STR_PAD_LEFT);
                    $a['dummy_date'] = $a['year'] . '-' . $monthPad . '-01';
                    $a['url'] = $baseNewsUrl . $a['year'] . '/' . $monthPad . '/';
                }
                unset($a);
            }

            $siteDesc = $this->siteSettings['site_description']['value'] ?? '';

            $newsHome = $this->db->getNewsHomeConfig($idLang);

            if (!is_array($newsHome)) {
                $newsHome = [];
            }

            $defaultNewsTitle = (string)($this->view->getConfigVars('news_default_title') ?: 'Actualités');

            $newsHome['content_page']   = $newsHome['content_page'] ?? '';
            $newsHome['title_page']     = !empty($newsHome['title_page']) ? $newsHome['title_page'] : $defaultNewsTitle;
            $newsHome['seo_title_page'] = $newsHome['seo_title_page'] ?? '';
            $newsHome['seo_desc_page']  = $newsHome['seo_desc_page'] ?? '';

            $baseTitle = !empty($newsHome['seo_title_page'])
                ? $newsHome['seo_title_page']
                : $newsHome['title_page'] . ' - ' . $siteName;
            $baseDesc = !empty($newsHome['seo_desc_page']) ? $newsHome['seo_desc_page'] : $siteDesc;

            $seoTitle   = $baseTitle;
            $seoDesc    = $baseDesc;
            $isRootNews = false;

            if ($idTag) {
                $tagName = $this->db->getTagName($idTag, $idLang);
                if ($tagName) {
                    $tagTitlePattern = (string)($this->view->getConfigVars('news_tag_seo_title') ?: 'Tag : %s');
                    $tagDescPattern  = (string)($this->view->getConfigVars('news_tag_seo_desc') ?: 'Découvrez toutes nos actualités liées au tag %s.');

                    $seoTitle = sprintf($tagTitlePattern, $tagName) . ' - ' . $baseTitle;
                    $seoDesc  = trim(sprintf($tagDescPattern, $tagName) . ' ' . $baseDesc);
                }
            } elseif ($year && $month) {
                $formatter = new \IntlDateFormatter($iso, \IntlDateFormatter::FULL, \IntlDateFormatter::NONE, null, null, 'MMMM yyyy');
                $monthYearLabel = ucfirst((string)$formatter->format(mktime(0, 0, 0, (int)$month, 1, (int)$year)));

                $archiveTitlePattern = (string)($this->view->getConfigVars('news_archive_seo_title') ?: 'Archives %s');
                $archiveDescPattern  = (string)($this->view->getConfigVars('news_archive_seo_desc') ?: 'Retrouvez tous nos articles publiés en %s.');

                $seoTitle = sprintf($archiveTitlePattern, $monthYearLabel) . ' - ' . $baseTitle;
                $seoDesc  = trim(sprintf($archiveDescPattern, $monthYearLabel) . ' ' . $baseDesc);
            } else {
                if ($page === 1) {
                    $isRootNews = true;
                } else {
                    $pageLabel = (string)($this->view->getConfigVars('seo_page_label') ?: 'Page');
                    $seoTitle = $baseTitle . ' - ' . $pageLabel . ' ' . $page;
                }
            }

            $currentUrl = $_SERVER['REQUEST_URI'];
            $currentUrl = preg_replace('/([?&])p=[0-9]+&?/', '$1', $currentUrl);
            $currentUrl = rtrim($currentUrl, '?&');
            $sep = str_contains($currentUrl, '?') ? '&' : '?';
            $pageUrlBase = $currentUrl . $sep . 'p=';

            $urlTool = new UrlTool();
            $allLangs = $this->view->getTemplateVars('langs');
            $hreflangUrls = [];
            if ($allLangs && is_array($allLangs)) {
                foreach ($allLangs as $l) {
                    $hreflangUrls[(int)$l['id_lang']] = $urlTool->buildUrl(['type' => 'news', 'iso' => strtolower($l['iso_lang'])]);
                }
            }

            $this->view->assign([
                'news_list'     => $newsList,
                'json_ld'       => $jsonLd,
                'all_tags'      => $allTags,
                'archives'      => $archives,
                'current_tag'   => $idTag,
                'current_year'  => $year,
                'current_month' => $month,
                'reset_url'     => $baseNewsUrl,
                'seo_title'     => $seoTitle,
                'seo_desc'      => $seoDesc,
                'news_home'     => $newsHome,
                'is_root'       => $isRootNews,
                'pagination'    => $pagination,
                'page_url_base' => $pageUrlBase,
                'hreflang'      => $hreflangUrls
            ]);
        }

        $this->view->display('news/index.tpl', $cacheId);
    }
}