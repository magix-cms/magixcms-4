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

use ResourceBundle;
use NumberFormatter;
use Collator;

/**
 * Class LocalizationTool
 * Gestion multilingue des pays et langues avec support Smarty (.conf en minuscules).
 */
class LocalizationTool
{
    private static array $cache = [];
    private static array $custom_countries = [];
    private static array $custom_languages = [];

    /**
     * Liste de secours intégrée (Pays)
     */
    /**
     * @var array
     */
    private static array $default_countries = [
        "AF"=>"Afghanistan", "AL"=>"Albania", "DZ"=>"Algeria", "AD"=>"Andorra", "AO"=>"Angola", "AG"=>"Antigua and Barbuda", "AR"=>"Argentina", "AM"=>"Armenia", "AW"=>"Aruba", "AU"=>"Australia", "AT"=>"Austria", "AZ"=>"Azerbaijan", "BS"=>"Bahamas", "BH"=>"Bahrain", "BD"=>"Bangladesh", "BB"=>"Barbados", "BY"=>"Belarus", "BE"=>"Belgium", "BZ"=>"Belize", "BJ"=>"Benin", "BM"=>"Bermuda", "BT"=>"Bhutan", "BO"=>"Bolivia", "BA"=>"Bosnia-Herzegovina", "BW"=>"Botswana", "BR"=>"Brazil", "VG"=>"British Virgin Islands", "BN"=>"Brunei", "BG"=>"Bulgaria", "BF"=>"Burkina Faso", "BI"=>"Burundi", "KH"=>"Cambodia", "CM"=>"Cameroon", "CA"=>"Canada", "CV"=>"Cape Verde", "KY"=>"Cayman Islands", "CF"=>"Central African Republic", "TD"=>"Chad", "CL"=>"Chile", "CN"=>"China", "CO"=>"Colombia", "KM"=>"Comoros", "CG"=>"Congo (Brazzaville)", "CD"=>"Congo (Democratic Rep.)", "CR"=>"Costa Rica", "CI"=>"Cote d'Ivoire", "HR"=>"Croatia", "CU"=>"Cuba", "CY"=>"Cyprus", "CZ"=>"Czech Republic", "DK"=>"Denmark", "DJ"=>"Djibouti", "DM"=>"Dominica", "DO"=>"Dominican Republic", "EC"=>"Ecuador", "EG"=>"Egypt", "SV"=>"El Salvador", "GQ"=>"Equatorial Guinea", "ER"=>"Eritrea", "EE"=>"Estonia", "ET"=>"Ethiopia", "FK"=>"Falkland Islands", "FO"=>"Faroe Islands", "FJ"=>"Fiji", "FI"=>"Finland", "FR"=>"France", "GF"=>"French Guiana", "PF"=>"French Polynesia", "GA"=>"Gabon", "GM"=>"Gambia", "GE"=>"Georgia", "DE"=>"Germany", "GH"=>"Ghana", "GI"=>"Gibraltar", "GR"=>"Greece", "GL"=>"Greenland", "GD"=>"Grenada", "GP"=>"Guadeloupe", "GT"=>"Guatemala", "GG"=>"Guernsey", "GN"=>"Guinea", "GW"=>"Guinea-Bissau", "GY"=>"Guyana", "HT"=>"Haiti", "HN"=>"Honduras", "HK"=>"Hong Kong", "HU"=>"Hungary", "IS"=>"Iceland", "IN"=>"India", "ID"=>"Indonesia", "IR"=>"Iran", "IQ"=>"Iraq", "IE"=>"Ireland", "IM"=>"Isle of Man", "IL"=>"Israel", "IT"=>"Italy", "JM"=>"Jamaica", "JP"=>"Japan", "JE"=>"Jersey", "JO"=>"Jordan", "KZ"=>"Kazakhstan", "KE"=>"Kenya", "KI"=>"Kiribati", "KV"=>"Kosovo", "KW"=>"Kuwait", "KG"=>"Kyrgyzstan", "LA"=>"Laos", "LV"=>"Latvia", "LB"=>"Lebanon", "LS"=>"Lesotho", "LR"=>"Liberia", "LY"=>"Libya", "LI"=>"Liechtenstein", "LT"=>"Lithuania", "LU"=>"Luxembourg", "MO"=>"Macau", "MK"=>"Macedonia", "MG"=>"Madagascar", "MW"=>"Malawi", "MY"=>"Malaysia", "MV"=>"Maldives", "ML"=>"Mali", "MT"=>"Malta", "MH"=>"Marshall Islands", "MQ"=>"Martinique", "MR"=>"Mauritania", "MU"=>"Mauritius", "YT"=>"Mayotte", "MX"=>"Mexico", "FM"=>"Micronesia", "MD"=>"Moldova", "MC"=>"Monaco", "MN"=>"Mongolia", "ME"=>"Montenegro", "MA"=>"Morocco", "MZ"=>"Mozambique", "MM"=>"Myanmar", "NA"=>"Namibia", "NR"=>"Nauru", "NP"=>"Nepal", "NL"=>"Netherlands", "NC"=>"New Caledonia", "NZ"=>"New Zealand", "NI"=>"Nicaragua", "NE"=>"Niger", "NG"=>"Nigeria", "KP"=>"North Korea", "NO"=>"Norway", "OM"=>"Oman", "PK"=>"Pakistan", "PW"=>"Palau", "PA"=>"Panama", "PG"=>"Papua New Guinea", "PY"=>"Paraguay", "PE"=>"Peru", "PH"=>"Philippines", "PL"=>"Poland", "PT"=>"Portugal", "PR"=>"Puerto Rico", "QA"=>"Qatar", "RE"=>"Reunion", "RO"=>"Romania", "RU"=>"Russia", "RW"=>"Rwanda", "BL"=>"Saint Barthelemy", "KN"=>"Saint Kitts and Nevis", "LC"=>"Saint Lucia", "MF"=>"Saint Martin", "PM"=>"Saint Pierre and Miquelon", "VC"=>"Saint Vincent and the Grenadines", "WS"=>"Samoa", "SM"=>"San Marino", "ST"=>"Sao Tome and Principe", "SA"=>"Saudi Arabia", "SN"=>"Senegal", "RS"=>"Serbia", "SC"=>"Seychelles", "SL"=>"Sierra Leone", "SG"=>"Singapore", "SK"=>"Slovakia", "SI"=>"Slovenia", "SB"=>"Solomon Islands", "SO"=>"Somalia", "ZA"=>"South Africa", "KR"=>"South Korea", "SS"=>"South Sudan", "ES"=>"Spain", "LK"=>"Sri Lanka", "SD"=>"Sudan", "SR"=>"Suriname", "SJ"=>"Svalbard", "SZ"=>"Swaziland", "SE"=>"Sweden", "CH"=>"Switzerland", "SY"=>"Syria", "TW"=>"Taiwan", "TJ"=>"Tajikistan", "TZ"=>"Tanzania", "TH"=>"Thailand", "TL"=>"Timor-Leste", "TG"=>"Togo", "TO"=>"Tonga", "TT"=>"Trinidad and Tobago", "TN"=>"Tunisia", "TR"=>"Turkey", "TM"=>"Turkmenistan", "TC"=>"Turks and Caicos", "TV"=>"Tuvalu", "UG"=>"Uganda", "UA"=>"Ukraine", "AE"=>"United Arab Emirates", "GB"=>"United Kingdom", "US"=>"United States", "UY"=>"Uruguay", "UZ"=>"Uzbekistan", "VU"=>"Vanuatu", "VA"=>"Vatican City", "VE"=>"Venezuela", "VN"=>"Vietnam", "WF"=>"Wallis et Futuna", "EH"=>"Western Sahara", "YE"=>"Yemen", "ZM"=>"Zambia", "ZW"=>"Zimbabwe"
    ];
    /**
     * @var array|string[]
     */
    private static array $default_languages = [
        "ar"=>"Arabic", "az"=>"Azerbaijani", "bg"=>"Bulgarian", "bs"=>"Bosnian", "ca"=>"Catalan", "fr-ca"=>"Canadian (French)", "en-ca"=>"Canadian (English)", "cs"=>"Czech", "da"=>"Danish", "de"=>"German", "el"=>"Greek", "en"=>"English", "es"=>"Spanish", "et"=>"Estonian", "fi"=>"Finnish", "fr"=>"French", "he"=>"Hebrew", "hr"=>"Croatian", "hu"=>"Hungarian", "hy"=>"Armenian", "is"=>"Icelandic", "it"=>"Italian", "ja"=>"Japanese", "ko"=>"Korean", "lt"=>"Lithuanian", "lv"=>"Latvian", "mk"=>"Macedonian", "mn"=>"Mongolian", "mt"=>"Maltese", "nl"=>"Dutch", "no"=>"Norwegian", "pl"=>"Polish", "pt"=>"Portuguese", "ro"=>"Romanian", "ru"=>"Russian", "sk"=>"Slovak", "sl"=>"Slovenian", "sq"=>"Albanian", "sr"=>"Serbian", "sv"=>"Swedish", "sz"=>"Montenegrin", "th"=>"Thai", "tr"=>"Turkish", "uk"=>"Ukrainian", "uz"=>"Uzbek", "vi"=>"Vietnamese", "zh"=>"Chinese"
    ];

