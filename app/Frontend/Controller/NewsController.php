<?php

declare(strict_types=1);

namespace App\Frontend\Controller;

use App\Frontend\Db\NewsDb;
use App\Frontend\Model\NewsPresenter;
use Magepattern\Component\HTTP\Request;

class NewsController extends BaseController
{
    private NewsDb $db;

    public function run(): void
    {
        $this->db = new NewsDb();

        // On analyse l'URL pour savoir dans quel mode on se trouve
        $id = Request::isGet('id') ? (int)$_GET['id'] : 0;

        if ($id > 0) {
            $this->renderSingle($id);
        } else {
            $this->renderList();
        }
    }

    /**
     * AFFICHE UNE ACTUALITÉ PRÉCISE
     */
    private function renderSingle(int $id): void
    {
        $idLang = (int)($this->currentLang['id_lang'] ?? 1);
        $siteUrl = $this->view->getTemplateVars('site_url');

        $rawNews = $this->db->getNewsPage($id, $idLang);

        if (!$rawNews) {
            $this->render404();
            return;
        }

        $news = NewsPresenter::format($rawNews, $this->currentLang, $siteUrl);

        // Récupération de la galerie
        $news['gallery'] = [];
        $images = $this->db->getNewsImages($id, $idLang);
        foreach ($images as $imgRow) {
            $news['gallery'][] = NewsPresenter::format(array_merge($rawNews, $imgRow), $this->currentLang, $siteUrl)['img'];
        }

        // Récupération des tags pour cet article
        $news['tags'] = $this->db->getNewsTags($id, $idLang);

        $this->view->assign([
            'news'      => $news,
            'seo_title' => $news['seo']['title'],
            'seo_desc'  => $news['seo']['description']
        ]);

        $this->view->display('news/single.tpl');
    }

    /**
     * AFFICHE LA LISTE (Root, Tags, Archives)
     */
    private function renderList(): void
    {
        $idLang = (int)($this->currentLang['id_lang'] ?? 1);
        $siteUrl = $this->view->getTemplateVars('site_url');

        // Récupération des filtres depuis l'URL (géré par votre .htaccess)
        $filters = [];
        $idTag = Request::isGet('tag') ? (int)$_GET['tag'] : 0;
        $year = Request::isGet('year') ? (int)$_GET['year'] : 0;
        $month = Request::isGet('month') ? (int)$_GET['month'] : 0;

        if ($idTag > 0) $filters['id_tag'] = $idTag;
        if ($year > 0)  $filters['year']   = $year;
        if ($month > 0) $filters['month']  = $month;

        // Appel de la méthode qui gère nativement les overrides de listes !
        $rawNewsList = $this->db->getNewsList($idLang, $filters);

        $newsList = [];
        foreach ($rawNewsList as $newsRow) {
            $formatted = NewsPresenter::format($newsRow, $this->currentLang, $siteUrl);

            // On peut même ajouter les tags dans les cartes de la liste si besoin
            $formatted['tags'] = $this->db->getNewsTags((int)$formatted['id'], $idLang);

            $newsList[] = $formatted;
        }

        // On crée un titre SEO dynamique basé sur les filtres appliqués
        $seoTitle = 'Actualités';
        if ($idTag > 0) $seoTitle .= ' - Tag'; // Idéalement, faire une req. pour récupérer le nom du tag
        if ($year > 0)  $seoTitle .= " - Archives {$year}";
        if ($month > 0) $seoTitle .= "/" . str_pad((string)$month, 2, '0', STR_PAD_LEFT);

        $this->view->assign([
            'news_list' => $newsList,
            'seo_title' => $seoTitle,
            'seo_desc'  => 'Découvrez toutes nos actualités et événements.'
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