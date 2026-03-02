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

namespace Magepattern\Component\file;

class CacheTool
{
    private string $cacheDir;
    private int $defaultTtl;

    /**
     * @param string $secureCacheDir Le chemin absolu du dossier (généré par FileTool)
     * @param int $defaultTtl Durée de vie par défaut en secondes (ex: 3600 = 1h)
     */
    public function __construct(string $secureCacheDir, int $defaultTtl = 3600)
    {
        $this->cacheDir = rtrim($secureCacheDir, DIRECTORY_SEPARATOR);
        $this->defaultTtl = $defaultTtl;
    }

    /**
     * Génère une empreinte SHA-256 unique, rapide et sans risque de collision.
     */
    /**
     * Génère une empreinte SHA-256 unique, avec un Tag optionnel pour le ciblage.
     * * @param string $sql La requête
     * @param array $params Les paramètres
     * @param string $tag Ex: 'pages', 'products'
     */
    public function generateKey(string $sql, array $params, string $tag = ''): string
    {
        $payload = $sql . json_encode($params);
        $hash = hash('sha256', $payload);

        // Sécurisation du tag pour le système de fichiers
        $safeTag = preg_replace('/[^a-zA-Z0-9_-]/', '', $tag);

        return $safeTag !== '' ? $safeTag . '_' . $hash : $hash;
    }

    /**
     * Supprime uniquement le cache lié à un tag spécifique.
     * * @param string $tag Ex: 'pages'
     */
    public function clearByTag(string $tag): void
    {
        $safeTag = preg_replace('/[^a-zA-Z0-9_-]/', '', $tag);
        if ($safeTag === '') {
            return;
        }

        // On cherche tous les fichiers qui commencent par ce tag
        $pattern = $this->cacheDir . DIRECTORY_SEPARATOR . $safeTag . '_*.cache';
        $files = glob($pattern);

        if ($files) {
            foreach ($files as $file) {
                if (is_file($file)) {
                    @unlink($file);
                }
            }
        }
    }

    /**
     * Récupère et valide une donnée du cache.
     */
    public function get(string $key): mixed
    {
        // SÉCURITÉ 1 : Strictement alphanumérique (Anti Path-Traversal)
        $safeKey = preg_replace('/[^a-zA-Z0-9_-]/', '', $key);
        $path = $this->cacheDir . DIRECTORY_SEPARATOR . $safeKey . '.cache';

        if (!file_exists($path)) {
            return null;
        }

        $fileContent = file_get_contents($path);
        if (!$fileContent) {
            return null;
        }

        // SÉCURITÉ 2 : Désérialisation stricte (bloque les attaques par injection d'objets)
        $data = @unserialize($fileContent, ['allowed_classes' => false]);

        if (!is_array($data) || !isset($data['expires'], $data['content'])) {
            return null; // Donnée corrompue
        }

        // Vérification de l'expiration
        if (time() > $data['expires']) {
            @unlink($path); // Suppression silencieuse du fichier périmé
            return null;
        }

        return $data['content'];
    }

    /**
     * Stocke une donnée de manière concurrente et sécurisée.
     */
    public function set(string $key, mixed $content, ?int $ttl = null): void
    {
        $safeKey = preg_replace('/[^a-zA-Z0-9_-]/', '', $key);
        $path = $this->cacheDir . DIRECTORY_SEPARATOR . $safeKey . '.cache';
        $ttl = $ttl ?? $this->defaultTtl;

        $data = [
            'expires' => time() + $ttl,
            'content' => $content
        ];

        // SÉCURITÉ 3 : LOCK_EX empêche la corruption si 2 requêtes écrivent en même temps
        file_put_contents($path, serialize($data), LOCK_EX);
    }

    /**
     * Vide tout le cache du dossier.
     */
    public function clear(): void
    {
        $files = glob($this->cacheDir . DIRECTORY_SEPARATOR . '*.cache');
        if ($files) {
            foreach ($files as $file) {
                if (is_file($file)) {
                    @unlink($file);
                }
            }
        }
    }
}