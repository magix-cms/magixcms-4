<?php

declare(strict_types=1);

namespace App\Frontend\Model;

use App\Component\File\ImageTool;
use App\Component\Routing\UrlTool;

class CategoryPresenter
{
    /**
     * 🟢 NOUVELLE SIGNATURE : Ajout du paramètre $skinFolder
     */
    public static function format(array $row, array $langContext, string $siteUrl, array $companyInfo = [], string $skinFolder = 'default'): array
    {
        $iso = $langContext['iso_lang'] ?? 'fr';
        $idCat = (int)($row['id_cat'] ?? 0);

        $data = [
            'id'        => $idCat,
            'id_parent' => (int)($row['id_parent'] ?? 0),
            'name'      => $row['name_cat'] ?? '',
            'longname'  => $row['longname_cat'] ?? '',
            'resume'    => $row['resume_cat'] ?? '',
            'content'   => $row['content_cat'] ?? '',
            'date'      => $row['last_update'] ?? $row['date_register'] ?? null,
            'link'      => [
                'label' => $row['link_label_cat'] ?? '',
                'title' => $row['link_title_cat'] ?? ''
            ]
        ];

        $urlTool = new UrlTool();
        $data['url'] = $urlTool->buildUrl([
            'type' => 'category',
            'id'   => $idCat,
            'url'  => $row['url_cat'] ?? '',
            'iso'  => $iso,
            'date' => $data['date']
        ]);

        // 🟢 Transmission de $skinFolder
        $data['img'] = self::processImages($row, $idCat, $siteUrl, $skinFolder);

        $data['seo'] = [
            'title'       => !empty($row['seo_title_cat']) ? $row['seo_title_cat'] : $data['name'],
            'description' => !empty($row['seo_desc_cat']) ? $row['seo_desc_cat'] : strip_tags($data['resume'])
        ];

        $data['json_ld'] = self::generateJsonLd($data, $data['img'], $siteUrl, $companyInfo);

        $knownKeys = array_flip([
            'id_cat', 'id_parent', 'name_cat', 'longname_cat', 'resume_cat', 'content_cat',
            'last_update', 'date_register', 'link_label_cat', 'link_title_cat', 'url_cat',
            'seo_title_cat', 'seo_desc_cat', 'name_img', 'alt_img', 'title_img', 'caption_img',
            'id_lang', 'published_cat', 'id_img'
        ]);

        $extraData = array_diff_key($row, $knownKeys);

        if (!empty($extraData)) {
            $data = array_merge($data, $extraData);
        }

        return $data;
    }

    private static function processImages(array $row, int $idCat, string $siteUrl, string $skinFolder): array
    {
        $imageTool = new ImageTool();
        $altText   = !empty($row['alt_img']) ? $row['alt_img'] : ($row['name_cat'] ?? '');
        $titleText = !empty($row['title_img']) ? $row['title_img'] : ($row['name_cat'] ?? '');

        // 🟢 AUCUNE IMAGE EN BDD : CASCADE DE FALLBACK
        if (empty($row['name_img'])) {
            static $fallbackData = [];

            if (!isset($fallbackData[$skinFolder])) {
                $holderFilename = 'category_medium.jpg';

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
        $baseDir = "{$siteUrl}/upload/category/{$idCat}/";

        $processed = $imageTool->setModuleImages('catalog', 'category', $rawImages, $idCat, $baseDir);

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
        $typesMap = [
            'org'    => 'Organization', 'locb' => 'LocalBusiness', 'corp' => 'Corporation',
            'store'  => 'Store', 'food' => 'FoodEstablishment', 'place' => 'Place', 'person' => 'Person'
        ];
        $companyType = $typesMap[$companyInfo['type'] ?? 'org'] ?? 'Organization';

        $publisher = [
            '@type' => $companyType,
            'name'  => $companyInfo['name'] ?? '',
        ];

        $schema = [
            '@context'    => 'https://schema.org',
            '@type'       => 'CollectionPage',
            'name'        => $data['name'],
            'description' => trim(strip_tags($data['resume'] ?: $data['content'])),
            'url'         => $siteUrl . $data['url'],
            'publisher'   => $publisher
        ];

        if (!empty($imgData['default']['src'])) {
            $imageUrl = $imgData['default']['src'];

            // 1. Pour Schema.org (L'objet complet)
            $schema['primaryImageOfPage'] = [
                '@type' => 'ImageObject',
                'url'   => $imageUrl
            ];

            // 2. Pour Google (Un tableau contenant l'URL brute)
            $schema['image'] = [$imageUrl];
        }

        return '<script type="application/ld+json">' . "\n" . json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n" . '</script>';
    }
}