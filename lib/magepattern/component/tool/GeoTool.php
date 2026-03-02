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

class GeoTool
{
    /** @var int Rayon moyen de la Terre en kilomètres */
    public const EARTH_RADIUS_KM = 6371;

    /**
     * Calcule la "Bounding Box" (les limites min/max) autour d'un point.
     * Idéal pour le pré-filtrage SQL (très rapide).
     *
     * @param float $lat Latitude du centre en degrés
     * @param float $lon Longitude du centre en degrés
     * @param int|float $radius Rayon de recherche en kilomètres
     * @return array<string, float|int>
     */
    /**
     * @param float $lat
     * @param float $lon
     * @param int|float $radius
     * @return array
     */
    public static function getBoxLimits(float $lat, float $lon, int|float $radius): array
    {
        $latRad = deg2rad($lat);

        $maxLat = $lat + rad2deg($radius / self::EARTH_RADIUS_KM);
        $minLat = $lat - rad2deg($radius / self::EARTH_RADIUS_KM);

        $lonDelta = rad2deg(asin($radius / self::EARTH_RADIUS_KM) / cos($latRad));
        $maxLon = $lon + $lonDelta;
        $minLon = $lon - $lonDelta;

        return [
            'lat_rad' => $latRad,
            'lon_rad' => deg2rad($lon),
            'minLat'  => $minLat,
            'minLon'  => $minLon,
            'maxLat'  => $maxLat,
            'maxLon'  => $maxLon,
            'radius'  => $radius
        ];
    }

    /**
     * Calcule la distance exacte (en km) entre deux points géographiques.
     * Utilise la formule de Haversine pour tenir compte de la courbure terrestre.
     *
     * @param float $lat1 Latitude du point A
     * @param float $lon1 Longitude du point A
     * @param float $lat2 Latitude du point B
     * @param float $lon2 Longitude du point B
     * @return float Distance en kilomètres
     */
    /**
     * @param float $lat1
     * @param float $lon1
     * @param float $lat2
     * @param float $lon2
     * @return float
     */
    public static function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        // Conversion en radians
        $lat1Rad = deg2rad($lat1);
        $lon1Rad = deg2rad($lon1);
        $lat2Rad = deg2rad($lat2);
        $lon2Rad = deg2rad($lon2);

        // Différences
        $dLat = $lat2Rad - $lat1Rad;
        $dLon = $lon2Rad - $lon1Rad;

        // Formule de Haversine
        $a = sin($dLat / 2) ** 2 + cos($lat1Rad) * cos($lat2Rad) * sin($dLon / 2) ** 2;
        $c = 2 * asin(sqrt($a));

        return self::EARTH_RADIUS_KM * $c;
    }
}