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



namespace Magepattern\Component\Security;

use Magepattern\Component\Debug\Logger;

class RSATool
{
    /**
     * @param string $data
     * @return string
     */
    private static function md5_base64(string $data): string
    {
        return preg_replace('/=+$/', '', base64_encode(pack('H*', md5($data))));
    }

    /**
     * @param string $data
     * @param string $type
     * @return string
     */
    public static function hashEncode(string $data, string $type = 'md5'): string
    {
        return match ($type) {
            'sha1'       => sha1($data),
            'md5_base64' => self::md5_base64($data),
            default      => md5($data)
        };
    }

    /**
     * Identifiant unique aléatoire (Hexadécimal)
     */
    /**
     * @param int $length
     * @return string
     */
    public static function uniqID(int $length = 16): string
    {
        try {
            // On génère suffisamment d'octets pour la longueur souhaitée
            return substr(bin2hex(random_bytes((int)ceil($length / 2))), 0, $length);
        } catch (\Throwable $e) {
            Logger::getInstance()->log($e, 'php', 'error');
            return '';
        }
    }

    /**
     * Token aléatoire base64
     */
    /**
     * @param int $length
     * @return string
     */
    public static function tokenID(int $length = 20): string
    {
        try {
            return base64_encode(random_bytes($length));
        } catch (\Throwable $e) {
            Logger::getInstance()->log($e, 'php', 'error');
            return '';
        }
    }

    /**
     * Identifiant de type UUID v4 (partiel ou complet)
     */
    /**
     * @param string $size
     * @return string
     * @throws \Random\RandomException
     */
    public static function randUI(string $size = 'Large'): string
    {
        return match($size) {
            'Tiny'  => sprintf('%04x', random_int(0, 0xffff)),
            'Mirco' => sprintf('%04x%04x', random_int(0, 0xffff), random_int(0, 0xffff)),
            'Small' => sprintf('%04x%04x%04x%04x',
                random_int(0, 0xffff), random_int(0, 0xffff),
                random_int(0, 0xffff),
                random_int(0, 0xffff)
            ),
            default => sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                random_int(0, 0xffff), random_int(0, 0xffff),
                random_int(0, 0xffff),
                random_int(0, 0x0fff) | 0x4000,
                random_int(0, 0x3fff) | 0x8000,
                random_int(0, 0xffff), random_int(0, 0xffff), random_int(0, 0xffff))
        };
    }

    /**
     * Chaîne de caractères aléatoires Alphanumériques
     */
    /**
     * @param int $length
     * @return string
     * @throws \Random\RandomException
     */
    public static function randString(int $length): string
    {
        $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $uId = '';
        $max = strlen($chars) - 1;

        for ($i = 0; $i < $length; $i++) {
            $uId .= $chars[random_int(0, $max)];
        }

        return $uId;
    }

    /**
     * Chaîne numérique aléatoire
     */
    /**
     * @param int $length
     * @return string
     */
    public static function randomInt(int $length): string
    {
        $int = '';
        try {
            for ($i = 0; $i < $length; $i++) {
                $int .= (string)random_int(0, 9);
            }
        } catch(\Throwable $e) {
            Logger::getInstance()->log($e, 'php', 'error');
        }
        return $int;
    }

    /**
     * Liste d'IDs aléatoires sans doublons
     */
    /**
     * @param int $nb
     * @param int $max
     * @param int $min
     * @param bool $duplicate
     * @return array
     * @throws \Random\RandomException
     */
    public static function getRandomIds(int $nb, int $max = 0, int $min = 1, bool $duplicate = false): array
    {
        $ids = [];
        if ($nb <= 0 || $max <= 0) return $ids;

        // Sécurité pour éviter boucle infinie si $nb > plage disponible
        if (!$duplicate && $nb > ($max - $min + 1)) {
            $nb = ($max - $min + 1);
        }

        for ($i = 0; $i < $nb; $i++) {
            $val = random_int($min, $max);
            if (!$duplicate && in_array($val, $ids)) {
                $i--; // On recommence le tour
                continue;
            }
            $ids[] = $val;
        }
        return $ids;
    }

    /**
     * Conversion courte de nombres (Base 62)
     */
    /**
     * @param string|int $in
     * @param bool $to_num
     * @param mixed $pad_up
     * @param string $passKey
     * @return string|int
     */
    public static function alphaID(string|int $in, bool $to_num = false, mixed $pad_up = false, string $passKey = ''): string|int
    {
        try {
            if (!extension_loaded('bcmath')) {
                throw new \RuntimeException('bcmath extension not loaded');
            }

            $index = "abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";

            if ($passKey !== '') {
                $passhash = hash('sha256', $passKey);
                if (strlen($passhash) < strlen($index)) $passhash = hash('sha512', $passKey);

                $i = str_split($index);
                $p = str_split(substr($passhash, 0, strlen($index)));

                array_multisort($p, SORT_DESC, $i);
                $index = implode($i);
            }

            $base = strlen($index);

            if ($to_num) {
                $in = strrev((string)$in);
                $out = '0';
                $len = strlen($in) - 1;
                for ($t = 0; $t <= $len; $t++) {
                    $bcpow = bcpow((string)$base, (string)($len - $t));
                    $pos = strpos($index, $in[$t]);
                    $out = bcadd($out, bcmul((string)$pos, $bcpow));
                }

                if (is_numeric($pad_up)) {
                    $pad_up--;
                    if ($pad_up > 0) {
                        $out = bcsub($out, bcpow((string)$base, (string)$pad_up));
                    }
                }
                return $out;
            } else {
                if (is_numeric($pad_up)) {
                    $pad_up--;
                    if ($pad_up > 0) {
                        $in = bcadd((string)$in, bcpow((string)$base, (string)$pad_up));
                    }
                }
                $out = "";
                // Utilisation de bcmath pour les calculs sur grands nombres
                $in = (string)$in;
                while (bccomp($in, '0') > 0) {
                    $mod = bcmod($in, (string)$base);
                    $out .= $index[(int)$mod];
                    $in = bcdiv(bcsub($in, $mod), (string)$base);
                }
                return strrev($out);
            }
        } catch (\Throwable $e) {
            Logger::getInstance()->log($e, "php", "error");
            return '';
        }
    }
}