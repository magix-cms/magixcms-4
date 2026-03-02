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
use DateTimeImmutable;
use DateTimeZone;
use DateInterval;
use Throwable;

/**
 * Class DateTool
 * Utilitaire de manipulation, validation et conversion de dates.
 * Utilise DateTimeImmutable pour éviter les effets de bord.
 *
 * @package Magepattern\Component\Tool
 */
class DateTool
{
    /** @var string Format Date Européen (ex: 05/02/2026) */
    public const EU_FORMAT = 'd/m/Y';

    /** @var string Format DateTime Européen (ex: 05/02/2026 14:30:00) */
    public const EU_DATETIME = 'd/m/Y H:i:s';

    /** @var string Format Date SQL Standard (ex: 2026-02-05) */
    public const SQL_FORMAT = 'Y-m-d';

    /** @var string Format DateTime SQL Standard (ex: 2026-02-05 14:30:00) */
    public const SQL_DATETIME = 'Y-m-d H:i:s';

    /** @var string Format ISO 8601 pour Sitemaps et API (ex: 2026-02-05T14:30:00+01:00) */
    public const W3C_FORMAT = 'Y-m-d\TH:i:sP';

    /**
     * Retourne une date formatée selon le standard demandé.
     * Gère automatiquement les timestamps et les chaînes de caractères.
     *
     * @example DateTool::getDate('now', 'sql')
     * @param int|string $time Timestamp UNIX ou chaîne compatible DateTime (ex: "now", "2026-01-01")
     * @param string $format Format de sortie ('rfc1123', 'sql', 'w3c', ou format personnalisé 'd/m/Y')
     * @return string La date formatée ou la date GMT actuelle en cas d'erreur critique
     */
    public static function getDate(int|string $time = 'now', string $format = 'rfc1123'): string
    {
        try {
            // Utilisation de DateTimeImmutable pour ne jamais modifier l'objet original par erreur
            $date = is_numeric($time)
                ? (new DateTimeImmutable())->setTimestamp((int)$time)
                : new DateTimeImmutable($time);

            // Match expression (PHP 8) pour gérer les formats nommés
            return match (strtolower($format)) {
                'rfc1123' => $date->format('D, d M Y H:i:s \G\M\T'),
                'rfc1036' => $date->format('l, d-M-y H:i:s \G\M\T'),
                'asctime' => $date->format('D M j H:i:s Y'),
                'sql'     => $date->format(self::SQL_FORMAT),
                'datetime'=> $date->format(self::SQL_DATETIME),
                'w3c'     => $date->format(self::W3C_FORMAT),
                default   => $date->format($format)
            };
        } catch (Throwable $e) {
            // En cas d'erreur (ex: format invalide), on log et on retourne une valeur sûre (GMT)
            Logger::getInstance()->log($e, "php", "error");
            return gmdate('Y-m-d H:i:s');
        }
    }

    /**
     * Convertit une date (généralement format EU) vers le format SQL DATE.
     * Utile avant une insertion en base de données.
     *
     * @example DateTool::toSql('25/12/2026') -> '2026-12-25'
     * @param string $date La date à convertir
     * @param string $fromFormat Le format d'origine (par défaut d/m/Y)
     * @return string|bool La date au format Y-m-d ou false si conversion impossible
     */
    public static function toSql(string $date, string $fromFormat = self::EU_FORMAT): string|bool
    {
        return self::convert($date, $fromFormat, self::SQL_FORMAT);
    }

    /**
     * Convertit une date vers le format SQL DATETIME.
     *
     * @example DateTool::toSqlDateTime('25/12/2026 14:00') -> '2026-12-25 14:00:00'
     * @param string $date La date à convertir
     * @param string $fromFormat Le format d'origine (par défaut d/m/Y H:i:s)
     * @return string|bool La date au format Y-m-d H:i:s ou false
     */
    public static function toSqlDateTime(string $date, string $fromFormat = self::EU_DATETIME): string|bool
    {
        return self::convert($date, $fromFormat, self::SQL_DATETIME);
    }

    /**
     * Génère une date au format W3C (ISO 8601).
     * Indispensable pour les flux RSS, Atom et les Sitemaps XML.
     *
     * @param int|string $time Timestamp ou chaîne (défaut: 'now')
     * @return string
     */
    public static function toW3C(int|string $time = 'now'): string
    {
        return self::getDate($time, self::W3C_FORMAT);
    }

    /**
     * Helper interne pour convertir les formats de date de manière stricte.
     *
     * @param string $date
     * @param string $from
     * @param string $to
     * @return string|bool
     */
    private static function convert(string $date, string $from, string $to): string|bool
    {
        try {
            $d = DateTimeImmutable::createFromFormat($from, $date);
            // On vérifie $d car createFromFormat peut retourner false en cas d'échec silencieux
            return $d ? $d->format($to) : false;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Valide si une chaîne correspond strictement à un format attendu.
     * Contrairement à strtotime, ceci refuse "2026-13-45".
     *
     * @param string $date La date à tester
     * @param string $format Le format attendu (ex: DateTool::EU_FORMAT)
     * @return bool
     */
    public static function validateFormat(string $date, string $format = self::EU_FORMAT): bool
    {
        $d = DateTimeImmutable::createFromFormat($format, $date);
        // La double vérification ($d->format === $date) assure que PHP n'a pas "corrigé" la date
        // Ex: sans ça, le 32/01 deviendrait le 01/02.
        return $d && $d->format($format) === $date;
    }

    /**
     * Wrapper pour checkdate().
     * Vérifie la validité grégorienne (années bissextiles incluses).
     *
     * @param int $y Année
     * @param int $m Mois
     * @param int $d Jour
     * @return bool
     */
    public static function isValid(int $y, int $m, int $d): bool
    {
        return checkdate($m, $d, $y);
    }

    /**
     * Calcule la différence entre deux dates.
     *
     * @param string $date1 Date de début
     * @param string $date2 Date de fin (si omis, utilise 'now' via DateTime)
     * @return DateInterval|bool L'objet intervalle ou false en cas d'erreur
     */
    public static function diff(string $date1, string $date2): DateInterval|bool
    {
        try {
            $d1 = new DateTimeImmutable($date1);
            $d2 = new DateTimeImmutable($date2);
            return $d1->diff($d2);
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Trouve la TimeZone correspondante à un décalage horaire (offset).
     *
     * @param int $offset Décalage en secondes par rapport à UTC (ex: 3600 pour UTC+1)
     * @return DateTimeZone|bool
     */
    public static function findTimeZone(int $offset): DateTimeZone|bool
    {
        // 1. Recherche incluant l'heure d'été (DST)
        $name = timezone_name_from_abbr("", $offset, 1);

        // 2. Si échec, recherche sans l'heure d'été
        if ($name === false) {
            $name = timezone_name_from_abbr("", $offset, 0);
        }

        try {
            return $name !== false ? new DateTimeZone($name) : false;
        } catch (Throwable $t) {
            Logger::getInstance()->log($t,"php", "error", Logger::LOG_MONTH, Logger::LOG_LEVEL_ERROR);
            return false;
        }
    }
}