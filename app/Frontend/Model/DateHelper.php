<?php

declare(strict_types=1);

namespace App\Frontend\Model;

class DateHelper
{
    /**
     * Formate les dates avec IntlDateFormatter (Moderne, PHP 8+)
     *
     * @param mixed $string La date à formater
     * @param string $pattern Le format ICU (ex: 'dd MMMM yyyy')
     * @param string $locale La langue (ex: 'fr', 'en')
     * @return string
     */
    public static function format(mixed $string, string $pattern = 'dd MMMM yyyy', string $locale = 'fr'): string
    {
        if (empty($string)) {
            return '';
        }

        if (empty($locale)) {
            $locale = 'fr_FR';
        }

        if (strlen($locale) === 2) {
            $locale = strtolower($locale) . '_' . strtoupper($locale); // Transforme "fr" en "fr_FR"
        }

        // 1. Conversion de l'entrée en Timestamp
        if ($string instanceof \DateTimeInterface) {
            $timestamp = $string->getTimestamp();
        } elseif (is_numeric($string)) {
            $timestamp = (int)$string;
        } else {
            $timestamp = strtotime((string)$string);
        }

        if ($timestamp === false) {
            return (string)$string;
        }

        // 2. Initialisation du formateur natif PHP (Intl)
        $formatter = new \IntlDateFormatter(
            $locale,
            \IntlDateFormatter::FULL,
            \IntlDateFormatter::FULL,
            date_default_timezone_get(),
            \IntlDateFormatter::GREGORIAN,
            $pattern
        );

        // 3. Formatage et retour
        if ($formatter !== null) {
            return $formatter->format($timestamp);
        }

        return date('Y-m-d', $timestamp);
    }
}
?>