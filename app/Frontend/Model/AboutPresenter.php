<?php

declare(strict_types=1);

namespace App\Frontend\Model;

use App\Component\File\ImageTool;
use App\Component\Routing\UrlTool;

class AboutPresenter
{
    /**
     * 🟢 NOUVELLE SIGNATURE : Ajout du paramètre $companyInfo
     */
    public static function format(array $row, array $langContext, string $siteUrl, array $companyInfo = [], string $skinFolder = 'default'): array
    {
        $iso = $langContext['iso_lang'] ?? 'fr';
        $idAbout = (int)($row['id_about'] ?? 0);

        $data = [
            'id'        => $idAbout,
            'id_parent' => (int)($row['id_parent'] ?? 0),
            'name'      => $row['name_about'] ?? '',
            'longname'  => $row['longname_about'] ?? '',
            'resume'    => $row['resume_about'] ?? '',
            'content'   => $row['content_about'] ?? '',
            'date'      => $row['last_update'] ?? $row['date_register'] ?? null,
            'link'      => [
                'label' => $row['link_label_about'] ?? '',
                'title' => $row['link_title_about'] ?? ''
            ]
        ];

        $urlTool = new UrlTool();
        $data['url'] = $urlTool->buildUrl([
            'type' => 'about',
            'id'   => $idAbout,
            'url'  => $row['url_about'] ?? '',
            'iso'  => $iso,
            'date' => $data['date']
        ]);

        $data['img'] = self::processImages($row, $idAbout, $siteUrl, $skinFolder);

        $data['seo'] = [
            'title'       => !empty($row['seo_title_about']) ? $row['seo_title_about'] : $data['name'],
            'description' => !empty($row['seo_desc_about']) ? $row['seo_desc_about'] : strip_tags($data['resume'])
        ];

        // 🟢 GÉNÉRATION DU JSON-LD
        $data['json_ld'] = self::generateJsonLd($data, $data['img'], $siteUrl, $companyInfo);

        // =====================================================================
        // 🟢 OVERRIDE : Extraction des champs de plugins
        // =====================================================================
        $knownKeys = array_flip([
            'id_about', 'id_parent', 'name_about', 'longname_about', 'resume_about', 'content_about',
            'last_update', 'date_register', 'link_label_about', 'link_title_about', 'url_about',
            'seo_title_about', 'seo_desc_about', 'name_img', 'alt_img', 'title_img', 'caption_img',
            'id_lang', 'published_about', 'id_img'
        ]);

        $extraData = array_diff_key($row, $knownKeys);

        if (!empty($extraData)) {
            $data = array_merge($data, $extraData);
        }

        return $data;
    }

    private static function processImages(array $row, int $idAbout, string $siteUrl, string $skinFolder): array
    {
        $imageTool = new ImageTool();
        $altText   = !empty($row['alt_img']) ? $row['alt_img'] : ($row['name_about'] ?? '');
        $titleText = !empty($row['title_img']) ? $row['title_img'] : ($row['name_about'] ?? '');

        // 🟢 AUCUNE IMAGE EN BDD : CASCADE DE FALLBACK
        if (empty($row['name_img'])) {

            // Cache statique basé sur le nom du skin
            static $fallbackData = [];

            if (!isset($fallbackData[$skinFolder])) {
                $holderFilename = 'about_medium.jpg';

                // Chemins physiques
                $generatedPath = ROOT_DIR . 'img/default/' . $holderFilename;

                // ⚠️ Attention : vous aviez écrit 'img' au lieu de 'images' dans votre code précédent.
                // Assurez-vous d'utiliser le bon nom de dossier de votre skin !
                $skinPath = ROOT_DIR . 'skin/' . $skinFolder . '/img/default/' . $holderFilename;

                if (file_exists($generatedPath)) {
                    $src = "{$siteUrl}/img/default/{$holderFilename}";
                    $size = getimagesize($generatedPath);
                } else {
                    $src = "{$siteUrl}/skin/{$skinFolder}/img/default/{$holderFilename}";
                    $size = @getimagesize($skinPath);
                }

                // Mise en cache
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

        // --- TRAITEMENT NORMAL SI UNE IMAGE EXISTE ---
        $rawImages = [['name_img' => $row['name_img'], 'alt_img' => $altText, 'title_img' => $titleText]];
        $baseDir = "{$siteUrl}/upload/about/{$idAbout}/";

        $processed = $imageTool->setModuleImages('about', 'about', $rawImages, $idAbout, $baseDir);

        $imgData = $processed[0]['img'] ?? [];
        $imgData['alt'] = $altText;
        $imgData['title'] = $titleText;

        if (isset($imgData['original']) && !isset($imgData['default'])) {
            $imgData['default'] = $imgData['original'];
        }

        return $imgData;
    }

    /**
     * 🟢 GÉNÉRATION DU JSON-LD
     */
    private static function generateJsonLd(array $data, array $imgData, string $siteUrl, array $companyInfo = []): string
    {
        // 1. Détermination du type d'entité Schema.org
        $typesMap = [
            'org'    => 'Organization',
            'locb'   => 'LocalBusiness',
            'corp'   => 'Corporation',
            'store'  => 'Store',
            'food'   => 'FoodEstablishment',
            'place'  => 'Place',
            'person' => 'Person'
        ];
        $companyType = $typesMap[$companyInfo['type'] ?? 'org'] ?? 'Organization';

        $socials = [];
        $socialKeys = ['facebook', 'twitter', 'instagram', 'linkedin', 'youtube', 'github'];
        foreach ($socialKeys as $key) {
            if (!empty($companyInfo[$key])) {
                $socials[] = $companyInfo[$key];
            }
        }

        // 2. Construction de l'objet Éditeur
        $publisher = [
            '@type' => $companyType,
            'name'  => $companyInfo['name'] ?? '',
        ];

        if (!empty($companyInfo['phone'])) {
            $publisher['telephone'] = $companyInfo['phone'];
        }
        if (!empty($companyInfo['tva'])) {
            $publisher['vatID'] = $companyInfo['tva'];
        }
        if (!empty($companyInfo['street']) && !empty($companyInfo['city'])) {
            $publisher['address'] = [
                '@type'           => 'PostalAddress',
                'streetAddress'   => $companyInfo['street'],
                'postalCode'      => $companyInfo['postcode'] ?? '',
                'addressLocality' => $companyInfo['city'],
                'addressCountry'  => 'BE'
            ];
        }
        if (!empty($socials)) {
            $publisher['sameAs'] = $socials;
        }

        // 3. Construction de la Page Web globale
        $schema = [
            '@context'    => 'https://schema.org',
            '@type'       => 'WebPage',
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