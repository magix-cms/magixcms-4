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
    protected string $ipKey;
    protected string $uaKey;
    protected string $csrfKey;

    // NOUVEAU : Propriétés pour le timeout
    protected int $idleTimeout;
    protected int $absoluteTimeout;

    protected bool $ssl;
    protected string $sessionName;

    /**
     * @param bool $ssl
     * @param string $name
     * @param array $config
     */
    public function __construct(
        bool $ssl = true,
        string $name = 'mp_sess_id',
        array $config = []
    ) {
        $this->ssl = $ssl;
        $this->sessionName = $name;

        $this->ipKey   = $config['ip_key']   ?? 'mp_client_ip';
        $this->uaKey   = $config['ua_key']   ?? 'mp_client_ua';
        $this->csrfKey = $config['csrf_key'] ?? 'mp_csrf_token';

        // CONFIGURATION : 1 heure d'inactivité / 12 heures maximum absolu
        $this->idleTimeout     = $config['idle_timeout']     ?? 3600;
        $this->absoluteTimeout = $config['absolute_timeout'] ?? 43200;
    }

    /**
     * @param int $lifetime
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
            ];

            if (!session_start($options)) {
                throw new RuntimeException("Impossible de démarrer la session.");
            }

            // 🛡️ MODIFICATION : On vérifie l'empreinte ET le temps écoulé
            if (!$this->validateFingerprint() || !$this->checkTimeouts()) {
                $this->destroy();
                session_start($options);
                $this->regenerate();
            }

            // Mise à jour de la dernière activité à chaque requête
            $_SESSION['last_activity'] = time();

            return true;

        } catch (Throwable $e) {
            Logger::getInstance()->log($e, "session", "critical");
            return false;
        }
    }

    /**
     * Méthode de vérification des timeouts
     */
    /**
     * @return bool
     */
    private function checkTimeouts(): bool
    {
        $now = time();

        // Initialisation à la création
        if (!isset($_SESSION['created_at'])) {
            $_SESSION['created_at'] = $now;
        }
        if (!isset($_SESSION['last_activity'])) {
            $_SESSION['last_activity'] = $now;
        }

        // Expiration absolue (ex: après 12h de connexion, peu importe l'activité)
        if (($now - $_SESSION['created_at']) > $this->absoluteTimeout) {
            return false;
        }

        // Expiration d'inactivité (ex: parti manger et resté absent plus d'1h)
        if (($now - $_SESSION['last_activity']) > $this->idleTimeout) {
            return false;
        }

        return true;
    }

    /**
     * Régénère l'ID (à utiliser après un changement de privilèges / login).
     */
    /**
     * @param bool $deleteOldSession
     * @return string
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
    /**
     * @return void
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
    /**
     * @param bool $forceRegenerate
     * @return string
     * @throws \Random\RandomException
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
    /**
     * @param string|null $token
     * @return bool
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
    /**
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function set(string $key, mixed $value): void
    {
        $this->ensureStarted();
        $_SESSION[$key] = $value;
    }

    /**
     * @param string $key
     * @param mixed|null $default
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $this->ensureStarted();
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Fingerprinting : IP + User Agent.
     */
    /**
     * @return void
     */
    private function setFingerprint(): void
    {
        $_SESSION[$this->ipKey] = IPMatcher::getVisitorIp();
        $_SESSION[$this->uaKey] = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    }

    /**
     * @return bool
     */
    private function validateFingerprint(): bool
    {
        if (!isset($_SESSION[$this->ipKey])) {
            $this->setFingerprint();
            return true;
        }

        //  TOLÉRANCE POUR LE DÉVELOPPEMENT LOCAL (Simulateur Mobile Chrome)
        // On vérifie si le serveur est en local (localhost, .test, .local, ou IP interne)
        $isLocalhost = in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1', '::1']) ||
            str_ends_with($_SERVER['HTTP_HOST'], '.test') ||
            str_ends_with($_SERVER['HTTP_HOST'], '.local');

        if ($isLocalhost) {
            // En local, on ne vérifie que l'IP, on pardonne les changements d'User-Agent du simulateur
            return ($_SESSION[$this->ipKey] === IPMatcher::getVisitorIp());
        }

        //  EN PRODUCTION : Sécurité maximale (IP + User-Agent)
        return ($_SESSION[$this->ipKey] === IPMatcher::getVisitorIp() &&
            $_SESSION[$this->uaKey] === ($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'));
    }

    /**
     * @return void
     */
    private function ensureStarted(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            $this->start();
        }
    }
}