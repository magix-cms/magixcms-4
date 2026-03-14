<?php

declare(strict_types=1);

namespace App\Component\Routing;

use Magepattern\Component\HTTP\Request;
use Magepattern\Component\HTTP\Url;
use Magepattern\Component\Tool\DateTool;

class UrlTool
{
    protected bool $amp;

    public function __construct()
    {
        if (!defined('DS')) {
            define('DS', DIRECTORY_SEPARATOR);
        }

        // On vérifie si on est en mode AMP
        $this->amp = Request::isGet('amp');
    }

    /**
     * Construit les URLs publiques selon le type de contenu
     */
    /**
     * Construit les URLs publiques selon le type de contenu
     */
    public function buildUrl(array $data): string
    {
        if (empty($data) || empty($data['type'])) {
            return '';
        }

        $iso = $data['iso'] ?? 'fr';
        $type = $data['type'];
        $ampPath = $this->amp ? '/amp' : '';
        $id = $data['id'] ?? '';

        $slug = !empty($data['url']) ? Url::clean((string)$data['url']) : '';

        // Création d'une base commune (ex: /fr/amp ou /fr)
        $basePath = "/{$iso}{$ampPath}";

        // Formatage de la date (News / Archives)
        $formattedDate = '';
        if (!empty($data['date'])) {
            $sqlDate = DateTool::toSql((string)$data['date']);
            if ($sqlDate) {
                // CORRECTIF : On remplace les tirets par des slashs (2026-03-12 -> 2026/03/12)
                $formattedDate = str_replace('-', '/', substr($sqlDate, 0, 10));
            }
        }

        return match ($type) {
            // 🟢 AJOUT : La racine du catalogue
            'catalog'        => "{$basePath}/catalog/",

            'pages', 'about' => "{$basePath}/{$type}/{$id}-{$slug}/",

            'category'       => "{$basePath}/catalog/{$id}-{$slug}/",

            'product'        => isset($data['id_category'], $data['url_category'])
                ? "{$basePath}/catalog/{$data['id_category']}-{$data['url_category']}/{$id}-{$slug}/"
                : "{$basePath}/catalog/{$id}-{$slug}/", // Fallback au cas où le parent manque

            // 🟢 CORRECTION : Sécurisation si c'est la racine des news (pas d'ID)
            'news'           => empty($id)
                ? "{$basePath}/news/"
                : (!empty($formattedDate)
                    ? "{$basePath}/news/{$formattedDate}/{$id}-{$slug}/"
                    : "{$basePath}/news/{$id}-{$slug}/"),

            'date'           => "{$basePath}/news/" . ($data['year'] ?? '') . '/' . (!empty($data['month']) ? sprintf('%02d', $data['month']) . '/' : ''),

            'tag'            => "{$basePath}/news/tag/{$id}-{$slug}/",

            default          => ''
        };
    }

    /**
     * Retourne le chemin absolu depuis la racine du serveur (DOCUMENT_ROOT)
     */
    public function basePath(string $pathUpload): string
    {
        return rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/' . ltrim($pathUpload, '/');
    }

    /**
     * Retourne le chemin d'upload et crée le dossier s'il n'existe pas
     */
    public function dirUpload(string $path, bool $basePath = true): string
    {
        $path = rtrim($path, DS) . DS;
        $fullPath = $this->basePath($path);

        if (!is_dir($fullPath)) {
            mkdir($fullPath, 0755, true);
        }

        return $basePath ? $fullPath : $path;
    }

    /**
     * Retourne une collection de chemins d'upload et crée les dossiers s'ils n'existent pas
     */
    public function dirUploadCollection(string $root, array $directories = [], bool $basePath = true): array
    {
        $urls = [];
        $root = rtrim($root, DS) . DS;

        if (!empty($directories)) {
            foreach ($directories as $dir) {
                $urls[] = $this->dirUpload($root . $dir, $basePath);
            }
        }

        return $urls;
    }
}