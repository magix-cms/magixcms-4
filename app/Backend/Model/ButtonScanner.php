<?php

declare(strict_types=1);

namespace App\Backend\Model;

use App\Backend\Db\ThemeDb;
use Magepattern\Component\Debug\Logger;

/**
 * Scanner CSS des boutons du thème actif pour TinyMCE 7
 * (C) 2008 - 2026 Gerits Aurelien (Magix CMS) - Licence GPLv3
 */
class ButtonScanner
{
    /**
     * Classes Bootstrap commençant par .btn- qui ne sont PAS des variantes visuelles
     */
    private const EXCLUDED_CLASSES = [
        'btn-group',
        'btn-group-vertical',
        'btn-group-sm',
        'btn-group-lg',
        'btn-toolbar',
        'btn-check',
        'btn-close',
        'btn-close-white',
        'btn-link',
        'btn-outline-dark'
    ];

    /**
     * Classes de tailles reconnues séparément
     */
    private const SIZE_CLASSES = [
        'btn-sm' => 'Petit (btn-sm)',
        'btn-lg' => 'Grand (btn-lg)'
    ];

    /**
     * Scanne le fichier button.css (ou buttons.css) du thème actif
     */
    public static function getAvailableButtons(): array
    {
        // 1. Récupération sécurisée du thème courant
        try {
            $themeDb = new ThemeDb();
            $rawTheme = (string)$themeDb->getCurrentTheme();
            $activeTheme = basename(preg_replace('/[^a-zA-Z0-9_-]/', '', $rawTheme)) ?: 'default';
        } catch (\Throwable $e) {
            $activeTheme = 'default';
        }

        // 2. Résolution du fichier CSS (thème actif -> fallback default)
        $candidates = [
            //['theme' => $activeTheme, 'file' => 'button.css'],
            ['theme' => $activeTheme, 'file' => 'buttons.css'],
            //['theme' => 'default',    'file' => 'button.css'],
            ['theme' => 'default',    'file' => 'buttons.css'],
        ];

        $cssPath = null;
        $cssUrl  = null;

        foreach ($candidates as $candidate) {
            $path = ROOT_DIR . 'skin' . DS . $candidate['theme'] . DS . 'css' . DS . $candidate['file'];
            if (file_exists($path)) {
                $cssPath = $path;
                $version = filemtime($path) ?: time();
                $cssUrl  = '/skin/' . $candidate['theme'] . '/css/' . $candidate['file'] . '?v=' . $version;
                break;
            }
        }

        $variants = [];
        // Tailles Bootstrap 5.3 toujours disponibles par défaut
        $sizes    = [
            ['class' => 'btn-sm', 'label' => 'Petit (btn-sm)'],
            ['class' => 'btn-lg', 'label' => 'Grand (btn-lg)']
        ];

        if ($cssPath === null) {
            Logger::getInstance()->log(
                "ButtonScanner: Aucun fichier button.css ou buttons.css trouvé pour le thème '{$activeTheme}'.",
                "warning",
                "tinymce",
                Logger::LOG_YEAR,
                Logger::LOG_LEVEL_ERROR
            );

            return [
                'theme'    => $activeTheme,
                'cssUrl'   => '',
                'variants' => [],
                'sizes'    => []
            ];
        }

        // 3. Extraction des classes .btn-* (supporte les commentaires optionnels /* @label: Mon Bouton */)
        $content = (string)file_get_contents($cssPath);

        // Récupération des labels personnalisés éventuels : /* @label: Mon titre */ .btn-custom
        $customLabels = [];
        if (preg_match_all('/\/\*\s*@label:\s*([^*]+?)\s*\*\/\s*\.(btn-[\w\-]+)/i', $content, $labelMatches, PREG_SET_ORDER)) {
            foreach ($labelMatches as $match) {
                $customLabels[$match[2]] = trim($match[1]);
            }
        }

        // Extraction de toutes les classes .btn-* suivies d'un délimiteur CSS valide
        if (preg_match_all('/\.(btn-[a-zA-Z0-9_-]+)(?=[\s,{:.>~+\[])/', $content, $matches)) {
            $rawClasses = array_unique($matches[1]);

            foreach ($rawClasses as $className) {
                if (in_array($className, self::EXCLUDED_CLASSES, true)) {
                    continue;
                }

                // Déjà présents par défaut dans $sizes : on les ignore pour éviter tout doublon
                if (isset(self::SIZE_CLASSES[$className])) {
                    continue;
                }

                $variants[] = [
                    'class' => $className,
                    'label' => $customLabels[$className] ?? self::formatLabel($className)
                ];
            }
        }

        return [
            'theme'    => $activeTheme,
            'cssUrl'   => $cssUrl,
            'variants' => $variants,
            'sizes'    => $sizes
        ];
    }

    /**
     * Convertit "btn-outline-primary" en "Outline Primary"
     */
    private static function formatLabel(string $className): string
    {
        $clean = preg_replace('/^btn-/', '', $className);
        $clean = str_replace(['-', '_'], ' ', (string)$clean);

        return ucwords(trim($clean));
    }
}