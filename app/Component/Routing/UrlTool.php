<?php

declare(strict_types=1);

namespace App\Component\Routing;

use Magepattern\Component\HTTP\Request;
use Magepattern\Component\HTTP\Url;

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
    public function getBuildUrl(array $data): string
    {
        if (empty($data)) return '';

        $iso = $data['iso'] ?? 'fr';
        $type = $data['type'] ?? '';
        $ampPath = $this->amp ? '/amp' : '';
        $id = $data['id'] ?? '';
        $slug = isset($data['url']) ? Url::clean($data['url']) : '';

        // Formatage natif de la date pour les URLs (ex: 2026-03-02)
        $formattedDate = '';
        if (!empty($data['date'])) {
            try {
                $dateObj = new \DateTime($data['date']);
                $formattedDate = $dateObj->format('Y-m-d');
            } catch (\Exception $e) {
                // Si la date est invalide, on laisse vide pour ne pas faire planter l'URL
                $formattedDate = '';
            }
        }

        return match ($type) {
            'pages', 'about' => '/' . $iso . $ampPath . '/' . $type . '/' . $id . '-' . $slug . '/',
            'category'       => '/' . $iso . $ampPath . '/catalog/' . $id . '-' . $slug . '/',
            'product'        => isset($data['id_parent'], $data['url_parent'])
                ? '/' . $iso . $ampPath . '/catalog/' . $data['id_parent'] . '-' . $data['url_parent'] . '/' . $id . '-' . $slug . '/'
                : '',
            'news'           => '/' . $iso . $ampPath . '/news/' . $formattedDate . '/' . $id . '-' . $slug . '/',
            'date'           => '/' . $iso . $ampPath . '/news/' . ($data['year'] ?? '') . '/' . (isset($data['month']) ? sprintf('%02d', $data['month']) . '/' : ''),
            'tag'            => '/' . $iso . $ampPath . '/news/tag/' . $id . '-' . $slug . '/',
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