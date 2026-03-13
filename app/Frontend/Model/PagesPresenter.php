<?php

declare(strict_types=1);

namespace App\Frontend\Model;

use App\Component\File\ImageTool;
use App\Component\Routing\UrlTool;

class PagesPresenter
{
    /**
     * Formate une ligne de résultat Pages pour le frontend
     */
    public static function format(array $row, array $langContext, string $siteUrl, array $companyInfo = []): array
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

        // Construction de l'URL
        $urlTool = new UrlTool();
        $data['url'] = $urlTool->buildUrl([
            'type' => 'pages',
            'id'   => $idPages,
            'url'  => $row['url_pages'] ?? '',
            'iso'  => $iso,
            'date' => $data['date']
        ]);

        // --- GESTION DES IMAGES ---
        $data['img'] = self::processImages($row, $idPages, $siteUrl);

        // --- SEO ---
        $data['seo'] = [
            'title'       => !empty($row['seo_title_pages']) ? $row['seo_title_pages'] : $data['name'],
            'description' => !empty($row['seo_desc_pages']) ? $row['seo_desc_pages'] : strip_tags($data['resume'])
        ];

        // 🟢 GÉNÉRATION DU JSON-LD AVEC INFOS ENTREPRISE
        $data['json_ld'] = self::generateJsonLd($data, $data['img'], $siteUrl, $companyInfo);

        // =====================================================================
        // 🟢 OVERRIDE (Capture des champs des plugins)
        // =====================================================================
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

    /**
     * Traitement des images
     */
    private static function processImages(array $row, int $idPages, string $siteUrl): array
    {
        $imageTool = new ImageTool();
        $altText   = !empty($row['alt_img']) ? $row['alt_img'] : ($row['name_pages'] ?? '');
        $titleText = !empty($row['title_img']) ? $row['title_img'] : ($row['name_pages'] ?? '');

        if (empty($row['name_img'])) {
            return [
                'alt' => $altText, 'title' => $titleText,
                'default' => ['src' => "{$siteUrl}/skin/default/images/no-image.jpg", 'w' => 800, 'h' => 800]
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

    /**
     * 🟢 GÉNÉRATION DU JSON-LD
     */
    private static function generateJsonLd(array $data, array $imgData, string $siteUrl, array $companyInfo = []): string    {
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

        // 2. Liste des réseaux sociaux pour le tableau "sameAs"
        $socials = [];
        $socialKeys = ['facebook', 'twitter', 'instagram', 'linkedin', 'youtube', 'github'];
        foreach ($socialKeys as $key) {
            if (!empty($companyInfo[$key])) {
                $socials[] = $companyInfo[$key];
            }
        }

        // 3. Construction de l'objet Éditeur / Entreprise
        $publisher = [
            '@type' => $companyType,
            'name'  => $companyInfo['name'] ?? '',
        ];

        // Ajout des infos de contact si elles existent
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

        // 4. Construction de la Page Web globale
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