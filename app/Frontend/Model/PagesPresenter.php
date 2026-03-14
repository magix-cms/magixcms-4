<?php

declare(strict_types=1);

namespace App\Frontend\Model;

use App\Component\File\ImageTool;
use App\Component\Routing\UrlTool;

class PagesPresenter
{
    /**
     * 🟢 NOUVELLE SIGNATURE : Ajout du paramètre $skinFolder
     */
    public static function format(array $row, array $langContext, string $siteUrl, array $companyInfo = [], string $skinFolder = 'default'): array
    {
        $iso = $langContext['iso_lang'] ?? 'fr';
        $idPages = (int)($row['id_pages'] ?? 0);

        $data = [
            'id'        => $idPages,
            'id_parent' => (int)($row['id_parent'] ?? 0),
            'name'      => $row['name_pages'] ?? '',
            'longname'  => $row['longname_pages'] ?? '',
            'resume'    => $row['resume_pages'] ?? '',
            'content'   => $row['content_pages'] ?? '',
            'date'      => $row['last_update'] ?? $row['date_register'] ?? null,
            'link'      => [
                'label' => $row['link_label_pages'] ?? '',
                'title' => $row['link_title_pages'] ?? ''
            ]
        ];

        $urlTool = new UrlTool();
        $data['url'] = $urlTool->buildUrl([
            'type' => 'pages',
            'id'   => $idPages,
            'url'  => $row['url_pages'] ?? '',
            'iso'  => $iso,
            'date' => $data['date']
        ]);

        // 🟢 Transmission de $skinFolder
        $data['img'] = self::processImages($row, $idPages, $siteUrl, $skinFolder);

        $data['seo'] = [
            'title'       => !empty($row['seo_title_pages']) ? $row['seo_title_pages'] : $data['name'],
            'description' => !empty($row['seo_desc_pages']) ? $row['seo_desc_pages'] : strip_tags($data['resume'])
        ];

        $data['json_ld'] = self::generateJsonLd($data, $data['img'], $siteUrl, $companyInfo);

        $knownKeys = array_flip([
            'id_pages', 'id_parent', 'name_pages', 'longname_pages', 'resume_pages', 'content_pages',
            'last_update', 'date_register', 'link_label_pages', 'link_title_pages', 'url_pages',
            'seo_title_pages', 'seo_desc_pages', 'name_img', 'alt_img', 'title_img', 'caption_img',
            'id_lang', 'published_pages', 'id_img'
        ]);

        $extraData = array_diff_key($row, $knownKeys);

        if (!empty($extraData)) {
            $data = array_merge($data, $extraData);
        }

        return $data;
    }

    private static function processImages(array $row, int $idPages, string $siteUrl, string $skinFolder): array
    {
        $imageTool = new ImageTool();
        $altText   = !empty($row['alt_img']) ? $row['alt_img'] : ($row['name_pages'] ?? '');
        $titleText = !empty($row['title_img']) ? $row['title_img'] : ($row['name_pages'] ?? '');

        // 🟢 AUCUNE IMAGE EN BDD : CASCADE DE FALLBACK
        if (empty($row['name_img'])) {
            static $fallbackData = [];

            if (!isset($fallbackData[$skinFolder])) {
                $holderFilename = 'pages_medium.jpg';

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
        $baseDir = "{$siteUrl}/upload/pages/{$idPages}/";

        $processed = $imageTool->setModuleImages('pages', 'pages', $rawImages, $idPages, $baseDir);

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
        // ... (votre code JSON-LD reste inchangé) ...
        $typesMap = [
            'org'    => 'Organization', 'locb' => 'LocalBusiness', 'corp' => 'Corporation',
            'store'  => 'Store', 'food' => 'FoodEstablishment', 'place' => 'Place', 'person' => 'Person'
        ];
        $companyType = $typesMap[$companyInfo['type'] ?? 'org'] ?? 'Organization';

        $socials = [];
        $socialKeys = ['facebook', 'twitter', 'instagram', 'linkedin', 'youtube', 'github'];
        foreach ($socialKeys as $key) {
            if (!empty($companyInfo[$key])) {
                $socials[] = $companyInfo[$key];
            }
        }

        $publisher = [
            '@type' => $companyType,
            'name'  => $companyInfo['name'] ?? '',
        ];

        if (!empty($companyInfo['phone'])) $publisher['telephone'] = $companyInfo['phone'];
        if (!empty($companyInfo['tva'])) $publisher['vatID'] = $companyInfo['tva'];
        if (!empty($companyInfo['street']) && !empty($companyInfo['city'])) {
            $publisher['address'] = [
                '@type'           => 'PostalAddress',
                'streetAddress'   => $companyInfo['street'],
                'postalCode'      => $companyInfo['postcode'] ?? '',
                'addressLocality' => $companyInfo['city'],
                'addressCountry'  => 'BE'
            ];
        }
        if (!empty($socials)) $publisher['sameAs'] = $socials;

        $schema = [
            '@context'    => 'https://schema.org',
            '@type'       => 'WebPage',
            'name'        => $data['name'],
            'description' => trim(strip_tags($data['resume'] ?: $data['content'])),
            'url'         => $siteUrl . $data['url'],
            'publisher'   => $publisher
        ];

        if (!empty($imgData['default']['src'])) {
            $schema['image'] = $imgData['default']['src'];
        }

        return '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';
    }
}