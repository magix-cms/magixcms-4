<?php

declare(strict_types=1);

namespace App\Component\Routing;

use Magepattern\Component\Database\QueryBuilder;
use App\Frontend\Db\BaseDb; // Ajustez selon la classe parente utilisée pour vos requêtes
use Magepattern\Component\HTTP\Request;

class RedirectTool extends BaseDb
{
    /**
     * Vérifie si l'URL courante possède une redirection active
     * et l'exécute immédiatement le cas échéant.
     */
    public function checkAndRedirect(): void
    {
        // 1. Récupération de l'URL courante tapée par le visiteur
        // $_SERVER['REQUEST_URI'] contient le chemin (ex: /fr/ancienne-page/?source=google)
        $currentUri = $_SERVER['REQUEST_URI'] ?? '/';

        // On nettoie l'URL pour ne garder que le chemin (on ignore les ?param=...)
        $path = parse_url($currentUri, PHP_URL_PATH);

        if (empty($path) || $path === '/') {
            return; // Pas de redirection sur la racine absolue pour éviter les boucles
        }

        // 2. Recherche dans la base de données
        $qb = new QueryBuilder();
        $qb->select(['new_url', 'type_redirect'])
            ->from('mc_seo_redirect')
            ->where('old_url = :url', ['url' => $path])
            ->where('active = 1');

        $redirect = $this->executeRow($qb);

        // 3. Exécution de la redirection si elle existe
        if ($redirect) {
            $type = (int)$redirect['type_redirect'];
            $newUrl = $redirect['new_url'];

            // Sécurité anti-boucle infinie (si old_url == new_url)
            if ($path === parse_url($newUrl, PHP_URL_PATH)) {
                return;
            }

            // Exécution propre des entêtes HTTP
            http_response_code($type);
            header("Location: " . $newUrl, true, $type);

            // On stoppe net l'exécution de PHP pour économiser les ressources du serveur
            exit;
        }
    }
}