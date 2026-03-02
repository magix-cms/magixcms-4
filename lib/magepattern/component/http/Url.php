<?php
/*
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Mage Pattern.
# The toolkit PHP for developer
# Copyright (C) 2012 - 2026 Gerits Aurelien contact[at]gerits-aurelien[dot]be
#
# OFFICIAL TEAM MAGE PATTERN:
#
#   * Gerits Aurelien (Author - Developer) contact[at]gerits-aurelien[dot]be
#
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.

# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
# Redistributions of source code must retain the above copyright notice,
# this list of conditions and the following disclaimer.
#
# Redistributions in binary form must reproduce the above copyright notice,
# this list of conditions and the following disclaimer in the documentation
# and/or other materials provided with the distribution.
#
# DISCLAIMER
*/

namespace Magepattern\Component\HTTP;

use Magepattern\Component\Tool\EscapeTool;
use Magepattern\Component\Tool\StringTool;
use Magepattern\Component\Debug\Logger;
use Transliterator;
use Throwable;

class Url
{
    /**
     * Map de fallback si l'extension Intl n'est pas disponible.
     */
    private const TRANSLIT_MAP = [
        "¥" => "Y", "µ" => "u", "À" => "A", "Á" => "A", "Â" => "A", "Ã" => "A", "Ä" => "A", "Å" => "A",
        "Æ" => "A", "Ç" => "C", "È" => "E", "É" => "E", "Ê" => "E", "Ë" => "E", "Ì" => "I", "Í" => "I",
        "Î" => "I", "Ï" => "I", "Ð" => "D", "Ñ" => "N", "Ò" => "O", "Ó" => "O", "Ô" => "O", "Õ" => "O",
        "Ö" => "O", "Ø" => "O", "Ù" => "U", "Ú" => "U", "Û" => "U", "Ü" => "U", "Ý" => "Y", "ß" => "s",
        "à" => "a", "á" => "a", "â" => "a", "ã" => "a", "ä" => "a", "å" => "a", "æ" => "a", "ç" => "c",
        "è" => "e", "é" => "e", "ê" => "e", "ë" => "e", "ì" => "i", "í" => "i", "î" => "i", "ï" => "i",
        "ð" => "o", "ñ" => "n", "ò" => "o", "ó" => "o", "ô" => "o", "õ" => "o", "ö" => "o", "ø" => "o",
        "ù" => "u", "ú" => "u", "û" => "u", "ü" => "u", "ý" => "y", "ÿ" => "y"
    ];

    /**
     * Supprime le protocole et le domaine pour ne garder que le chemin.
     * Ex: https://site.com/blog/article -> /blog/article
     */
    public static function stripHostURL(string $url): string
    {
        $path = parse_url($url, PHP_URL_PATH);
        $query = parse_url($url, PHP_URL_QUERY);

        $result = $path ?? '';
        if ($query) {
            $result .= '?' . $query;
        }

        return $result ?: $url;
    }

    /**
     * Récupère l'URL complète actuelle.
     * Gère correctement le HTTPS et les proxies.
     */
    public static function current(bool $withQueryString = true): string
    {
        // Détection robuste du HTTPS (compatible Load Balancers/Proxies)
        $isHttps = (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off')
            || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
            || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443);

        $protocol = $isHttps ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

        $url = $protocol . '://' . $host;

        if ($withQueryString && isset($_SERVER['REQUEST_URI'])) {
            $url .= $_SERVER['REQUEST_URI'];
        } else {
            $url .= $_SERVER['SCRIPT_NAME'] ?? '';
        }

        return $url;
    }

    /**
     * Alias compatible avec votre ancien code
     * @deprecated Utiliser Url::current(true)
     */
    public static function getUrl(bool $requestUri = false): string
    {
        return self::current($requestUri);
    }

    /**
     * Récupère uniquement le nom du fichier exécuté (ex: index.php).
     */
    public static function getFilename(): string
    {
        return basename($_SERVER["SCRIPT_NAME"] ?? '');
    }

