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

namespace Magepattern\Component\Tool;

/**
 * Class ClientTool
 * Utilitaires pour l'identification technique du client (IP, Navigateur, OS).
 * Remplace l'ancienne classe Utils.
 */
class ClientTool
{
    /**
     * Récupère l'adresse IP du client (Support Proxy & Cloudflare).
     */
    public static function getIp(): string
    {
        // Ordre de priorité pour traverser les proxys
        $headers = [
            'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'
        ];

        foreach ($headers as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    // On valide que c'est une IP publique
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    /**
     * Détermine le nom du navigateur (Version optimisée sans browscap).
     */
    public static function getBrowser(?string $userAgent = null): string
    {
        $agent = $userAgent ?? $_SERVER['HTTP_USER_AGENT'] ?? '';

        if (str_contains($agent, 'OPR/') || str_contains($agent, 'Opera')) return 'Opera';
        if (str_contains($agent, 'Edg'))    return 'Edge';
        if (str_contains($agent, 'Chrome')) return 'Chrome';
        if (str_contains($agent, 'Safari')) return 'Safari';
        if (str_contains($agent, 'Firefox')) return 'Firefox';
        if (str_contains($agent, 'MSIE') || str_contains($agent, 'Trident/7')) return 'Internet Explorer';

        if (self::isBot($agent)) return 'Bot';

        return 'Other';
    }

    /**
     * Détermine le système d'exploitation.
     */
    public static function getOs(?string $userAgent = null): string
    {
        $agent = $userAgent ?? $_SERVER['HTTP_USER_AGENT'] ?? '';

        if (preg_match('/linux/i', $agent)) return 'Linux';
        if (preg_match('/macintosh|mac os x/i', $agent)) return 'Mac';
        if (preg_match('/windows|win32/i', $agent)) return 'Windows';
        if (preg_match('/android/i', $agent)) return 'Android';
        if (preg_match('/iphone|ipad|ipod/i', $agent)) return 'iOS';

        return 'Unknown';
    }

    /**
     * Vérifie si le client est un robot (Crawler/Spider).
     */
    public static function isBot(?string $userAgent = null): bool
    {
        $agent = strtolower($userAgent ?? $_SERVER['HTTP_USER_AGENT'] ?? '');
        $bots = ['bot', 'crawl', 'slurp', 'spider', 'mediapartners', 'facebook', 'whatsapp', 'telegram', 'google'];

        foreach ($bots as $bot) {
            if (str_contains($agent, $bot)) return true;
        }

        return false;
    }
}