<?php

declare(strict_types=1);

namespace App\Component\File;

use App\Component\Routing\UrlTool;
use App\Component\Db\ConfigDb;

class ImageTool
{
    /**
     * @var array Stocke la configuration chargée pour éviter les requêtes SQL multiples
     */
    protected array $imgConfig = [];

    protected ConfigDb $configDb;
    protected UrlTool $urlTool;

    public function __construct()
    {
        $this->configDb = new ConfigDb();
        $this->urlTool = new UrlTool();
    }

    /**
     * Récupère la configuration des tailles d'images (cache local à la classe)
     */
    public function getConfigItems(string $module, string $attribute): array
    {
        if (!isset($this->imgConfig[$module][$attribute])) {
            // Récupère les tailles depuis la BDD (table mc_config_img)
            $imgConf = $this->configDb->fetchImageSizes($module, $attribute);

            if (empty($imgConf)) {
                return [];
            }
            $this->imgConfig[$module][$attribute] = $imgConf;
        }
        return $this->imgConfig[$module][$attribute];
    }

    /**
     * Formate le tableau d'images pour Smarty (Admin & Frontend)
     * Transforme les préfixes BDD ('s', 'l') en clés lisibles ('small', 'large')
     */
    public function setModuleImages(string $module, string $attribute, array $images, int $id = 0, string $customBaseDir = ''): array
    {
        if (empty($images)) {
            return [];
        }

        $configs = $this->getConfigItems($module, $attribute);

        // Si on a fourni un chemin custom, on l'utilise. Sinon, comportement par défaut.
        if (!empty($customBaseDir)) {
            $baseDir = $customBaseDir;
        } else {
            $baseDir = '/upload/' . $module . '/' . $id . '/';
        }

        foreach ($images as $key => $image) {
            $filename = $image['name_img'];
            $filenameNoExt = pathinfo($filename, PATHINFO_FILENAME);
            $ext = pathinfo($filename, PATHINFO_EXTENSION);

            // Initialisation du tableau d'image formaté
            $images[$key]['img'] = [];

            // A. Image Originale (toujours disponible)
            $images[$key]['img']['original'] = [
                'src' => $baseDir . $filename
            ];

            // B. Génération des formats selon la configuration BDD
            foreach ($configs as $conf) {
                $prefix = $conf['prefix']; // ex: 's', 'm', 'l'

                // On mappe le préfixe vers un nom lisible pour Smarty
                $keyName = match ($prefix) {
                    's' => 'small',
                    'm' => 'medium',
                    'l' => 'large',
                    default => $prefix // Si c'est 'z' ou autre, on garde la lettre
                };

                // Construction du nom de fichier : "s_mon-image.jpg"
                /*$generatedName = $prefix . '_' . $filename;

                // Assignation
                $images[$key]['img'][$keyName] = [
                    'src'    => $baseDir . $generatedName,
                    'width'  => $conf['width'],
                    'height' => $conf['height'],
                    // Si tu utilises WebP, le chemin serait :
                    'webp'   => $baseDir . $prefix . '_' . $filenameNoExt . '.webp'
                ];*/
                // Construction du nom de fichier : "s_mon-image.jpg"
                $generatedName = $prefix . '_' . $filename;

                // Assignation avec ALIAS pour compatibilité Front/Back
                $images[$key]['img'][$keyName] = [
                    'src'      => $baseDir . $generatedName,

                    // --- CLÉS POUR L'ADMINISTRATION ---
                    'width'    => $conf['width'],
                    'height'   => $conf['height'],
                    'webp'     => $baseDir . $prefix . '_' . $filenameNoExt . '.webp',

                    // --- CLÉS POUR LE FRONTEND (Template CMS 3) ---
                    'w'        => $conf['width'],
                    'h'        => $conf['height'],
                    'src_webp' => $baseDir . $prefix . '_' . $filenameNoExt . '.webp',
                    'ext'      => 'image/' . ($ext === 'jpg' ? 'jpeg' : $ext)
                ];
            }

            // C. Définition de l'image "Adaptive" (Zoom / Lightbox)
            // On essaie de prendre 'large', sinon 'medium', sinon l'original
            if (isset($images[$key]['img']['large'])) {
                $images[$key]['img']['adaptive'] = $images[$key]['img']['large'];
            } elseif (isset($images[$key]['img']['medium'])) {
                $images[$key]['img']['adaptive'] = $images[$key]['img']['medium'];
            } else {
                $images[$key]['img']['adaptive'] = $images[$key]['img']['original'];
            }

            // D. Fallback pour 'small' (Miniature)
            // Si pas de config 'small' (prefix 's'), on utilise l'original pour ne pas casser l'affichage
            if (!isset($images[$key]['img']['small'])) {
                $images[$key]['img']['small'] = $images[$key]['img']['original'];
            }
            uasort($images[$key]['img'], function ($a, $b) {
                $wA = $a['w'] ?? 0;
                $wB = $b['w'] ?? 0;
                return $wB <=> $wA; // Tri décroissant
            });
        }

        return $images;
    }
}