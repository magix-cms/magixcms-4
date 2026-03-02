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

namespace Magepattern\Component\Security;

class PasswordTool
{
    /**
     * Crée un hash sécurisé (BCrypt par défaut).
     */
    /**
     * @param string $password
     * @return string
     */
    public static function hash(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Vérifie si le mot de passe correspond au hash stocké.
     */
    /**
     * @param string $password
     * @param string $hash
     * @return bool
     */
    public static function verify(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Vérifie si le hash doit être mis à jour (ex: changement d'algorithme serveur).
     */
    /**
     * @param string $hash
     * @return bool
     */
    public static function needsRehash(string $hash): bool
    {
        return password_needs_rehash($hash, PASSWORD_DEFAULT);
    }

    /**
     * Génère un mot de passe aléatoire hautement sécurisé.
     * * @param int $length Longueur souhaitée (défaut: 16)
     */
    /**
     * @param int $length
     * @return string
     * @throws \Random\RandomException
     */
    public static function generateRandom(int $length = 16): string
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-_=+';
        $max = strlen($chars) - 1;
        $password = '';

        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[random_int(0, $max)];
        }

        return $password;
    }

    /**
     * Génère un jeton (token) hexadécimal unique pour les liens de réinitialisation.
     * * @param int $length Longueur de la chaîne retournée (doit être paire, défaut: 64)
     */
    /**
     * @param int $length
     * @return string
     * @throws \Random\RandomException
     */
    public static function generateResetToken(int $length = 64): string
    {
        return bin2hex(random_bytes(intdiv($length, 2)));
    }

    /**
     * Valide la robustesse d'un mot de passe selon des critères stricts.
     * (Au moins: 1 majuscule, 1 minuscule, 1 chiffre, 1 caractère spécial).
     * * @param string $password Le mot de passe à tester
     * @param int $minLength Longueur minimale (défaut: 8)
     */
    public static function checkStrength(string $password, int $minLength = 8): bool
    {
        if (strlen($password) < $minLength) {
            return false;
        }

        // Vérifie la présence d'au moins une majuscule, minuscule, chiffre et caractère spécial
        if (!preg_match('/[A-Z]/', $password)) return false;
        if (!preg_match('/[a-z]/', $password)) return false;
        if (!preg_match('/[0-9]/', $password)) return false;
        if (!preg_match('/[^a-zA-Z0-9]/', $password)) return false;

        return true;
    }
}