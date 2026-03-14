<?php

declare(strict_types=1);

namespace App\Frontend\Model;

use App\Component\File\ImageTool;
use App\Component\Routing\UrlTool;

class ProductPresenter
{
    /**
     * 🟢 NOUVELLE SIGNATURE : Ajout du paramètre $siteSettings
     */
    public static function format(array $row, array $langContext, string $siteUrl, array $companyInfo = [], string $skinFolder = 'default', array $siteSettings = []): ?array
    {
        $idParent = $row['default_id_cat'] ?? $row['id_cat'] ?? 0;
        $urlCat   = $row['default_url_cat'] ?? $row['url_cat'] ?? '';

        if (empty($idParent) || empty($urlCat)) {
            return null;
        }

        $iso = $langContext['iso_lang'] ?? 'fr';

        // 🟢 CALCUL DU PRIX ET DES PROMOTIONS (HT ou TTC)
        $rawPrice = (float)($row['price_p'] ?? 0);
        $rawPromo = (float)($row['price_promo_p'] ?? 0);

        $displayMode = $siteSettings['price_display']['value'] ?? 'texc'; // texc par défaut
        $vatRate = (float)($siteSettings['vat_rate']['value'] ?? 0);

        // Définition du multiplicateur de taxe
        $taxMultiplier = ($displayMode === 'tinc') ? (1 + ($vatRate / 100)) : 1;
        $priceSuffix = ($displayMode === 'tinc') ? 'TTC' : 'HT';

        // Est-ce qu'il y a une promotion valide ? (Promo > 0 et inférieure au prix de base)
        $hasPromo = ($rawPromo > 0 && $rawPromo < $rawPrice);

        // Calcul du prix de base (avec ou sans taxe)
        $originalPriceFinal = $rawPrice * $taxMultiplier;

        if ($hasPromo) {
            $effectivePriceFinal = $rawPromo * $taxMultiplier; // Le prix à payer
            $promoPercentage = (int)round((($rawPrice - $rawPromo) / $rawPrice) * 100);
        } else {
            $effectivePriceFinal = $originalPriceFinal; // Pas de promo
            $promoPercentage = 0;
        }

        $data = [
            'id'                       => $row['id_product'] ?? null,
            'name'                     => $row['name_p'] ?? '',
            'reference'                => $row['reference_p'] ?? '',
            'ean_p'                    => $row['ean_p'] ?? '', // 🟢 Ajout de l'EAN
            'availability_p'           => $row['availability_p'] ?? 'InStock', // 🟢 Ajout du stock
            // --- Variables de prix ---
            'price_final'              => round($effectivePriceFinal, 2), // Le vrai prix final (promo ou non)
            'price_formatted'          => number_format($effectivePriceFinal, 2, ',', ' '),
            'has_promo'                => $hasPromo,
            'promo_percent'            => $promoPercentage,
            'price_original'           => round($originalPriceFinal, 2), // L'ancien prix
            'price_original_formatted' => number_format($originalPriceFinal, 2, ',', ' '),
            'price_suffix'             => $priceSuffix,
            // -------------------------
            'resume'                   => $row['resume_p'] ?? '',
            'content'                  => $row['content_p'] ?? '',
            'id_parent'                => $idParent,
            'cat_name'                 => $row['name_cat'] ?? '',
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

        $data['img'] = self::processImage($row, $siteUrl, $skinFolder);

        $seoTitle = $row['seo_title_p'] ?? '';
        $data['seo_title'] = !empty($seoTitle) ? $seoTitle : $data['name'] . ' - ' . $data['cat_name'];

        // On passe $data (qui contient price_final) au JSON-LD
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

    private static function processImage(array $row, string $siteUrl, string $skinFolder): array
    {
        // ... (votre code existant reste strictement identique) ...
        $altText = !empty($row['alt_img']) ? $row['alt_img'] : ($row['name_p'] ?? '');
        $titleText = !empty($row['title_img']) ? $row['title_img'] : ($row['name_p'] ?? '');

        if (empty($row['name_img'])) {
            static $fallbackData = [];
            if (!isset($fallbackData[$skinFolder])) {
                $holderFilename = 'product_medium.jpg';
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
                    'src' => $src, 'w' => $size ? $size[0] : 800, 'h' => $size ? $size[1] : 600, 'ext' => 'image/jpeg'
                ];
            }
            return ['alt' => $altText, 'title' => $titleText, 'default' => $fallbackData[$skinFolder]];
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
                // 🟢 On envoie à Google le prix FINAL (TTC ou HT selon le site)
                'price'         => number_format((float)$data['price_final'], 2, '.', ''),
                'availability'  => 'https://schema.org/InStock'
            ]
        ];

        if (!empty($companyInfo['name'])) {
            $schema['brand'] = ['@type' => 'Brand', 'name' => $companyInfo['name']];
        }

        return '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';
    }
}