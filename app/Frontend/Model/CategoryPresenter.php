<?php

declare(strict_types=1);

namespace App\Frontend\Model;

use App\Component\File\ImageTool;
use App\Component\Routing\UrlTool;

class CategoryPresenter
{
    /**
     * Formate une ligne de résultat Catégorie pour le frontend
     */
    public static function format(array $row, array $langContext, string $siteUrl): array
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

        // 🟢 Utilisation de l'UrlTool pour le lien de la catégorie
        $urlTool = new UrlTool();
        $data['url'] = $urlTool->buildUrl([
            'type' => 'category',
            'id'   => $idCat,
            'url'  => $row['url_cat'] ?? '',
            'iso'  => $iso,
            'date' => $data['date']
        ]);

        // --- GESTION DES IMAGES ---
        $data['img'] = self::processImages($row, $idCat, $siteUrl);

        // --- SEO ---
        $data['seo'] = [
            'title'       => !empty($row['seo_title_cat']) ? $row['seo_title_cat'] : $data['name'],
            'description' => !empty($row['seo_desc_cat']) ? $row['seo_desc_cat'] : strip_tags($data['resume'])
        ];


        // =====================================================================
        // 🟢 NOUVEAU : LA MAGIE DE L'OVERRIDE (Capture des champs des plugins)
        // =====================================================================
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
        // =====================================================================


        return $data;
    }

    private static function processImages(array $row, int $idCat, string $siteUrl): array
    {
        $imageTool = new ImageTool();
        $altText   = !empty($row['alt_img']) ? $row['alt_img'] : ($row['name_cat'] ?? '');
        $titleText = !empty($row['title_img']) ? $row['title_img'] : ($row['name_cat'] ?? '');

        if (empty($row['name_img'])) {
            return [
                'alt' => $altText, 'title' => $titleText,
                'default' => ['src' => "{$siteUrl}/skin/default/images/no-image.jpg", 'w' => 800, 'h' => 800]
            ];
        }

        $rawImages = [['name_img' => $row['name_img'], 'alt_img' => $altText, 'title_img' => $titleText]];

        // On définit le dossier d'upload pour les catégories
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
}