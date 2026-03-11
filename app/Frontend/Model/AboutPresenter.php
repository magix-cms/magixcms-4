<?php

declare(strict_types=1);

namespace App\Frontend\Model;

use App\Component\File\ImageTool;
use App\Component\Routing\UrlTool; // 🟢 Import de l'UrlTool

class AboutPresenter
{
    public static function format(array $row, array $langContext, string $siteUrl): array
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

        // 🟢 REMPLACEMENT : Utilisation de l'UrlTool au lieu du formatage manuel
        $urlTool = new UrlTool();
        $data['url'] = $urlTool->buildUrl([
            'type' => 'about',
            'id'   => $idAbout,
            'url'  => $row['url_about'] ?? '',
            'iso'  => $iso,
            'date' => $data['date'] // Utile si UrlTool en a besoin
        ]);

        $data['img'] = self::processImages($row, $idAbout, $siteUrl);

        $data['seo'] = [
            'title'       => !empty($row['seo_title_about']) ? $row['seo_title_about'] : $data['name'],
            'description' => !empty($row['seo_desc_about']) ? $row['seo_desc_about'] : strip_tags($data['resume'])
        ];

        return $data;
    }

    private static function processImages(array $row, int $idAbout, string $siteUrl): array
    {
        $imageTool = new ImageTool();
        $altText   = !empty($row['alt_img']) ? $row['alt_img'] : ($row['name_about'] ?? '');
        $titleText = !empty($row['title_img']) ? $row['title_img'] : ($row['name_about'] ?? '');

        if (empty($row['name_img'])) {
            return [
                'alt' => $altText, 'title' => $titleText,
                'default' => ['src' => "{$siteUrl}/skin/default/images/no-image.jpg", 'w' => 800, 'h' => 800]
            ];
        }

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
}