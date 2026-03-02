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

use DateInterval;
use Exception;
use DateTimeInterface;
use Magepattern\Component\Debug\Logger;

class DateIntervalTool
{
    /**
     * Crée une instance de DateInterval de manière sécurisée.
     * * @param string $duration La valeur de durée (ex: "P1D" ou "1 day")
     * @param string $mode     Le format d'entrée ('iso' ou 'human')
     * @return DateInterval|false Retourne l'objet ou false en cas d'erreur
     *
     * Example :
     * use Magepattern\Component\Tool\DateIntervalTool;
     *
     * // Ajout de 1 An, 2 Mois et 4 Jours
     * $interval = DateIntervalTool::create('P1Y2M4D', 'iso');
     *
     * if ($interval) {
     * $date = new DateTime();
     * $date->add($interval);
     * echo $date->format('Y-m-d');
     * }
     *
     * // Ajout de "2 jours et 4 heures"
     * $interval = DateIntervalTool::create('2 days + 4 hours', 'human');
     *
     * // Utilisation avec DateTime
     * $date = new DateTime('2026-01-01');
     * $date->add($interval); // Deviendra 2026-01-03 04:00:00
     */
    public static function create(string $duration, string $mode = 'iso'): DateInterval|false
    {
        try {
            return match ($mode) {
                // Mode ISO 8601 (ex: "P1Y2M", "PT4H") - Correspond à votre ancien type 'object'
                'iso', 'object' => new DateInterval($duration),

                // Mode Humain (ex: "1 day + 12 hours") - Correspond à votre ancien type 'string'
                'human', 'string' => DateInterval::createFromDateString($duration),

                default => throw new \InvalidArgumentException("Mode '$mode' non supporté par DateIntervalTool."),
            };
        } catch (Exception $e) {
            // Log l'erreur via votre Logger existant et retourne false pour ne pas casser l'app
            Logger::getInstance()->log($e, "php_date", "error", Logger::LOG_MONTH, Logger::LOG_LEVEL_ERROR);
            return false;
        }
    }
    /**
     * Convertit un DateInterval (ou la différence entre deux dates) en chaîne ISO 8601 (ex: P1Y2DT4H)
     * * @param DateInterval|DateTimeInterface $input Un intervalle ou une date de début
     * @param DateTimeInterface|null $end Date de fin (si le premier paramètre est une date)
     * @return string La chaîne formatée (ex: "P1D") ou "PT0S" si vide
     *
     * Exemples
     *
     * use Magepattern\Component\Tool\DateIntervalTool;
     *
     * $start = new DateTime('2023-01-01');
     * $end   = new DateTime('2024-03-15');
     *
     * // Retournera : "P1Y2M14D" (1 An, 2 Mois, 14 Jours)
     * $isoString = DateIntervalTool::toIso($start, $end);
     *
     * echo "Durée stockée : " . $isoString;
     *
     *
     * // 1. On crée l'objet via votre méthode existante
     * $interval = DateIntervalTool::create('2 weeks', 'human');
     *
     * // 2. On le convertit en ISO pour le stockage
     * // Retournera : "P14D"
     * $isoString = DateIntervalTool::toIso($interval);
     *
     *
     * // Petite astuce PHP : on crée une date zéro et on ajoute les secondes
     * $dt1 = new DateTime('@0');
     * $dt2 = new DateTime('@3600');
     *
     * // Retournera : "PT1H"
     * echo DateIntervalTool::toIso($dt1, $dt2);
     */
    public static function toIso(DateInterval|DateTimeInterface $input, ?DateTimeInterface $end = null): string
    {
        // 1. Si on passe deux dates, on calcule d'abord la différence
        if ($input instanceof DateTimeInterface) {
            if ($end === null) {
                return 'PT0S'; // Pas de date de fin = durée nulle
            }
            $interval = $input->diff($end);
        } else {
            $interval = $input;
        }

        // 2. Construction de la chaîne ISO
        $date = [];
        if ($interval->y) $date[] = $interval->y . 'Y';
        if ($interval->m) $date[] = $interval->m . 'M';
        if ($interval->d) $date[] = $interval->d . 'D';

        $time = [];
        if ($interval->h) $time[] = $interval->h . 'H';
        if ($interval->i) $time[] = $interval->i . 'M';
        if ($interval->s) $time[] = $interval->s . 'S';

        // 3. Assemblage
        $spec = 'P' . implode('', $date);

        if (!empty($time)) {
            $spec .= 'T' . implode('', $time);
        }

        // Cas particulier : durée vide
        return $spec === 'P' ? 'PT0S' : $spec;
    }
}