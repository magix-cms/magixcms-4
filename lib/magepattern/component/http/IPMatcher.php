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

use Magepattern\Component\Debug\Logger;
use Throwable;

/**
 * return [
 * '45.12.34.56',       // Le spammeur du lundi
 * '192.168.0.50',      // Un stagiaire qui fait des bêtises
 * '2001:db8::/32',     // Tout un réseau IPv6 suspect
 * '10.0.0.0/8'         // Bloquer tout un réseau local (CIDR)
 * ];
 *
 * $blacklist = require __DIR__ . '/config/blacklist.php';
 *
 * // 2. Vérification immédiate
 * // Notez qu'on ne passe pas l'IP en 2ème paramètre, la classe la détecte toute seule !
 * if (IPMatcher::isListed($blacklist)) {
 *
 * // A. On logue la tentative pour vos stats
 * $detectedIp = IPMatcher::getVisitorIp();
 * Logger::getInstance()->log("Tentative d'accès depuis IP bannie : $detectedIp", "security", "warning");
 *
 * // B. On renvoie une 403 Forbidden propre
 * Header::setStatus(403);
 *
 * // C. On arrête le script pour économiser le serveur
 * die("<h1>403 Forbidden</h1><p>Votre IP ($detectedIp) n'est pas autorisée à accéder à ce serveur.</p>");
 * }
 */
class IPMatcher
{
    /**
     * Récupère l'IP réelle du visiteur.
     * Gère les proxies (Cloudflare, Varnish, Load Balancers).
     */
    public static function getVisitorIp(): string
    {
        // 1. Check Cloudflare / Proxy (Standard de facto)
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            // X-Forwarded-For peut contenir une liste : "client, proxy1, proxy2"
            // On prend toujours la première IP (celle du client)
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            return trim($ips[0]);
        }

        // 2. Check Client IP (Certains hébergeurs)
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        }

        // 3. Fallback standard
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    /**
     * Vérifie si l'IP actuelle est présente dans une liste (Blacklist ou Whitelist).
     *
     * @param array $ipList Liste des IPs ou CIDR (ex: ['1.2.3.4', '192.168.0.0/24'])
     * @param string|null $currentIp (Optionnel) IP à tester. Si null, détecte auto.
     * @return bool True si l'IP est trouvée dans la liste
     */
    public static function isListed(array $ipList, ?string $currentIp = null): bool
    {
        $ipToTest = $currentIp ?? self::getVisitorIp();

        foreach ($ipList as $entry) {
            // Optimisation 1 : Comparaison stricte (IP Fixe) -> Ultra rapide
            if ($ipToTest === $entry) {
                return true;
            }

            // Optimisation 2 : Masque CIDR (ex: /24) -> Calcul binaire
            if (str_contains($entry, '/') && self::checkIp($ipToTest, $entry)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Compare une IP avec un sous-réseau (Support IPv4 & IPv6).
     */
    public static function checkIp(string $requestIp, string $subnet): bool
    {
        try {
            $mask = null;
            if (str_contains($subnet, '/')) {
                [$subnet, $mask] = explode('/', $subnet, 2);
                $mask = (int)$mask;
            }

            $binRequest = @inet_pton($requestIp);
            $binSubnet  = @inet_pton($subnet);

            if ($binRequest === false || $binSubnet === false) {
                return false; // IP invalide
            }

            if (strlen($binRequest) !== strlen($binSubnet)) {
                return false; // IPv4 vs IPv6 incompatible
            }

            $maxBits = strlen($binSubnet) * 8;
            $mask = $mask ?? $maxBits;

            if ($mask < 0 || $mask > $maxBits) {
                return false;
            }

            return self::compareBinary($binRequest, $binSubnet, $mask);

        } catch (Throwable $e) {
            Logger::getInstance()->log($e, "security", "error");
            return false;
        }
    }

    /**
     * Comparaison binaire bas niveau.
     */
    private static function compareBinary(string $addr1, string $addr2, int $mask): bool
    {
        if ($mask === 0) return true;
        $bytes = $mask >> 3;
        $bits = $mask & 7;

        if ($bytes > 0) {
            if (substr($addr1, 0, $bytes) !== substr($addr2, 0, $bytes)) return false;
        }

        if ($bits > 0) {
            $maskByte = chr(0xFF << (8 - $bits));
            if (($addr1[$bytes] & $maskByte) !== ($addr2[$bytes] & $maskByte)) return false;
        }

        return true;
    }
}