    /**
     * Génère un "Slug" propre pour les URLs.
     * Nettoie les accents, les caractères spéciaux et les espaces.
     * * @param string $str La chaîne à nettoyer
     * @param array $option Options: ['dot' => 'none', 'ampersand' => 'none', 'cspec' => [], 'rspec' => []]
     */
    public static function clean(string $str, array $option = []): string
    {
        $config = array_merge([
            'dot'       => 'none',
            'ampersand' => 'none',
            'cspec'     => [],
            'rspec'     => []
        ], $option);

        // 1. Transliteraion (Accents -> ASCII)
        $str = self::transliterate($str);

        // 2. Nettoyage initial technique
        $str = EscapeTool::cleanQuote($str);

        // 3. Définition des Regex de nettoyage (On nettoie AVANT de transformer l'ampersand)
        $patterns = ['@["’|,+\'\\/[:blank:]\s]+@i', '@[?#!:()\\[\\]{}\@$%]+@i'];
        $replacements = ['-', ''];

        if (!empty($config['cspec'])) {
            $patterns = array_merge($patterns, $config['cspec']);
        }
        if (!empty($config['rspec'])) {
            $replacements = array_merge($replacements, $config['rspec']);
        }

        try {
            $str = preg_replace($patterns, $replacements, $str);
        } catch (Throwable $e) {
            Logger::getInstance()->log($e, 'php', 'error');
            return '';
        }

        // 4. Gestion des points (.)
        if ($config['dot'] === 'none') {
            $str = str_replace('.', '', $str);
        }

        // 5. Gestion des esperluettes (&) -> APRES le nettoyage regex
        $str = match ($config['ampersand']) {
            'strict' => str_replace('&', '&amp;', $str),
            'none'   => str_replace('&', '-', $str),
            default  => str_replace('&', (string)$config['ampersand'], $str)
        };

        // 6. Nettoyage final des tirets
        $str = preg_replace('/-{2,}/', '-', $str);
        $str = trim($str, '-');

        // 7. Sortie
        $str = EscapeTool::decode_utf8($str);
        return StringTool::strtolower($str);
    }

    /**
     * Méthode interne pour gérer la translitération proprement
     */
    private static function transliterate(string $str): string
    {
        // Essai 1 : Extension PHP Intl (Le plus robuste)
        if (class_exists('Transliterator')) {
            try {
                $trans = Transliterator::createFromRules(
                    ':: Any-Latin; :: Latin-ASCII; :: NFD; :: [:Nonspacing Mark:] Remove; :: NFC;',
                    Transliterator::FORWARD
                );
                if ($trans) {
                    return $trans->transliterate($str);
                }
            } catch (Throwable $e) {
                Logger::getInstance()->log($e, 'php', 'warning');
            }
        }

        // Essai 2 : Fallback tableau statique (Manuel)
        return strtr($str, self::TRANSLIT_MAP);
    }

    /**
     * Version légère de clean pour des tags simples.
     */
    public static function shortClean(string $str): string
    {
        $str = self::transliterate($str);
        $str = trim($str);
        $str = EscapeTool::cleanQuote($str);

        // Regex simplifiée : remplace les caractères spéciaux par des espaces, puis trim
        $str = preg_replace("/['?#@,!:()]/", ' ', $str);
        $str = preg_replace('/\s+/', ' ', $str); // Réduit les espaces multiples

        $str = EscapeTool::decode_utf8($str);
        return StringTool::strtolower(trim($str));
    }

    /**
     * Résout une URI relative par rapport à l'URL de base actuelle.
     * Remplace votre ancienne méthode getUri() qui contenait du code mort.
     * * @param string|null $path Chemin relatif (ex: 'css/style.css' ou '/contact')
     * @return string URL absolue
     */
    public static function resolve(?string $path = null): string
    {
        $base = self::current(false); // Récupère http://domain.com/script.php

        // Si aucun path, on renvoie l'URL courante complète
        if ($path === null) {
            return self::current(true);
        }

        // Si c'est déjà une URL absolue, on la retourne
        if (str_starts_with($path, 'http')) {
            return $path;
        }

        // Gestion de la racine du domaine
        $scheme = parse_url($base, PHP_URL_SCHEME) . '://';
        $host = parse_url($base, PHP_URL_HOST);

        // Si le path commence par /, c'est relatif à la racine du domaine
        if (str_starts_with($path, '/')) {
            return $scheme . $host . $path;
        }

        // Sinon, c'est relatif au dossier courant
        // On enlève le nom du script (index.php) pour avoir le dossier
        $folder = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');

        return $scheme . $host . $folder . '/' . $path;
    }
}