    /**
     * Injecte un dictionnaire (ex: issu d'un .conf Smarty en minuscules).
     * Les clés sont normalisées en MAJUSCULES pour l'indexation interne.
     */
    /**
     * @param array $countries
     * @param string $locale
     * @return void
     */
    public static function setCountries(array $countries, string $locale = 'en'): void
    {
        self::$custom_countries[$locale] = array_change_key_case($countries, CASE_UPPER);
    }

    /**
     * @param array $languages
     * @param string $locale
     * @return void
     */
    public static function setLanguages(array $languages, string $locale = 'en'): void
    {
        self::$custom_languages[$locale] = array_change_key_case($languages, CASE_LOWER);
    }

    /**
     * Retourne la liste complète traduite (Priorité : Injection > Intl > Fallback).
     * @param string $locale
     * @return array
     */
    public static function getCountries(string $locale = 'en'): array
    {
        // 1. Retourne l'injection si présente
        if (!empty(self::$custom_countries[$locale])) {
            return self::$custom_countries[$locale];
        }

        // 2. Tentative via Intl
        if (extension_loaded('intl')) {
            if (isset(self::$cache['countries'][$locale])) return self::$cache['countries'][$locale];
            $countries = [];
            $bundle = ResourceBundle::create($locale, 'ICUDATA', true);
            if ($bundle) {
                $regions = $bundle->get('Countries') ?: [];
                foreach ($regions as $code => $name) {
                    if (strlen($code) === 2 && !is_numeric($code)) $countries[$code] = $name;
                }
            }
            if (!empty($countries)) {
                $collator = new Collator($locale);
                $collator->asort($countries);
                self::$cache['countries'][$locale] = $countries;
                return $countries;
            }
        }

        // 3. Retourne la liste par défaut (Fallback)
        return self::$default_countries;
    }

