<?php
/**
 * Pont PHP pour le plugin TinyMCE "bsicons"
 * Parse le CSS officiel pour extraire toutes les icônes de manière dynamique.
 */
header('Content-Type: application/json; charset=utf-8');

// Fichier de cache local (créé dans le même dossier que ce script)
$cacheFile = __DIR__ . DIRECTORY_SEPARATOR . 'icons_cache.json';
$cacheTime = 86400 * 30; // 30 jours de validité du cache

// 1. Si le cache est valide, on le sert immédiatement (Ultra-rapide)
if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTime) {
    echo file_get_contents($cacheFile);
    exit;
}

// 2. Sinon, on télécharge le CSS officiel (Garantit d'avoir les ~2000+ icônes à jour)
$cssUrl = 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css';

// Pour contourner certaines restrictions d'hébergeurs sur file_get_contents, on utilise le contexte web
$context = stream_context_create(['http' => ['header' => 'User-Agent: PHP-Bridge/1.0']]);
$cssContent = @file_get_contents($cssUrl, false, $context);

$icons = [];

if ($cssContent) {
    // 3. REGEX STRICTE : Cherche uniquement ".bi-nom-icone::before"
    if (preg_match_all('/\.bi-([\w\-]+)::before/', $cssContent, $matches)) {
        $iconNames = array_unique($matches[1]);
        sort($iconNames); // Tri alphabétique

        // Formatage pour le JavaScript
        foreach ($iconNames as $name) {
            $icons[] = [
                'name' => str_replace('-', ' ', $name), // "check-circle" devient "check circle" pour la recherche
                'icon' => 'bi-' . $name
            ];
        }
    }
}

$jsonOutput = json_encode($icons);

// 4. Sauvegarde du cache si l'extraction a fonctionné
if (!empty($icons)) {
    @file_put_contents($cacheFile, $jsonOutput);
}

echo $jsonOutput;