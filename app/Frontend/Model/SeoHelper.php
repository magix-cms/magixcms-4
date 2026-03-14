<?php
declare(strict_types=1);

namespace App\Frontend\Model; // Ou le namespace de vos helpers

class SeoHelper
{
    /**
     * Génère un JSON-LD de type ItemList pour n'importe quel tableau d'items formatés.
     */
    public static function generateItemListJsonLd(array $formattedItems): string
    {
        if (empty($formattedItems)) {
            return '';
        }

        $elements = [];
        $position = 1;

        foreach ($formattedItems as $item) {
            // On s'assure que l'item a au moins une URL et un nom
            if (!empty($item['url']) && !empty($item['name'])) {
                $elements[] = [
                    '@type'    => 'ListItem',
                    'position' => $position,
                    'url'      => $item['url'],
                    'name'     => $item['name']
                ];
                $position++;
            }
        }

        $schema = [
            '@context'        => 'https://schema.org',
            '@type'           => 'ItemList',
            'itemListElement' => $elements
        ];

        return '<script type="application/ld+json">' . "\n" . json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n" . '</script>';
    }
}