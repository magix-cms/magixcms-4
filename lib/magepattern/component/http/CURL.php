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
use Magepattern\Component\Tool\StringTool;
use CurlHandle;
use Exception;
use Throwable;
use CURLFile;
use JsonException;

/**
 * $curl = new \Magepattern\Component\HTTP\CURL();
 *
 * // 1. Simple appel GET
 * $html = $curl->get('https://google.com');
 *
 * // 2. Télécharger un fichier
 * $success = $curl->download('https://site.com/image.jpg', '/var/www/uploads/image.jpg');
 *
 * // 3. Appel API JSON avec Auth
 * $response = $curl->addHeader('Authorization', 'Bearer TOKEN123')
 * ->post('https://api.site.com/v1/update', json_encode(['id' => 1]));
 *
 * $api = new CURL();
 * // Envoi automatique en JSON + Réception automatique en Array
 * $user = $api->requestJSON('POST', 'https://api.com/users', [
 * 'name' => 'John Doe',
 * 'role' => 'admin'
 * ]);
 *
 * echo $user['id']; // Accès direct au tableau !
 *
 * $api->setProxy('192.168.0.1', 8080, 'user:password');
 * $api->request('GET', 'https://google.com'); // Passe par le proxy
 */
class CURL
{
    private array $defaultOptions = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true, // Suivre les redirections 301/302
        CURLOPT_MAXREDIRS      => 5,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_USERAGENT      => 'Magepattern/2.1 HTTP Client'
    ];

    private array $headers = [];
    private ?array $proxyConfig = null;

    public function __construct()
    {
        if (!extension_loaded('curl')) {
            $e = new Exception('CURL extension not loaded');
            Logger::getInstance()->log($e, "php", "critical");
            throw $e;
        }
    }

    /**
     * Configure un proxy pour les prochaines requêtes.
     * @param string $host Adresse IP ou Domaine (ex: '192.168.1.10')
     * @param int $port Port (ex: 8080)
     * @param string|null $auth Format 'user:pass' ou null si pas d'auth
     */
    public function setProxy(string $host, int $port, ?string $auth = null): self
    {
        $this->proxyConfig = [
            CURLOPT_PROXY => $host,
            CURLOPT_PROXYPORT => $port,
        ];

        if ($auth) {
            $this->proxyConfig[CURLOPT_PROXYUSERPWD] = $auth;
        }

        return $this;
    }

    /**
     * Ajoute un header HTTP.
     */
    public function addHeader(string $key, string $value): self
    {
        $this->headers[$key] = "$key: $value";
        return $this;
    }

    /**
     * Vide les headers (utile entre deux requêtes).
     */
    public function resetHeaders(): self
    {
        $this->headers = [];
        return $this;
    }

    /**
     * Envoie une requête JSON complète (Request & Response).
     *
     * @param string $method GET, POST, PUT, DELETE
     * @param string $url
     * @param array $data Données à encoder en JSON
     * @param bool $decodeResponse Si true, retourne un array associatif au lieu d'une string
     * @return array|string|bool
     */
    public function requestJSON(string $method, string $url, array $data = [], bool $decodeResponse = true): array|string|bool
    {
        $this->addHeader('Content-Type', 'application/json');
        $this->addHeader('Accept', 'application/json');

        // Encodage automatique des données
        try {
            $jsonBody = empty($data) ? null : json_encode($data, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            Logger::getInstance()->log("JSON Encode Error: " . $e->getMessage(), "curl", "error");
            return false;
        }

        // Exécution de la requête brute
        $response = $this->request($method, $url, $jsonBody);

        if ($response === false) {
            return false;
        }

        // Décodage automatique de la réponse si demandé
        if ($decodeResponse) {
            try {
                return json_decode($response, true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException $e) {
                // Si la réponse n'est pas du JSON valide (ex: erreur HTML), on retourne la string brute
                return $response;
            }
        }

        return $response;
    }

    /**
     * Méthode centrale d'exécution avec CurlHandle (PHP 8).
     */
    public function request(string $method, string $url, array|string|null $body = null, array $options = []): string|bool
    {
        $ch = null;
        try {
            if (!StringTool::isURL($url)) {
                throw new Exception("Invalid URL format: $url");
            }

            // Initialisation PHP 8 (Retourne un objet CurlHandle)
            $ch = curl_init();

            // Sécurité de type
            if (!$ch instanceof CurlHandle) {
                throw new Exception("Failed to initialize CurlHandle");
            }

            // Configuration de base
            $config = $this->defaultOptions + $options;
            $config[CURLOPT_URL] = $url;
            $config[CURLOPT_CUSTOMREQUEST] = strtoupper($method);

            // Injection du Proxy si configuré
            if ($this->proxyConfig) {
                $config += $this->proxyConfig;
            }

            // Injection des Headers
            if (!empty($this->headers)) {
                $config[CURLOPT_HTTPHEADER] = array_values($this->headers);
            }

            // Injection du Body
            if ($body !== null) {
                $config[CURLOPT_POSTFIELDS] = $body;
            }

            curl_setopt_array($ch, $config);

            $response = curl_exec($ch);

            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $errno = curl_errno($ch);
            $error = curl_error($ch);

            // On ferme explicitement le handle, bien que PHP 8 le fasse au destruct
            curl_close($ch);
            $ch = null; // Pour le bloc finally si besoin

            if ($errno) {
                throw new Exception("CURL Error ($errno): $error");
            }

            if ($httpCode >= 400) {
                Logger::getInstance()->log("HTTP $httpCode on $url", "curl", "warning");
                return false;
            }

            return $response;

        } catch (Throwable $e) {
            Logger::getInstance()->log($e, "curl", "error");
            // Sécurité : fermeture si l'exception survient avant le close
            if ($ch instanceof CurlHandle) {
                curl_close($ch);
            }
            return false;
        }
    }

    /**
     * Télécharge un fichier (Streaming direct sur disque).
     */
    public function download(string $url, string $destinationPath): bool
    {
        $fp = null;
        $ch = null;
        try {
            $fp = fopen($destinationPath, 'wb');
            if (!$fp) throw new Exception("Cannot open file: $destinationPath");

            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_FILE => $fp,
                CURLOPT_HEADER => false,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_FAILONERROR => true
            ]);

            // Injection Proxy pour le download aussi
            if ($this->proxyConfig) {
                curl_setopt_array($ch, $this->proxyConfig);
            }

            $result = curl_exec($ch);
            curl_close($ch);
            fclose($fp);

            if (!$result) {
                @unlink($destinationPath);
                return false;
            }

            return true;
        } catch (Throwable $e) {
            Logger::getInstance()->log($e, "curl", "error");
            if (is_resource($fp)) fclose($fp);
            if ($ch instanceof CurlHandle) curl_close($ch);
            return false;
        }
    }
    /**
     * Envoie un fichier physique vers un serveur distant (Multipart/form-data).
     * * @param string $url
     * @param string $fieldName Le nom du champ attendu par le serveur (ex: 'file' ou 'image')
     * @param string $filePath Chemin local du fichier (ex: __DIR__ . '/photo.jpg')
     * @param array $extraFields Autres champs de formulaire à envoyer simultanément
     * @return string|bool
     */
    public function upload(string $url, string $fieldName, string $filePath, array $extraFields = []): string|bool
    {
        try {
            if (!file_exists($filePath)) {
                throw new \Exception("File to upload not found: $filePath");
            }

            // [Utilisation de CURLFile ici]
            // On crée l'objet qui indique à cURL de traiter ce chemin comme un fichier à envoyer
            $cfile = new \CURLFile($filePath);

            // On prépare le corps de la requête (Multipart)
            $postFields = array_merge([$fieldName => $cfile], $extraFields);

            // On utilise notre moteur 'request' existant
            return $this->request('POST', $url, $postFields);

        } catch (\Throwable $e) {
            Logger::getInstance()->log($e, "curl", "error");
            return false;
        }
    }
    /**
     * Envoie plusieurs fichiers dans une seule requête.
     * * @param string $url
     * @param array $files Tableau de fichiers [ 'champ_form' => 'chemin/physique' ]
     * @param array $extraFields Champs texte additionnels
     * @return string|bool
     *
     * // Exemple pour envoyer dans un tableau 'photos'
     * $filesToUpload = [
     * 'photos[0]' => '/path/to/img1.jpg',
     * 'photos[1]' => '/path/to/img2.jpg'
     * ];
     * $curl->uploadMultiple($url, $filesToUpload);
     */
    public function uploadMultiple(string $url, array $files, array $extraFields = []): string|bool
    {
        try {
            $postFields = $extraFields;

            foreach ($files as $name => $path) {
                if (!file_exists($path)) {
                    Logger::getInstance()->log("File not found for upload: $path", "curl", "warning");
                    continue;
                }
                // On crée un CURLFile pour chaque chemin
                $postFields[$name] = new \CURLFile($path);
            }

            if (empty($postFields)) {
                throw new \Exception("No valid files to upload.");
            }

            // Notre moteur request() détecte que c'est un tableau et envoie en multipart/form-data
            return $this->request('POST', $url, $postFields);

        } catch (\Throwable $e) {
            Logger::getInstance()->log($e, "curl", "error");
            return false;
        }
    }
    /**
     * @param string $url
     * @param string $fieldName
     * @param string $filePath
     * @param callable|null $onProgress Fonction de rappel : function($resource, $downloadSize, $downloaded, $uploadSize, $uploaded)
     * @return string|bool
     *
     * $curl = new \Magepattern\Component\HTTP\CURL();
     *
     * $curl->uploadWithProgress(
     * 'https://api.remote-storage.com/upload',
     * 'image',
     * '/path/to/very-big-file.zip',
     * function($resource, $dltotal, $dlnow, $ultotal, $ulnow) {
     * if ($ultotal > 0) {
     * $percent = round(($ulnow / $ultotal) * 100);
     * // On peut loguer la progression ou l'écrire dans un fichier de statut pour JS
     * echo "Progression de l'upload : $percent% \r";
     * }
     * }
     * );
     */
    public function uploadWithProgress(string $url, string $fieldName, string $filePath, ?callable $onProgress = null): string|bool
    {
        $options = [
            // Activer la progression
            CURLOPT_NOPROGRESS => false,
        ];

        if ($onProgress) {
            $options[CURLOPT_PROGRESSFUNCTION] = $onProgress;
        }

        return $this->upload($url, $fieldName, $filePath, [], $options);
    }
}