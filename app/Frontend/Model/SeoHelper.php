<?php
declare(strict_types=1);

namespace App\Frontend\Model;

class SeoHelper
{
    /**
     * Génère le JSON-LD global de la page d'accueil (WebSite + Organization via @graph)
     */
    public static function generateHomeGraphJsonLd(string $siteName, string $siteUrl, string $isoLang, string $seoDesc, array $companyInfo): string
    {
        $baseUrl = rtrim($siteUrl, '/') . '/';

        // 1. Définition de l'organisation
        $organization = [
            '@type' => 'Organization',
            '@id'   => $baseUrl . '#organization',
            'name'  => $companyInfo['company_name'] ?? $siteName,
            'url'   => $baseUrl,
            'logo'  => [
                '@type' => 'ImageObject',
                'url'   => $baseUrl . 'img/logo/l_logo-1.png'
            ]
        ];

        // Ajout de l'adresse si disponible
        if (!empty($companyInfo['address']) || !empty($companyInfo['city'])) {
            $organization['address'] = [
                '@type'           => 'PostalAddress',
                'streetAddress'   => $companyInfo['street'] ?? '',
                'postalCode'      => $companyInfo['postcode'] ?? '',
                'addressLocality' => $companyInfo['city'] ?? '',
                'addressCountry'  => $companyInfo['country'] ?? 'BE'
            ];
        }

        // Ajout du téléphone de contact si disponible
        if (!empty($companyInfo['phone'])) {
            $organization['contactPoint'] = [
                [
                    '@type'       => 'ContactPoint',
                    'telephone'   => $companyInfo['phone'],
                    'contactType' => 'customer service'
                ]
            ];
        }

        // Ajout des réseaux sociaux (adaptez les clés selon vos colonnes en DB)
        $sameAs = [];
        if (!empty($companyInfo['facebook'])) $sameAs[] = $companyInfo['facebook'];
        if (!empty($companyInfo['twitter'])) $sameAs[] = $companyInfo['twitter'];
        if (!empty($companyInfo['linkedin'])) $sameAs[] = $companyInfo['linkedin'];

        // Fallback temporaire si vos champs sont vides mais que vous avez l'URL
        if (empty($sameAs)) {
            $sameAs[] = 'https://www.facebook.com/Aurelien.Stireg';
        }

        if (!empty($sameAs)) {
            $organization['sameAs'] = $sameAs;
        }

        // 2. Assemblage avec WebSite via @graph
        $schema = [
            '@context' => 'https://schema.org',
            '@graph'   => [
                [
                    '@type'       => 'WebSite',
                    '@id'         => $baseUrl . '#website',
                    'url'         => $baseUrl,
                    'name'        => $siteName,
                    'description' => $seoDesc,
                    'inLanguage'  => $isoLang
                ],
                $organization
            ]
        ];

        return '<script type="application/ld+json">' . "\n" . json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n" . '</script>';
    }

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
        $baseUrl = rtrim($siteUrl, '/') . '/';

        $schema = [
            '@context' => 'https://schema.org',
            '@type'    => 'WebSite',
            'name'     => $siteName,
            'url'      => $baseUrl
        ];

        return '<script type="application/ld+json">' . "\n" . json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n" . '</script>';
    }

    /**
     * Génère le JSON-LD FAQPage de manière universelle
     */
    public static function generateFaqJsonLd(array $data): string
    {
        if (empty($data)) {
            return '';
        }

        $mainEntity = [];

        foreach ($data as $item) {
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