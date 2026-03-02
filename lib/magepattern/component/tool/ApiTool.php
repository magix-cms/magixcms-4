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

use Fiber;
use CurlHandle;
use CurlMultiHandle;
use Magepattern\Component\Debug\Logger;

class ApiTool
{
    private const USER_AGENT = 'Magepattern/3.0 (FiberEngine)';

    /** @var CurlMultiHandle|null Le moteur global pour les Fibers */
    private static ?CurlMultiHandle $mh = null;

    /** @var array<int, Fiber> Mapping entre ID de requête et Fiber en attente */
    private static array $suspendedFibers = [];

    /**
     * [Mode Synchrone] Requête classique bloquante
     */
    /**
     * @param string $method
     * @param string $url
     * @param array $options
     * @return array
     */
    public static function request(string $method, string $url, array $options = []): array
    {
        // ... (Code identique à la version précédente pour le mode simple) ...
        // Je le garde court ici pour se concentrer sur la partie Fiber
        $ch = curl_init();
        self::configureCurl($ch, $method, $url, $options);
        $response = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        return self::formatResponse($response, $info);
    }

    /**
     * [Mode Asynchrone] Démarre une requête dans une Fiber et la met en PAUSE
     * @return mixed Retourne le résultat une fois la Fiber réveillée par run()
     */
    /**
     * @param string $method
     * @param string $url
     * @param array $options
     * @return mixed
     * @throws \Throwable
     */
    public static function asyncRequest(string $method, string $url, array $options = []): mixed
    {
        // On initialise le multi-handle si ce n'est pas fait
        if (self::$mh === null) {
            self::$mh = curl_multi_init();
        }

        // On crée la Fiber qui va gérer cette requête spécifique
        $fiber = new Fiber(function () use ($method, $url, $options) {
            $ch = curl_init();
            self::configureCurl($ch, $method, $url, $options);

            // On ajoute la requête au moteur global
            curl_multi_add_handle(self::$mh, $ch);

            // C'EST ICI LA MAGIE :
            // On suspend l'exécution de CETTE fonction.
            // On passe l'ID du handle à l'ordonnanceur pour qu'il sache qui réveiller.
            $handleId = (int)$ch;
            Fiber::suspend($handleId);

            // --- L'exécution reprendra ICI quand la réponse sera arrivée ---

            $content = curl_multi_getcontent($ch);
            $info = curl_getinfo($ch);
            curl_multi_remove_handle(self::$mh, $ch);
            curl_close($ch);

            return self::formatResponse($content, $info);
        });

        // Démarrage de la Fiber jusqu'à sa suspension
        $handleId = $fiber->start();

        // On enregistre la Fiber pour pouvoir la réveiller plus tard
        self::$suspendedFibers[$handleId] = $fiber;

        // On retourne null car le vrai résultat arrivera à la fin du run()
        return null;
    }

    /**
     * [L'Ordonnanceur] Le cœur du système "Event Loop"
     * Cette méthode fait tourner le moteur tant qu'il y a des Fibers en attente
     */
    /**
     * @return void
     * @throws \Throwable
     */
    public static function run(): void
    {
        if (self::$mh === null || empty(self::$suspendedFibers)) {
            return;
        }

        TimerTool::getInstance('api_fiber')->start();
        $active = null;

        // Boucle tant qu'il y a de l'activité réseau ou des fibers en pause
        do {
            // On laisse Curl travailler
            curl_multi_exec(self::$mh, $active);
            curl_multi_select(self::$mh, 0.01); // Pause minuscule pour ne pas brûler le CPU

            // On vérifie si des requêtes sont terminées
            while ($info = curl_multi_info_read(self::$mh)) {
                if ($info['msg'] === CURLMSG_DONE) {
                    $ch = $info['handle'];
                    $handleId = (int)$ch;

                    // Si une Fiber attendait ce résultat, on la RÉVEILLE !
                    if (isset(self::$suspendedFibers[$handleId])) {
                        $fiber = self::$suspendedFibers[$handleId];
                        unset(self::$suspendedFibers[$handleId]);

                        // La Fiber reprend là où elle s'était arrêtée (au suspend)
                        // Note: Le résultat final de la fiber est stocké dans son return
                        if ($fiber->isSuspended()) {
                            $fiber->resume();
                        }
                    }
                }
            }
        } while ($active > 0 || !empty(self::$suspendedFibers));

        curl_multi_close(self::$mh);
        self::$mh = null; // Reset pour la prochaine fois

        TimerTool::getInstance('api_fiber')->stop();
    }

    /**
     * @param CurlHandle $ch
     * @param string $method
     * @param string $url
     * @param array $options
     * @return void
     */
    private static function configureCurl(CurlHandle $ch, string $method, string $url, array $options): void
    {
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_USERAGENT, self::USER_AGENT);
        // ... gestion headers/body ...
    }

    /**
     * @param string|bool $response
     * @param array $info
     * @return array
     */
    private static function formatResponse(string|bool $response, array $info): array
    {
        return [
            'success' => $info['http_code'] >= 200 && $info['http_code'] < 300,
            'status'  => $info['http_code'],
            'data'    => json_decode((string)$response, true) ?? $response
        ];
    }
    /**
     * Exemples :
     * ConsoleTool::register('test:fiber', function() {
     * ConsoleTool::info("Démarrage du moteur Fiber...");
     *
     * $usersToCheck = ['user1', 'user2', 'user3'];
     * $fibers = [];
     *
     * // 1. DÉMARRAGE (Non-bloquant)
     * foreach ($usersToCheck as $user) {
     * // On crée une Fiber pour chaque utilisateur
     * // Note: Le code à l'intérieur ne s'exécutera à 100% que lors du run()
     * $fibers[$user] = new Fiber(function() use ($user) {
     * ConsoleTool::line("[$user] Début requête...");
     *
     * // Appel asynchrone : La fiber se met en pause ICI
     * $result = ApiTool::asyncRequest('GET', "https://api.example.com/users/$user");
     *
     * // Reprise quand la réponse est là
     * ConsoleTool::success("[$user] Terminé ! Status: " . $result['status']);
     * return $result;
     * });
     *
     * // On démarre la fiber (elle va s'exécuter jusqu'au suspend)
     * $fibers[$user]->start();
     * }
     *
     * ConsoleTool::info("Toutes les requêtes sont lancées. Attente des réponses...");
     *
     * // 2. EXÉCUTION (L'Event Loop)
     * // C'est ici que tout se joue : le script attend que toutes les fibers finissent
     * ApiTool::run();
     *
     * // 3. RÉSULTATS
     * foreach ($fibers as $user => $fiber) {
     * // On récupère la valeur de retour (le return de la fonction anonyme)
     * $data = $fiber->getReturn();
     * // Traitement final...
     * }
     *
     * }, "Test de l'implémentation Fiber pour API");
     */
}