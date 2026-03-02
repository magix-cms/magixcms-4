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
use Magepattern\Component\Security\RSATool;
use RuntimeException;
use Throwable;

class Session
{
    // Clés de configuration (identifiants dans $_SESSION)
    protected string $ipKey;
    protected string $uaKey;
    protected string $csrfKey;

    protected bool $ssl;
    protected string $sessionName;

    /**
     * Session constructor.
     * * @param bool $ssl Force le mode HTTPS (Secure cookie)
     * @param string $name Nom du cookie de session (ex: MP_SESSID)
     * @param array $config Clés personnalisées [ip_key, ua_key, csrf_key]
     */
    public function __construct(
        bool $ssl = true,
        string $name = 'mp_sess_id',
        array $config = []
    ) {
        $this->ssl = $ssl;
        $this->sessionName = $name;

        // Injection des clés personnalisées ou valeurs par défaut
        $this->ipKey   = $config['ip_key']   ?? 'mp_client_ip';
        $this->uaKey   = $config['ua_key']   ?? 'mp_client_ua';
        $this->csrfKey = $config['csrf_key'] ?? 'mp_csrf_token';
    }

    /**
     * Démarre la session avec des paramètres de sécurité modernes.
     * * @param int $lifetime 0 pour session de navigateur, ou durée en secondes
     * @return bool
     */
    public function start(int $lifetime = 0): bool
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return true;
        }

        try {
            $options = [
                'name'                   => $this->sessionName,
                'cookie_lifetime'        => $lifetime,
                'cookie_path'            => '/',
                'cookie_domain'          => '',
                'cookie_secure'          => $this->ssl,
                'cookie_httponly'        => true,
                'cookie_samesite'        => 'Lax',
                'use_strict_mode'        => 1,
                'use_only_cookies'       => 1

                // 👇 COMMENTÉ OU SUPPRIMÉ POUR COMPATIBILITÉ PHP 8.4+ 👇
                // 'sid_length'             => 48,
                // 'sid_bits_per_character' => 6,
            ];

            if (!session_start($options)) {
                throw new RuntimeException("Impossible de démarrer la session.");
            }

            // Validation de l'empreinte (Anti-Hijacking)
            if (!$this->validateFingerprint()) {
                $this->destroy();
                session_start($options);
                $this->regenerate();
            }

            return true;

        } catch (Throwable $e) {
            Logger::getInstance()->log($e, "session", "critical");
            return false;
        }
    }

    /**
     * Régénère l'ID (à utiliser après un changement de privilèges / login).
     */
    public function regenerate(bool $deleteOldSession = true): string
    {
        if (session_status() !== PHP_SESSION_ACTIVE) return '';

        session_regenerate_id($deleteOldSession);
        $this->setFingerprint();

        return session_id();
    }

    /**
     * Détruit la session proprement côté serveur et navigateur.
     */
    public function destroy(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION = [];
            if (ini_get("session.use_cookies")) {
                $p = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000, $p["path"], $p["domain"], $p["secure"], $p["httponly"]);
            }
            session_destroy();
        }
    }

    /**
     * Gestion du Token CSRF (Cross-Site Request Forgery).
     */
    public function getToken(bool $forceRegenerate = false): string
    {
        $this->ensureStarted();

        if ($forceRegenerate || empty($_SESSION[$this->csrfKey])) {
            $token = class_exists(RSATool::class) ? RSATool::tokenID(32) : bin2hex(random_bytes(32));
            $_SESSION[$this->csrfKey] = $token;
        }

        return $_SESSION[$this->csrfKey];
    }

    /**
     * Vérification sécurisée du Token (Timing-attack safe).
     */
    public function validateToken(?string $token): bool
    {
        $this->ensureStarted();
        $stored = $_SESSION[$this->csrfKey] ?? '';
        return is_string($token) && hash_equals($stored, $token);
    }

    /**
     * Accesseurs de données.
     */
    public function set(string $key, mixed $value): void
    {
        $this->ensureStarted();
        $_SESSION[$key] = $value;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $this->ensureStarted();
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Fingerprinting : IP + User Agent.
     */
    private function setFingerprint(): void
    {
        $_SESSION[$this->ipKey] = IPMatcher::getVisitorIp();
        $_SESSION[$this->uaKey] = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    }

    private function validateFingerprint(): bool
    {
        if (!isset($_SESSION[$this->ipKey])) {
            $this->setFingerprint();
            return true;
        }

        return ($_SESSION[$this->ipKey] === IPMatcher::getVisitorIp() &&
            $_SESSION[$this->uaKey] === ($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'));
    }

    private function ensureStarted(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            $this->start();
        }
    }
}