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
 * Class MathTool
 * Fournit des utilitaires de validation, de conversion numérique et de calcul monétaire.
 */
class MathTool
{
    /**
     * Taux de change par défaut (exemple statique, pourrait être chargé via une API).
     */
    private static array $exchangeRates = [
        'EUR_USD' => 1.09,
        'USD_EUR' => 0.92,
        'EUR_GBP' => 0.86,
        'GBP_EUR' => 1.16
    ];

    /**
     * Vérifie si la chaîne représente un nombre (Entier ou Flottant).
     */
    public static function isNumeric(string $str): int|float|bool
    {
        $val = self::isInt($str);
        if ($val !== false) return $val;

        return self::isFloat($str);
    }

    /**
     * Vérifie si la chaîne est un nombre à virgule flottante.
     */
    public static function isFloat(string $str): float|bool
    {
        $result = filter_var($str, FILTER_VALIDATE_FLOAT);
        return ($result !== false) ? (float)$result : false;
    }

    /**
     * Vérifie si la chaîne est un entier.
     */
    public static function isInt(string $str): int|bool
    {
        $result = filter_var($str, FILTER_VALIDATE_INT);
        return ($result !== false) ? (int)$result : false;
    }

    /**
     * Arrondi spécifique (Comptable, Plafond, Plancher).
     * @param float $value
     * @param int $precision
     * @param string $type 'round', 'ceil', 'floor'
     * @return float
     */
    public static function formatNumber(float $value, int $precision = 2, string $type = 'round'): float
    {
        return match ($type) {
            'ceil'  => ceil($value * pow(10, $precision)) / pow(10, $precision),
            'floor' => floor($value * pow(10, $precision)) / pow(10, $precision),
            default => round($value, $precision)
        };
    }

    /**
     * Conversion de devises simple.
     * @param float $amount
     * @param string $from ISO Code (EUR, USD, GBP)
     * @param string $to ISO Code
     * @return float|bool
     */
    public static function convertCurrency(float $amount, string $from, string $to): float|bool
    {
        $pair = strtoupper($from . '_' . $to);

        if ($from === $to) return $amount;

        if (!isset(self::$exchangeRates[$pair])) {
            return false;
        }

        return self::formatNumber($amount * self::$exchangeRates[$pair], 2);
    }

    /**
     * Calcule un pourcentage avec précision.
     */
    public static function getPercentage(float $number, float $total, int $precision = 2): float
    {
        if ($total <= 0) return 0.0;
        return round(($number / $total) * 100, $precision);
    }
}