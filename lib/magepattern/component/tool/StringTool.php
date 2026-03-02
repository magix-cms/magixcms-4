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

use Magepattern\Component\Debug\Logger;
use Throwable;

/**
 * Class StringTool
 * Fournit des utilitaires pour la manipulation et la validation de chaînes de caractères.
 * Compatible UTF-8 (Multibyte).
 */
class StringTool
{
    /**
     * Regex pour validation d'URL complexe (fallback si filter_var ne suffit pas).
     */
    public const REGEX_URL_FORMAT = '~^(https?|ftps?):(([a-z0-9-]+\.)+[a-z]{2,6}|\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})(:[0-9]+)?(/?|/\S+)$~ix';

    /**
     * Recherche une ou plusieurs sous-chaînes.
     *
     * @param string $haystack La botte de foin.
     * @param array $needles Les aiguilles à chercher.
     * @param bool $contains Mode de recherche :
     * - true : Retourne TRUE si AU MOINS UNE aiguille est trouvée.
     * - false : Retourne TRUE si AUCUNE aiguille n'est trouvée.
     * @return bool
     */
    public static function str_search(string $haystack, array $needles, bool $contains = true): bool
    {
        if (empty($needles)) {
            return !$contains;
        }

        foreach ($needles as $needle) {
            if (is_string($needle) && str_contains($haystack, $needle)) {
                return $contains;
            }
        }

        return !$contains;
    }

    /**
     * Vérifie si la chaîne est une URL valide.
     * Utilise le filtre natif PHP 8 (plus robuste que la regex).
     */
    public static function isURL(string $url): bool
    {
        return (bool)filter_var($url, FILTER_VALIDATE_URL);
    }

    /**
     * Vérifie si la chaîne est un email valide.
     */
    public static function isMail(string $mail): bool
    {
        return (bool)filter_var($mail, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Vérifie si la chaîne ne contient que des lettres (Compatible UTF-8/Accents).
     * Remplace ctype_alpha qui échoue sur "Hélène".
     */
    public static function isAlpha(string $str): bool
    {
        // \p{L} match n'importe quelle lettre unicode, /u active le mode UTF-8
        return (bool)preg_match('/^[\p{L}]+$/u', $str);
    }

    /**
     * Vérifie si la chaîne est alphanumérique (Lettres + Chiffres, UTF-8).
     */
    public static function isAlphaNumeric(string $str): bool
    {
        // \p{L} pour lettres, \p{N} pour chiffres
        return (bool)preg_match('/^[\p{L}\p{N}]+$/u', $str);
    }

    /**
     * Vérifie la longueur minimale (en caractères, pas en octets).
     */
    public static function isMinString(string $str, int $size): bool
    {
        return mb_strlen($str, 'UTF-8') < $size;
    }

    /**
     * Vérifie la longueur maximale (en caractères).
     */
    public static function isMaxString(string $str, int $size): bool
    {
        return mb_strlen($str, 'UTF-8') > $size;
    }

    /**
     * Vérifie si la chaîne est Alpha ET respecte la longueur max.
     * Note : Retourne un booléen strict (l'ancienne version retournait une concaténation "11").
     */
    public static function isAlphaMax(string $str, int $lg_max): bool
    {
        $clean = trim($str);
        return self::isAlpha($clean) && !self::isMaxString($clean, $lg_max);
    }

    /**
     * Vérifie si la chaîne est AlphaNumérique ET respecte la longueur max.
     */
    public static function isAlphaNumericMax(string $str, int $lg_max): bool
    {
        $clean = trim($str);
        return self::isAlphaNumeric($clean) && !self::isMaxString($clean, $lg_max);
    }

    /**
     * Vérifie si la chaîne est Numérique ET respecte la longueur max.
     */
    public static function isNumericClean(string $str, int $lg_max): bool
    {
        $clean = trim($str);
        // Utilisation de MathTool si disponible, sinon is_numeric natif
        $isNumeric = class_exists(MathTool::class) ? MathTool::isNumeric($clean) : is_numeric($clean);

        return $isNumeric && !self::isMaxString($clean, $lg_max);
    }

    /**
     * Convertit en majuscules (Support UTF-8 natif).
     */
    public static function strtoupper(string $str): string
    {
        return mb_strtoupper($str, 'UTF-8');
    }

    /**
     * Convertit en minuscules (Support UTF-8 natif).
     */
    public static function strtolower(string $str): string
    {
        return mb_strtolower($str, 'UTF-8');
    }

    /**
     * Met la première lettre en majuscule (Support UTF-8).
     */
    public static function ucFirst(string $str): string
    {
        if ($str === '') return '';

        $firstChar = mb_substr($str, 0, 1, 'UTF-8');
        $rest = mb_substr($str, 1, null, 'UTF-8');

        return mb_strtoupper($firstChar, 'UTF-8') . $rest;
    }

    /**
     * Tronque une chaîne sans couper les mots.
     *
     * @param string $str La chaîne à couper.
     * @param int $lg_max Longueur maximale désirée.
     * @param string $delimiter Délimiteur à ajouter (ex: '...').
     * @return string
     */
    public static function truncate(string $str, int $lg_max, string $delimiter = '...'): string
    {
        // Si la chaîne est plus courte que la limite, on la retourne telle quelle
        if (mb_strlen($str, 'UTF-8') <= $lg_max) {
            return $str;
        }

        // On coupe d'abord à la longueur max
        $cut = mb_substr($str, 0, $lg_max, 'UTF-8');

        // On cherche le dernier espace pour ne pas couper un mot en deux
        $lastSpace = mb_strrpos($cut, ' ', 0, 'UTF-8');

        // Si on trouve un espace, on coupe là. Sinon on garde la coupure brute.
        if ($lastSpace !== false) {
            $cut = mb_substr($cut, 0, $lastSpace, 'UTF-8');
        }

        return $cut . $delimiter;
    }
}