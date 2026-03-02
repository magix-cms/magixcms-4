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

namespace Magepattern\Component\HTTP;

use Magepattern\Component\Debug\Logger;
use JsonException;

/**
 * Class JSON
 * * Fournit une interface robuste pour l'encodage et le décodage JSON
 * avec gestion native des exceptions et journalisation des erreurs.
 */
class JSON
{
    /**
     * Options de configuration pour les opérations JSON.
     */
    private array $options = [
        'decode' => [
            'assoc' => true,
            'depth' => 512,
            'flags' => JSON_THROW_ON_ERROR,
        ],
        'encode' => [
            'flags' => JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
            'depth' => 512,
        ],
    ];

    /**
     * JSON constructor.
     * * @param array $customOptions Permet de surcharger les flags ou la profondeur par défaut.
     */
    public function __construct(array $customOptions = [])
    {
        $this->options = array_replace_recursive($this->options, $customOptions);
    }

    /**
     * Décode une chaîne JSON en structure PHP.
     *
     * @param string $json La chaîne à décoder.
     * @param array $overrideOptions Options locales pour cet appel spécifique.
     * @return mixed Données décodées ou null en cas d'erreur.
     */
    public function decode(string $json, array $overrideOptions = []): mixed
    {
        $opt = array_merge($this->options['decode'], $overrideOptions);

        try {
            return json_decode($json, $opt['assoc'], $opt['depth'], $opt['flags']);
        } catch (JsonException $e) {
            Logger::getInstance()->log(
                "JSON Decode Error: " . $e->getMessage(),
                'php',
                'json_error',
                Logger::LOG_MONTH,
                Logger::LOG_LEVEL_ERROR
            );
            return null;
        }
    }

    /**
     * Encode une valeur PHP en chaîne JSON.
     *
     * @param mixed $value La valeur à encoder (tableau, objet, etc.).
     * @param array $overrideOptions Options locales (ex: JSON_PRETTY_PRINT).
     * @return string|null La chaîne JSON résultante ou null en cas d'erreur.
     */
    public function encode(mixed $value, array $overrideOptions = []): ?string
    {
        $opt = array_merge($this->options['encode'], $overrideOptions);

        try {
            return json_encode($value, $opt['flags'], $opt['depth']);
        } catch (JsonException $e) {
            Logger::getInstance()->log(
                "JSON Encode Error: " . $e->getMessage(),
                'php',
                'json_error',
                Logger::LOG_MONTH,
                Logger::LOG_LEVEL_ERROR
            );
            return null;
        }
    }

    /**
     * Valide la syntaxe d'une chaîne JSON sans la décoder.
     * * @param string $json
     * @return bool
     */
    public function isValid(string $json): bool
    {
        if (function_exists('json_validate')) {
            return json_validate($json);
        }

        try {
            json_decode($json, true, 512, JSON_THROW_ON_ERROR);
            return true;
        } catch (JsonException $e) {
            Logger::getInstance()->log(
                "JSON isValid Error: " . $e->getMessage(),
                'php',
                'json_error',
                Logger::LOG_MONTH,
                Logger::LOG_LEVEL_ERROR
            );
            return false;
        }
    }

    /**
     * Charge et décode un fichier JSON.
     *
     * @param string $path Chemin vers le fichier .json
     * @param array $overrideOptions
     * @return mixed
     */
    public function fromFile(string $path, array $overrideOptions = []): mixed
    {
        if (!file_exists($path) || !is_readable($path)) {
            Logger::getInstance()->log("JSON File missing or not readable: $path", 'php', 'json_error');
            return null;
        }

        $content = file_get_contents($path);
        return $this->decode($content, $overrideOptions);
    }

    /**
     * Encode et sauvegarde des données dans un fichier JSON.
     *
     * @param string $path Chemin de destination.
     * @param mixed $value Données à sauvegarder.
     * @param bool $pretty Activer le formatage lisible (Indentation).
     * @return bool True si l'écriture a réussi.
     */
    public function toFile(string $path, mixed $value, bool $pretty = true): bool
    {
        $flags = $this->options['encode']['flags'];

        if ($pretty) {
            $flags |= JSON_PRETTY_PRINT;
        }

        $json = $this->encode($value, ['flags' => $flags]);

        if ($json === null) {
            return false;
        }

        return (bool) file_put_contents($path, $json, LOCK_EX);
    }
}