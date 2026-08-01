<?php
declare(strict_types=1);

namespace App\Frontend\Model;

class SeoHelper
{
    /**
     * Génère le JSON-LD global de la page d'accueil (WebSite + Organization via @graph)
     */
    /**
     * Génère le JSON-LD global de la page d'accueil (WebSite + Entité dynamique via @graph)
     */
    public static function generateHomeGraphJsonLd(string $siteName, string $siteUrl, string $isoLang, string $seoDesc, array $companyInfo): string
    {
        $baseUrl = rtrim($siteUrl, '/') . '/';

        // 1. Le mapping exact de votre administration
        $typesMap = [
            'org'    => 'Organization',
            'locb'   => 'LocalBusiness',
            'corp'   => 'Corporation',
            'store'  => 'Store',
            'food'   => 'FoodEstablishment',
            'place'  => 'Place',
            'person' => 'Person'
        ];

        // Détection du type (LocalBusiness par exemple), sinon Organization par défaut
        $companyType = $typesMap[$companyInfo['type'] ?? 'org'] ?? 'Organization';

        // 2. Construction de l'entité (Entreprise locale, Personne, etc.)
        $organization = [
            '@type' => $companyType,
            '@id'   => $baseUrl . '#' . strtolower($companyType),
            'name'  => !empty($companyInfo['name']) ? $companyInfo['name'] : $siteName,
            'url'   => $baseUrl,
            'logo'  => [
                '@type' => 'ImageObject',
                'url'   => $baseUrl . 'img/logo/l_logo-1.png'
            ]
        ];

        // 3. Traitement de l'adresse (Basé sur vos clés DB: street, postcode, city)
        if (!empty($companyInfo['street']) || !empty($companyInfo['city'])) {
            $organization['address'] = [
                '@type'           => 'PostalAddress',
                'streetAddress'   => $companyInfo['street'] ?? '',
                'postalCode'      => $companyInfo['postcode'] ?? '',
                'addressLocality' => $companyInfo['city'] ?? '',
                'addressCountry'  => 'BE' // Belgique par défaut (à adapter si le pays est en DB un jour)
            ];
        }

        // 4. Traitement des Contacts directs (Fortement recommandé pour le SEO Local)
        if (!empty($companyInfo['phone'])) {
            $organization['telephone'] = $companyInfo['phone'];
        } elseif (!empty($companyInfo['mobile'])) {
            $organization['telephone'] = $companyInfo['mobile']; // Fallback sur le mobile
        }

        if (!empty($companyInfo['mail'])) {
            $organization['email'] = $companyInfo['mail'];
        }

        // 5. Point de contact formel (Service client)
        if (!empty($organization['telephone'])) {
            $organization['contactPoint'] = [
                [
                    '@type'       => 'ContactPoint',
                    'telephone'   => $organization['telephone'],
                    'contactType' => 'customer service',
                    'email'       => $organization['email'] ?? ''
                ]
            ];
        }

        // 6. Informations Légales (TVA)
        if (!empty($companyInfo['tva'])) {
            $organization['vatID'] = $companyInfo['tva'];
        }

        // 7. Réseaux Sociaux (Boucle dynamique sur les clés de votre DB)
        $sameAs = [];
        $socialKeys = ['facebook', 'twitter', 'instagram', 'linkedin', 'youtube', 'github'];

        foreach ($socialKeys as $key) {
            if (!empty($companyInfo[$key])) {
                $sameAs[] = $companyInfo[$key];
            }
        }

        if (!empty($sameAs)) {
            $organization['sameAs'] = $sameAs;
        }

        // 8. Assemblage final (WebSite + Entité dynamique)
        $schema = [
            '@context' => 'https://schema.org',
            '@graph'   => [
                [
                    '@type'       => 'WebSite',
                    '@id'         => $baseUrl . '#website',
                    'url'         => $baseUrl,
                    'name'        => !empty($companyInfo['name']) ? $companyInfo['name'] : $siteName,
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
                    'name'     => trim(html_entity_decode(stripslashes((string)$item['name']), ENT_QUOTES, 'UTF-8'))
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
                // Nettoyage de la question (texte brut uniquement)
                $cleanQuestion = trim(preg_replace('/\s+/', ' ', html_entity_decode(stripslashes(strip_tags((string)$question)), ENT_QUOTES, 'UTF-8')));

                $cleanAnswer = trim(preg_replace('/\s+/', ' ', html_entity_decode(stripslashes(strip_tags((string)$answer)), ENT_QUOTES, 'UTF-8')));

                $mainEntity[] = [
                    '@type'          => 'Question',
                    'name'           => $cleanQuestion,
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text'  => $cleanAnswer
                    ]
                ];
            }
        }

        $schema = [
            '@context'   => 'https://schema.org',
            '@type'      => 'FAQPage',
            'mainEntity' => $mainEntity
        ];

        $jsonFlags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;

        return '<script type="application/ld+json">' . "\n" .
            json_encode($schema, $jsonFlags) .
            "\n" . '</script>';
    }
}