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

class EscapeTool
{
    /**
     * Carte des caractères accentués et spéciaux vers ASCII.
     * Déplacé en constante pour éviter la réallocation mémoire à chaque appel.
     */
    private const SEARCH = [
        'À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ă', 'Ą', 'Ç', 'Ć', 'Č', 'Œ', 'Ď', 'Đ',
        'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ă', 'ą', 'ç', 'ć', 'č', 'œ', 'ď', 'đ',
        'È', 'É', 'Ê', 'Ë', 'Ę', 'Ě', 'Ğ', 'Ì', 'Í', 'Î', 'Ï', 'İ', 'Ĺ', 'Ľ', 'Ł',
        'è', 'é', 'ê', 'ë', 'ę', 'ě', 'ğ', 'ì', 'í', 'î', 'ï', 'ı', 'ĺ', 'ľ', 'ł',
        'Ñ', 'Ń', 'Ň', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ő', 'Ŕ', 'Ř', 'Ś', 'Ş', 'Š',
        'ñ', 'ń', 'ň', 'ò', 'ó', 'ô', 'ö', 'ø', 'ő', 'ŕ', 'ř', 'ś', 'ş', 'š',
        'Ţ', 'Ť', 'Ù', 'Ú', 'Û', 'Ų', 'Ü', 'Ů', 'Ű', 'Ý', 'ß', 'Ź', 'Ż', 'Ž',
        'ţ', 'ť', 'ù', 'ú', 'û', 'ų', 'ü', 'ů', 'ű', 'ý', 'ÿ', 'ź', 'ż', 'ž',
        // Cyrillic
        'А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р',
        'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'р',
        'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я',
        'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я'
    ];

    private const REPLACE = [
        'A', 'A', 'A', 'A', 'A', 'A', 'AE', 'A', 'A', 'C', 'C', 'C', 'CE', 'D', 'D',
        'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'a', 'a', 'c', 'c', 'c', 'ce', 'd', 'd',
        'E', 'E', 'E', 'E', 'E', 'E', 'G', 'I', 'I', 'I', 'I', 'I', 'L', 'L', 'L',
        'e', 'e', 'e', 'e', 'e', 'e', 'g', 'i', 'i', 'i', 'i', 'i', 'l', 'l', 'l',
        'N', 'N', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'R', 'R', 'S', 'S', 'S',
        'n', 'n', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'r', 'r', 's', 's', 's',
        'T', 'T', 'U', 'U', 'U', 'U', 'U', 'U', 'U', 'Y', 'Y', 'Z', 'Z', 'Z',
        't', 't', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'y', 'y', 'z', 'z', 'z',
        // Cyrillic replacements (Mapping preserved from original)
        'A', 'B', 'B', 'r', 'A', 'E', 'E', 'X', '3', 'N', 'N', 'K', 'N', 'M', 'H', 'O', 'N', 'P',
        'a', 'b', 'b', 'r', 'a', 'e', 'e', 'x', '3', 'n', 'n', 'k', 'n', 'm', 'h', 'o', 'p',
        'C', 'T', 'Y', 'O', 'X', 'U', 'u', 'W', 'W', 'b', 'b', 'b', 'E', 'O', 'R',
        'c', 't', 'y', 'o', 'x', 'u', 'u', 'w', 'w', 'b', 'b', 'b', 'e', 'o', 'r'
    ];

    /**
     * Supprime les balises HTML, PHP, et les octets nuls.
     * @param string $str Chaîne à nettoyer
     * @return string
     */
    public static function clean(string $str): string
    {
        return strip_tags($str);
    }

    /**
     * Supprime les antislashs (Un-quote string quoted with addcslashes).
     * Utilisé pour nettoyer des entrées JSON ou des chemins échappés.
     * @param string $str
     * @return string
     */
    public static function cleanQuote(string $str): string
    {
        return stripcslashes($str);
    }

    /**
     * Convertit un chemin système au format Unix (Slash /).
     * @param string $path Chemin à convertir (optionnel, sinon retourne le séparateur)
     * @return string
     */
    public static function unix_separator(string $path = ''): string
    {
        if ($path === '') return '/';
        return str_replace('\\', '/', $path);
    }

    /**
     * Convertit un chemin système au format Windows (Backslash \).
     * @param string $path Chemin à convertir (optionnel, sinon retourne le séparateur)
     * @return string
     */
    public static function win_separator(string $path = ''): string
    {
        if ($path === '') return '\\';
        return str_replace('/', '\\', $path);
    }

    /**
     * Convertit le premier caractère d'une chaîne en sa valeur ASCII (0-255).
     * @param string $str
     * @return int
     */
    public static function convertASCII(string $str): int
    {
        return ord($str);
    }

    /**
     * Convertit une valeur ASCII (int) en caractère.
     * @param int $asciiCode
     * @return string
     */
    public static function decodeASCII(int $asciiCode): string
    {
        return chr($asciiCode);
    }

    /**
     * Remplace les caractères accentués par leur équivalent latin (ASCII).
     * Fallback manuel si l'extension Intl n'est pas disponible.
     *
     * @param string $str The string to convert
     * @return string The corrected string
     */
    public static function decode_utf8(string $str): string
    {
        return str_replace(self::SEARCH, self::REPLACE, $str);
    }
}