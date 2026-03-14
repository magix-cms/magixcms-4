<?php

declare(strict_types=1);

namespace App\Frontend\Model;

use App\Component\File\ImageTool;
use App\Component\Routing\UrlTool;

class NewsPresenter
{
    /**
     * 🟢 AJOUT : $companyInfo et $skinFolder
     */
    public static function format(array $row, array $langContext, string $siteUrl, array $companyInfo = [], string $skinFolder = 'default'): array
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

        $urlTool = new UrlTool();
        $data['url'] = $urlTool->buildUrl([
            'type' => 'news',
            'id'   => $idNews,
            'url'  => $row['url_news'] ?? '',
            'iso'  => $iso,
            'date' => $data['date_publish']
        ]);

        // 🟢 Transmission de $skinFolder
        $data['img'] = self::processImages($row, $idNews, $siteUrl, $skinFolder);

        $data['seo'] = [
            'title'       => !empty($row['seo_title_news']) ? $row['seo_title_news'] : $data['name'],
            'description' => !empty($row['seo_desc_news']) ? $row['seo_desc_news'] : strip_tags($data['resume'])
        ];

        // 🟢 JSON-LD mis à jour avec $companyInfo
        $data['json_ld'] = self::generateJsonLd($data, $data['img'], $siteUrl, $companyInfo);

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

    private static function processImages(array $row, int $idNews, string $siteUrl, string $skinFolder): array
    {
        $imageTool = new ImageTool();
        $altText   = !empty($row['alt_img']) ? $row['alt_img'] : ($row['name_news'] ?? '');
        $titleText = !empty($row['title_img']) ? $row['title_img'] : ($row['name_news'] ?? '');

        // 🟢 CASCADE DE FALLBACK
        if (empty($row['name_img'])) {
            static $fallbackData = [];

            if (!isset($fallbackData[$skinFolder])) {
                $holderFilename = 'news_medium.jpg';

                $generatedPath = ROOT_DIR . 'img/default/' . $holderFilename;
                $skinPath      = ROOT_DIR . 'skin/' . $skinFolder . '/img/default/' . $holderFilename;

                if (file_exists($generatedPath)) {
                    $src = "{$siteUrl}/img/default/{$holderFilename}";
                    $size = getimagesize($generatedPath);
                } else {
                    $src = "{$siteUrl}/skin/{$skinFolder}/img/default/{$holderFilename}";
                    $size = @getimagesize($skinPath);
                }

                $fallbackData[$skinFolder] = [
                    'src' => $src,
                    'w'   => $size ? $size[0] : 800,
                    'h'   => $size ? $size[1] : 600
                ];
            }

            return [
                'alt'   => $altText,
                'title' => $titleText,
                'default' => $fallbackData[$skinFolder]
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

    private static function generateJsonLd(array $data, array $imgData, string $siteUrl, array $companyInfo = []): string
    {
        $imageUrl = $imgData['default']['src'] ?? '';

        // Éditeur (Important pour les NewsArticles SEO)
        $publisher = [];
        if (!empty($companyInfo['name'])) {
            $typesMap = ['org' => 'Organization', 'locb' => 'LocalBusiness', 'corp' => 'Corporation'];
            $publisherType = $typesMap[$companyInfo['type'] ?? 'org'] ?? 'Organization';

            $publisher = [
                '@type' => $publisherType,
                'name'  => $companyInfo['name'],
            ];
            // Google requiert un logo pour l'éditeur d'un article
            $logoSrc = "{$siteUrl}/skin/default/images/logo.png"; // Fallback générique
            if (file_exists(ROOT_DIR . 'img/logo/logo.png')) {
                $logoSrc = "{$siteUrl}/img/logo/logo.png";
            }
            $publisher['logo'] = ['@type' => 'ImageObject', 'url' => $logoSrc];
        }

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
            if (!empty($publisher)) $schema['organizer'] = $publisher;
            if (!empty($data['date_end'])) $schema['endDate'] = date('c', strtotime($data['date_end']));
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
            if (!empty($publisher)) $schema['publisher'] = $publisher;
        }

        return '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';
    }
}