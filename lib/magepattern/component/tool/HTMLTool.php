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

class HTMLTool
{
    /**
     * Flags de sécurité par défaut pour PHP 8.
     * ENT_QUOTES : Échappe les doubles AND les simples quotes (Sécurité XSS).
     * ENT_SUBSTITUTE : Remplace les caractères invalides UTF-8 au lieu de renvoyer une chaîne vide.
     * ENT_HTML5 : Utilise les standards HTML5.
     */
    private const DEFAULT_FLAGS = ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5;

    /**
     * Échappement HTML standard (Léger).
     * Convertit les caractères spéciaux (&, ", ', <, >) en entités.
     *
     * @param string $str Chaîne à échapper.
     * @return string
     */
    public static function escapeHTML(string $str): string
    {
        return htmlspecialchars($str, self::DEFAULT_FLAGS, 'UTF-8');
    }

    /**
     * Échappement HTML Extrême.
     * Convertit TOUS les caractères éligibles en entités (ex: é devient &eacute;).
     * Utile si l'encodage de la page n'est pas garanti en UTF-8.
     *
     * @param string $str
     * @return string
     */
    public static function escapeExtremeHTML(string $str): string
    {
        return htmlentities($str, self::DEFAULT_FLAGS, 'UTF-8');
    }

    /**
     * Décode les entités HTML "extrêmes".
     *
     * @param string $str
     * @return string
     */
    public static function decodeExtremeHTML(string $str): string
    {
        return html_entity_decode($str, self::DEFAULT_FLAGS, 'UTF-8');
    }

    /**
     * Décode les entités HTML standard.
     *
     * @param string $str String à décoder.
     * @param bool $keep_special Si true, préserve les balises de structure (<, >, &).
     * @return string
     */
    public static function decodeEntities(string $str, bool $keep_special = false): string
    {
        if ($keep_special) {
            // On protège temporairement les caractères structurels pour qu'ils ne soient pas décodés
            $str = str_replace(
                ['&amp;', '&gt;', '&lt;'],
                ['&amp;amp;', '&amp;gt;', '&amp;lt;'],
                $str
            );
        }

        // Remplacement spécifique pour l'apostrophe XML souvent mal gérée
        $str = str_replace('&apos;', "'", $str);

        return html_entity_decode($str, self::DEFAULT_FLAGS, 'UTF-8');
    }

    /**
     * Encode manuellement certaines entités.
     * Note: Préférer escapeHTML() pour un usage standard.
     *
     * @param string $str
     * @param bool $keep_special Si true, force l'encodage des balises de structure.
     * @return string
     */
    public static function encodeEntities(string $str, bool $keep_special = false): string
    {
        if ($keep_special) {
            $str = str_replace(
                ['&', '<', '</', '>'],
                ['&amp;', '&lt;', '&lt;/', '&gt;'],
                $str
            );
        }

        // Standardisation de l'apostrophe
        return str_replace("'", '&apos;', $str);
    }

    /**
     * Échappe une URL pour l'insertion dans un attribut HTML (href, src).
     *
     * @param string $str L'URL à protéger.
     * @return string
     */
    public static function escapeURL(string $str): string
    {
        // htmlspecialchars est la bonne méthode pour protéger une URL dans un attribut HTML.
        // str_replace('&', '&amp;') est insuffisant et peut briser une URL déjà échappée.
        return htmlspecialchars($str, self::DEFAULT_FLAGS, 'UTF-8');
    }

    /**
     * Échappe une chaîne pour l'insertion dans du JavaScript.
     * Utilise json_encode pour garantir que la chaîne est sûre, peu importe son contenu.
     *
     * @param string $str La chaîne à protéger.
     * @return string La chaîne échappée (sans les guillemets englobants de JSON si possible).
     */
    public static function escapeJS(string $str): string
    {
        try {
            // json_encode gère nativement les retours à la ligne, les quotes, etc.
            // JSON_HEX_APOS | JSON_HEX_QUOT garantit qu'on ne cassera pas le JS
            $json = json_encode($str, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);

            // json_encode ajoute des guillemets doubles au début et à la fin ("...").
            // Si vous voulez juste le contenu interne pour l'injecter : var x = 'HERE';
            // On retire les guillemets de début et fin.
            return substr($json, 1, -1);
        } catch (Throwable $e) {
            Logger::getInstance()->log($e, "php", "error");
            return '';
        }
    }
}