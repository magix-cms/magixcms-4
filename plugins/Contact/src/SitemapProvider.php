<?php
declare(strict_types=1);

namespace Plugins\Contact\src;

class SitemapProvider
{
    /**
     * Retourne les URLs du plugin pour le sitemap du CMS
     */
    public function getUrls(int $idLang, string $iso, string $baseUrl): array
    {
        $urls = [];

        // 1. Ajouter la page principale du plugin
        $urls[] = [
            'loc'        => "/{$iso}/contact/",
            'date'       => 'now',
            'changefreq' => 'weekly',
            'priority'   => 0.8
        ];

        // 2. Fetcher les réalisations de ce plugin depuis la BDD (pseudo-code)
        // $db = new PortfolioDb();
        // $items = $db->getAllItems($idLang);
        // foreach($items as $item) {
        //     $urls[] = [
        //         'loc' => "/{$iso}/portfolio/{$item['id']}-{$item['url_rewriting']}/",
        //         'date' => $item['updated_at'],
        //         'changefreq' => 'monthly',
        //         'priority' => 0.6,
        //         'images' => [
        //             ['loc' => "{$baseUrl}/upload/portfolio/{$item['id']}/{$item['image']}", 'title' => $item['title']]
        //         ]
        //     ];
        // }

        return $urls;
    }
}