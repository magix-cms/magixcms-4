<?php

declare(strict_types=1);

namespace App\Frontend\Model;

class CatalogHomePresenter
{
    public static function format(array $row): array
    {
        $title = $row['title_page'] ?? 'Catalogue';

        return [
            'title'   => $title,
            'content' => $row['content_page'] ?? '',
            'seo'     => [
                'title'       => !empty($row['seo_title_page']) ? $row['seo_title_page'] : $title,
                'description' => $row['seo_desc_page'] ?? ''
            ]
        ];
    }
}