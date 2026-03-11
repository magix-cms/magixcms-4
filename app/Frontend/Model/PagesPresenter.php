<?php

declare(strict_types=1);

namespace App\Frontend\Model;

use App\Component\File\ImageTool;
use App\Component\Routing\UrlTool; // 🟢 On importe l'outil de routage

class PagesPresenter
{
    /**
     * Formate une ligne de résultat Pages pour le frontend
     */
    public static function format(array $row, array $langContext, string $siteUrl): array
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

        // 🟢 Utilisation de l'UrlTool pour une cohérence totale
        $urlTool = new UrlTool();
        $data['url'] = $urlTool->buildUrl([
            'type' => 'pages', // Match avec la règle dans UrlTool
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

        return $data;
    }

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
}