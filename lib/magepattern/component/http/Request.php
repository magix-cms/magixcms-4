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

/**
 * use Magepattern\Component\HTTP\Request;
 *
 * if (Request::isMethod('POST')) {
 *
 * if (Request::isPost('token_csrf')) {
 * // Traitement...
 * }
 *
 * // Si c'est de l'Ajax, on répond en JSON
 * if (Request::isAjax()) {
 * header('Content-Type: application/json');
 * echo json_encode(['status' => 'success']);
 * exit;
 * }
 * }
 */
class Request
{
    /**
     * Vérifie la méthode HTTP utilisée (GET, POST, PUT, DELETE...).
     * Insensible à la casse.
     */
    public static function isMethod(string $method): bool
    {
        $currentMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        return strtoupper($currentMethod) === strtoupper($method);
    }

    /**
     * Vérifie si la requête est sécurisée (HTTPS).
     */
    public static function isSecure(): bool
    {
        // Vérification standard HTTPS + Compatibilité Load Balancer (X-Forwarded-Proto)
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || ($_SERVER['SERVER_PORT'] ?? 0) === 443
            || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
    }

    /**
     * Vérifie si la requête est une requête AJAX (XMLHttpRequest).
     * Note: De plus en plus remplacé par Fetch API, mais standard pour jQuery/Axios.
     */
    public static function isAjax(): bool
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Cœur de la vérification.
     * Utilise array_key_exists pour être sûr que la clé existe même si la valeur est NULL.
     */
    private static function hasKey(string $key, string $source): bool
    {
        // On utilise match pour sélectionner le bon tableau
        // Note: $_REQUEST contient GET, POST et COOKIE par défaut
        $array = match ($source) {
            'get'     => $_GET,
            'post'    => $_POST,
            'request' => $_REQUEST,
            'server'  => $_SERVER,
            'cookie'  => $_COOKIE,
            'session' => $_SESSION ?? [], // Session peut ne pas être démarrée
            'files'   => $_FILES,
            default   => []
        };

        return array_key_exists($key, $array);
    }

    /**
     * Vérifie l'existence d'une clé dans $_GET
     */
    public static function isGet(string $key): bool
    {
        return self::hasKey($key, 'get');
    }

    /**
     * Vérifie l'existence d'une clé dans $_POST
     */
    public static function isPost(string $key): bool
    {
        return self::hasKey($key, 'post');
    }

    /**
     * Vérifie l'existence d'une clé dans $_REQUEST
     */
    public static function isRequest(string $key): bool
    {
        return self::hasKey($key, 'request');
    }

    /**
     * Vérifie l'existence d'une clé dans $_SESSION
     */
    public static function isSession(string $key): bool
    {
        // Sécurité : on vérifie si la session est active avant de lire
        if (session_status() === PHP_SESSION_NONE) {
            return false;
        }
        return self::hasKey($key, 'session');
    }

    /**
     * Vérifie l'existence d'une clé dans $_SERVER
     */
    public static function isServer(string $key): bool
    {
        return self::hasKey($key, 'server');
    }

    /**
     * Vérifie l'existence d'une clé dans $_COOKIE
     */
    public static function isCookie(string $key): bool
    {
        return self::hasKey($key, 'cookie');
    }

    /**
     * Vérifie l'existence d'un fichier uploadé
     */
    public static function isFile(string $key): bool
    {
        return self::hasKey($key, 'files');
    }
}