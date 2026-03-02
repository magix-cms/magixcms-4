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

class Header
{
    /**
     * Liste des codes HTTP standards (RFC).
     * Enrichie avec les codes modernes (422, 429, etc.).
     */
    protected static array $statusTexts = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',            // RFC 2518
        103 => 'Early Hints',           // RFC 8297
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',          // RFC 4918
        208 => 'Already Reported',      // RFC 5842
        226 => 'IM Used',               // RFC 3229
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',    // RFC 7538
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Content Too Large',     // Renommé par RFC 7231
        414 => 'URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',         // RFC 2324 (Legacy/Fun)
        421 => 'Misdirected Request',   // RFC 7540
        422 => 'Unprocessable Entity',  // WebDAV (Très utilisé en API JSON)
        423 => 'Locked',                // WebDAV
        424 => 'Failed Dependency',     // WebDAV
        425 => 'Too Early',             // RFC 8470
        426 => 'Upgrade Required',
        428 => 'Precondition Required', // RFC 6585
        429 => 'Too Many Requests',     // RFC 6585 (Rate Limiting)
        431 => 'Request Header Fields Too Large', // RFC 6585
        451 => 'Unavailable For Legal Reasons',   // RFC 7725
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates', // RFC 2295
        507 => 'Insufficient Storage',    // WebDAV
        508 => 'Loop Detected',           // WebDAV
        510 => 'Not Extended',            // RFC 2774
        511 => 'Network Authentication Required', // RFC 6585
    ];

    /**
     * Récupère le message associé au code.
     */
    public static function getStatusText(int $code): string
    {
        return self::$statusTexts[$code] ?? 'Unknown Status';
    }

    /**
     * Définit le code de réponse HTTP.
     * Utilise la fonction native PHP pour la compatibilité (FastCGI/Apache/Nginx).
     */
    public static function setStatus(int $code): void
    {
        if (isset(self::$statusTexts[$code])) {
            http_response_code($code);
            // On peut forcer le texte si nécessaire, mais http_response_code le fait bien.
            // header('Status: ' . $code . ' ' . self::$statusTexts[$code]);
        }
    }

    /**
     * Définit les headers de cache.
     */
    public static function cache_control(string $cache): void
    {
        $control = match($cache) {
            'nocache' => ['no-store', 'no-cache', 'must-revalidate', 'max-age=0'],
            'public'  => ['public', 'max-age=3600'], // Ajout d'une durée par défaut pour public
            'private' => ['private', 'max-age=3600'],
            default   => ['no-cache'] // Sécurité par défaut
        };

        header('Cache-Control: ' . implode(', ', $control));
    }

    /**
     * Définit le Content-Type.
     */
    public static function content_type(string $type, string $charset = 'UTF-8'): void
    {
        $mime = match($type) {
            'text'      => 'text/plain',
            'html'      => 'text/html',
            'json'      => 'application/json',
            'xml'       => 'application/xml',
            'js'        => 'application/javascript',
            'css'       => 'text/css',
            'pdf'       => 'application/pdf',
            'excel'     => 'application/vnd.ms-excel',
            'excel2007' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'zip'       => 'application/zip',
            default     => 'application/octet-stream' // Binaire par défaut
        };

        header("Content-Type: $mime; charset=$charset");
    }

    /**
     * Prépare les headers pour une réponse JSON (API).
     */
    public static function set_json_headers(string $charset = 'UTF-8'): void
    {
        // Headers anti-cache agressifs
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate("D, d M Y H:i:s") . ' GMT');
        header('Pragma: no-cache');

        self::cache_control("nocache");
        self::setStatus(200);
        self::content_type('json', $charset);
    }

    /**
     * Headers spécifiques pour Google AMP (Accelerated Mobile Pages).
     *
     * @param string $origin L'origine de la requête (ex: https://google.com)
     * @param array $validOrigins Liste des domaines autorisés
     * @param bool|string $redirect URL de redirection ou true pour utiliser le Referer
     */
    public static function amp_headers(string $origin, array $validOrigins, bool|string $redirect = false): void
    {
        header('AMP-Same-Origin: true');

        // Sécurité CORS : On ne renvoie l'origine que si elle est dans la liste blanche.
        // On n'envoie JAMAIS une liste séparée par des espaces (invalide).
        if (in_array($origin, $validOrigins, true)) {
            header("Access-Control-Allow-Origin: $origin");
            header("AMP-Access-Control-Allow-Source-Origin: $origin");
        } else {
            // Si l'origine n'est pas valide, on ne met pas le header ou on met null,
            // ce qui bloquera la requête côté navigateur.
            // Optionnel : header("Access-Control-Allow-Origin: null");
        }

        $exposeHeaders = ['AMP-Access-Control-Allow-Source-Origin'];
        if ($redirect) {
            $exposeHeaders[] = 'AMP-Redirect-To';
        }
        header('Access-Control-Expose-Headers: ' . implode(', ', $exposeHeaders));

        if ($redirect) {
            $targetUrl = ($redirect === true) ? (self::getReferer() ?? '/') : $redirect;
            header("AMP-Redirect-To: $targetUrl");
        }
    }

    /**
     * Helper sécurisé pour récupérer le Referer (compatible Nginx/Apache).
     */
    private static function getReferer(): ?string
    {
        return $_SERVER['HTTP_REFERER'] ?? null;
    }
}