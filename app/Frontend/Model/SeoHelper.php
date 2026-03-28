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
    /**
     * Génère le JSON-LD global du site web (à placer dans le head de toutes les pages)
     */
    public static function generateWebSiteJsonLd(string $siteName, string $siteUrl): string
    {
        // On s'assure que l'URL de base se termine par un slash
        $baseUrl = rtrim($siteUrl, '/') . '/';

        $schema = [
            '@context' => 'https://schema.org',
            '@type'    => 'WebSite',
            'name'     => $siteName,
            'url'      => $baseUrl/*,
            'potentialAction' => [
                '@type'       => 'SearchAction',
                // Adaptez l'URL '/search?q=' selon la vraie route de recherche de votre CMS
                'target'      => $baseUrl . 'search?q={search_term_string}',
                'query-input' => 'required name=search_term_string'
            ]*/
        ];

        return '<script type="application/ld+json">' . "\n" . json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n" . '</script>';
    }
    /**
     * Génère le JSON-LD FAQPage de manière universelle
     * @param array $data Tableau attendu : [['question' => '...', 'answer' => '...'], ...]
     */
    public static function generateFaqJsonLd(array $data): string
    {
        if (empty($data)) {
            return '';
        }

        $mainEntity = [];

        foreach ($data as $item) {
            // Le Helper ne connaît que "question" et "answer"
            $question = $item['question'] ?? '';
            $answer = $item['answer'] ?? '';

            if (!empty($question) && !empty($answer)) {
                $mainEntity[] = [
                    '@type'          => 'Question',
                    'name'           => strip_tags((string)$question),
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text'  => strip_tags((string)$answer)
                    ]
                ];
            }
        }

        $schema = [
            '@context'   => 'https://schema.org',
            '@type'      => 'FAQPage',
            'mainEntity' => $mainEntity
        ];

        return '<script type="application/ld+json">' . "\n" .
            json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) .
            "\n" . '</script>';
    }
}