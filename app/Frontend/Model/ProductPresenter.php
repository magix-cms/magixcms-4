<?php
declare(strict_types=1);

namespace App\Frontend\Model;

use App\Component\File\ImageTool;

class ProductPresenter
{
    public static function format(array $row, array $langContext, string $siteUrl): ?array
    {
        if (empty($row['id_cat']) || empty($row['url_cat'])) {
            return null;
        }

        $iso = $langContext['iso_lang'] ?? 'fr';

        $data = [
            'id'          => $row['id_product'] ?? null,
            'name'        => $row['name_p'] ?? '',
            'reference'   => $row['reference_p'] ?? '',
            'price'       => isset($row['price_p']) ? number_format((float)$row['price_p'], 2, ',', ' ') : '0,00',
            'resume'      => $row['resume_p'] ?? '',
            'content'     => $row['content_p'] ?? '',
            'id_parent'   => $row['id_cat'],
            'cat_name'    => $row['name_cat'] ?? '',
        ];

        // URLs SEO
        $idProd   = $row['id_product'];
        $urlProd  = $row['url_p'] ?? '';
        $idParent = $row['id_cat'];
        $urlCat   = $row['url_cat'];

        $data['url_cat'] = "{$siteUrl}/{$iso}/catalog/{$idParent}-{$urlCat}/";
        $data['url']     = "{$siteUrl}/{$iso}/catalog/{$idParent}-{$urlCat}/{$idProd}-{$urlProd}/";

        // --- NOUVEAU : UTILISATION DE VOTRE IMAGETOOL ---
        $data['img'] = self::processImage($row, $siteUrl);

        // Champs SEO
        $seoTitle = $row['seo_title_p'] ?? '';
        $data['seo_title'] = !empty($seoTitle) ? $seoTitle : $data['name'] . ' - ' . $data['cat_name'];

        return $data;
    }

    /**
     * Fait le pont avec App\Component\File\ImageTool
     */
    /**
     * Fait le pont avec App\Component\File\ImageTool
     */
    private static function processImage(array $row, string $siteUrl): array
    {
        $altText = !empty($row['alt_img']) ? $row['alt_img'] : ($row['name_p'] ?? '');
        $titleText = !empty($row['title_img']) ? $row['title_img'] : ($row['name_p'] ?? '');

        if (empty($row['name_img'])) {
            // Fallback parfait pour votre composant Smarty
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

        $rawImages = [
            [
                'name_img' => $row['name_img'],
                'alt_img'  => $altText,
                'title_img'=> $titleText
            ]
        ];

        // CORRECTION ICI : Ajout de l'ID du produit dans le chemin !
        $idProd = (int)$row['id_product'];
        $baseDir = "{$siteUrl}/upload/product/{$idProd}/";

        $processed = $imageTool->setModuleImages(
            'catalog',
            'product',
            $rawImages,
            $idProd,
            $baseDir
        );

        // On récupère le tableau des tailles généré par ImageTool
        $imgData = $processed[0]['img'] ?? [];

        // ON PRÉPARE LE TERRAIN POUR VOTRE SNIPPET SMARTY
        $imgData['alt'] = $altText;
        $imgData['title'] = $titleText;

        // Votre snippet cherche souvent $img.default s'il ne trouve pas de taille spécifique
        if (isset($imgData['original']) && !isset($imgData['default'])) {
            $imgData['default'] = $imgData['original'];
        }

        return $imgData;
    }
}