    /**
     * Traduit un record (élément unique) avec insensibilité à la casse.
     * @param string $countryCode
     * @param string $locale
     * @return string
     */
    public static function getCountry(string $countryCode, string $locale = 'en'): string
    {
        $countries = self::getCountries($locale);
        $code = strtoupper($countryCode);
        return $countries[$code] ?? $countryCode;
    }

    /**
     * @param string $locale
     * @return array|string[]
     */
    public static function getLanguages(string $locale = 'en'): array
    {
        if (!empty(self::$custom_languages[$locale])) return self::$custom_languages[$locale];

        if (extension_loaded('intl')) {
            if (isset(self::$cache['languages'][$locale])) return self::$cache['languages'][$locale];
            $languages = [];
            $bundle = ResourceBundle::create($locale, 'ICUDATA', true);
            if ($bundle) {
                $langs = $bundle->get('Languages') ?: [];
                foreach ($langs as $code => $name) {
                    if (strlen($code) <= 5) $languages[$code] = $name;
                }
            }
            if (!empty($languages)) {
                $collator = new Collator($locale);
                $collator->asort($languages);
                self::$cache['languages'][$locale] = $languages;
                return $languages;
            }
        }
        return self::$default_languages;
    }

    /**
     * @param string $langCode
     * @param string $locale
     * @return string
     */
    public static function getLanguage(string $langCode, string $locale = 'en'): string
    {
        $languages = self::getLanguages($locale);
        $code = strtolower($langCode);
        return $languages[$code] ?? $langCode;
    }

    /**
     * @param string $countryCode
     * @return string
     */
    public static function getFlag(string $countryCode): string
    {
        $code = strtoupper($countryCode);
        if (strlen($code) !== 2) return '';
        return mb_chr(ord($code[0]) + 127397) . mb_chr(ord($code[1]) + 127397);
    }
}