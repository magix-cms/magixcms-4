<?php

declare(strict_types=1);

namespace App\Frontend\Model;

use App\Component\File\ImageTool;
use App\Component\Routing\UrlTool; // 🟢 Import de l'UrlTool

class ProductPresenter
{
    public static function format(array $row, array $langContext, string $siteUrl, array $companyInfo = []): ?array
    {
        $idParent = $row['default_id_cat'] ?? $row['id_cat'] ?? 0;
        $urlCat   = $row['default_url_cat'] ?? $row['url_cat'] ?? '';

        if (empty($idParent) || empty($urlCat)) {
            return null;
        }

        $iso = $langContext['iso_lang'] ?? 'fr';

        $data = [
            'id'          => $row['id_product'] ?? null,
            'name'        => $row['name_p'] ?? '',
            'reference'   => $row['reference_p'] ?? '',
            'price'       => (float)($row['price_p'] ?? 0),
            'resume'      => $row['resume_p'] ?? '',
            'content'     => $row['content_p'] ?? '',
            'id_parent'   => $idParent,
            'cat_name'    => $row['name_cat'] ?? '',
        ];

        $urlTool = new UrlTool();

        $data['url_cat'] = $urlTool->buildUrl([
            'type' => 'category',
            'id'   => $idParent,
            'url'  => $urlCat,
            'iso'  => $iso
        ]);

        $data['url'] = $urlTool->buildUrl([
            'type'         => 'product',
            'id'           => $row['id_product'],
            'url'          => $row['url_p'] ?? '',
            'iso'          => $iso,
            'id_category'  => $idParent,
            'url_category' => $urlCat
        ]);

        $data['img'] = self::processImage($row, $siteUrl);

        $seoTitle = $row['seo_title_p'] ?? '';
        $data['seo_title'] = !empty($seoTitle) ? $seoTitle : $data['name'] . ' - ' . $data['cat_name'];

        // 🟢 2. On passe bien $companyInfo à generateJsonLd
        $data['json_ld'] = self::generateJsonLd($data, $data['img'], $siteUrl, $companyInfo);

        $knownKeys = array_flip([
            'id_product', 'name_p', 'reference_p', 'price_p', 'resume_p', 'content_p',
            'default_id_cat', 'id_cat', 'default_url_cat', 'url_cat', 'name_cat',
            'url_p', 'seo_title_p', 'seo_desc_p', 'name_img', 'alt_img', 'title_img',
            'id_lang', 'published_p', 'last_update', 'id_img'
        ]);

        $extraData = array_diff_key($row, $knownKeys);

        if (!empty($extraData)) {
            $data = array_merge($data, $extraData);
        }

        return $data;
    }

    private static function processImage(array $row, string $siteUrl): array
    {
        $altText = !empty($row['alt_img']) ? $row['alt_img'] : ($row['name_p'] ?? '');
        $titleText = !empty($row['title_img']) ? $row['title_img'] : ($row['name_p'] ?? '');

        if (empty($row['name_img'])) {
            return [
                'alt'     => $altText,
                'title'   => $titleText,
                'default' => [
                    'src' => "{$siteUrl}/skin/default/images/no-image.jpg",
                    'w'   => 800,
                    'h'   => 800,
                    'ext' => 'image/jpeg'
                ]
            ];
        }

        $imageTool = new ImageTool();
        $rawImages = [['name_img' => $row['name_img'], 'alt_img' => $altText, 'title_img' => $titleText]];
        $idProd = (int)$row['id_product'];
        $baseDir = "{$siteUrl}/upload/product/{$idProd}/";

        $processed = $imageTool->setModuleImages('catalog', 'product', $rawImages, $idProd, $baseDir);
        $imgData = $processed[0]['img'] ?? [];

        $imgData['alt'] = $altText;
        $imgData['title'] = $titleText;

        if (isset($imgData['original']) && !isset($imgData['default'])) {
            $imgData['default'] = $imgData['original'];
        }

        return $imgData;
    }

    /**
     * 🟢 3. Ajout de $companyInfo dans la signature ici aussi
     */
    /**
     * Génère le script JSON-LD Produit
     */
    private static function generateJsonLd(array $data, array $imgData, string $siteUrl, array $companyInfo = []): string
    {
        $schema = [
            '@context'    => 'https://schema.org/',
            '@type'       => 'Product',
            'name'        => $data['name'],
            'image'       => $imgData['default']['src'] ?? '',
            'description' => trim(strip_tags($data['resume'] ?: $data['content'])),
            'sku'         => $data['reference'] ?? '',
            'offers'      => [
                '@type'         => 'Offer',
                'url'           => $siteUrl . $data['url'],
                'priceCurrency' => 'EUR',
                'price'         => number_format((float)$data['price'], 2, '.', ''),
                'availability'  => 'https://schema.org/InStock'
            ]
        ];

        // 🟢 CORRECTION : Utilisation du type "Brand" au lieu de "Organization"
        if (!empty($companyInfo['name'])) {
            $schema['brand'] = [
                '@type' => 'Brand',
                'name'  => $companyInfo['name']
            ];
        }

        return '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';
    }
}