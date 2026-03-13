<?php

declare(strict_types=1);

namespace App\Frontend\Model;

use App\Component\File\ImageTool;
use App\Component\Routing\UrlTool;

class NewsPresenter
{
    public static function format(array $row, array $langContext, string $siteUrl): array
    {
        $iso = $langContext['iso_lang'] ?? 'fr';
        $idNews = (int)($row['id_news'] ?? 0);

        $data = [
            'id'           => $idNews,
            'name'         => $row['name_news'] ?? '',
            'longname'     => $row['longname_news'] ?? '',
            'resume'       => $row['resume_news'] ?? '',
            'content'      => $row['content_news'] ?? '',
            'date_publish' => $row['date_publish'] ?? null,
            'date_start'   => $row['date_event_start'] ?? null,
            'date_end'     => $row['date_event_end'] ?? null,
            'link'         => [
                'label' => $row['link_label_news'] ?? '',
                'title' => $row['link_title_news'] ?? ''
            ]
        ];

        // Construction de l'URL
        $urlTool = new UrlTool();
        $data['url'] = $urlTool->buildUrl([
            'type' => 'news',
            'id'   => $idNews,
            'url'  => $row['url_news'] ?? '',
            'iso'  => $iso,
            'date' => $data['date_publish'] // Utile si vos URL de news intègrent la date
        ]);

        // --- GESTION DES IMAGES ---
        $data['img'] = self::processImages($row, $idNews, $siteUrl);

        // --- SEO ---
        $data['seo'] = [
            'title'       => !empty($row['seo_title_news']) ? $row['seo_title_news'] : $data['name'],
            'description' => !empty($row['seo_desc_news']) ? $row['seo_desc_news'] : strip_tags($data['resume'])
        ];
        // JSON-LD
        $data['json_ld'] = self::generateJsonLd($data, $data['img'], $siteUrl);
        // =====================================================================
        // 🟢 LA MAGIE DE L'OVERRIDE (Capture des champs des plugins)
        // =====================================================================
        $knownKeys = array_flip([
            'id_news', 'date_publish', 'date_event_start', 'date_event_end', 'date_register',
            'id_content', 'id_lang', 'name_news', 'longname_news', 'url_news', 'resume_news',
            'content_news', 'link_label_news', 'link_title_news', 'seo_title_news', 'seo_desc_news',
            'last_update', 'published_news', 'name_img', 'alt_img', 'title_img', 'caption_img', 'id_img'
        ]);

        $extraData = array_diff_key($row, $knownKeys);

        if (!empty($extraData)) {
            $data = array_merge($data, $extraData);
        }

        return $data;
    }

    private static function processImages(array $row, int $idNews, string $siteUrl): array
    {
        $imageTool = new ImageTool();
        $altText   = !empty($row['alt_img']) ? $row['alt_img'] : ($row['name_news'] ?? '');
        $titleText = !empty($row['title_img']) ? $row['title_img'] : ($row['name_news'] ?? '');

        if (empty($row['name_img'])) {
            return [
                'alt' => $altText, 'title' => $titleText,
                'default' => ['src' => "{$siteUrl}/skin/default/images/no-image.jpg", 'w' => 800, 'h' => 800]
            ];
        }

        $rawImages = [['name_img' => $row['name_img'], 'alt_img' => $altText, 'title_img' => $titleText]];
        $baseDir = "{$siteUrl}/upload/news/{$idNews}/";

        $processed = $imageTool->setModuleImages('news', 'news', $rawImages, $idNews, $baseDir);

        $imgData = $processed[0]['img'] ?? [];
        $imgData['alt'] = $altText;
        $imgData['title'] = $titleText;

        if (isset($imgData['original']) && !isset($imgData['default'])) {
            $imgData['default'] = $imgData['original'];
        }

        return $imgData;
    }
    private static function generateJsonLd(array $data, array $imgData, string $siteUrl): string
    {
        $imageUrl = $imgData['default']['src'] ?? '';

        // Est-ce un évènement ou un article standard ?
        if (!empty($data['date_start'])) {
            $schema = [
                '@context'    => 'https://schema.org',
                '@type'       => 'Event',
                'name'        => $data['name'],
                'description' => trim(strip_tags($data['resume'] ?: $data['content'])),
                'image'       => $imageUrl,
                'startDate'   => date('c', strtotime($data['date_start'])),
                'url'         => $siteUrl . $data['url']
            ];

            if (!empty($data['date_end'])) {
                $schema['endDate'] = date('c', strtotime($data['date_end']));
            }
        } else {
            $schema = [
                '@context'      => 'https://schema.org',
                '@type'         => 'NewsArticle',
                'headline'      => $data['name'],
                'description'   => trim(strip_tags($data['resume'] ?: $data['content'])),
                'image'         => [$imageUrl],
                'datePublished' => date('c', strtotime($data['date_publish'] ?? 'now')),
                'url'           => $siteUrl . $data['url']
            ];
        }

        // json_encode gère nativement tous les échappements (guillemets, sauts de ligne) de manière sécurisée !
        return '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';
    }
}