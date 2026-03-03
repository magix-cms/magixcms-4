<?php

declare(strict_types=1);

namespace App\Component\File;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Magepattern\Component\HTTP\Url;
use Magepattern\Component\Debug\Logger;
use App\Component\Routing\UrlTool;
use App\Component\Db\ConfigDb;

class UploadTool
{
    protected const WEBP_EXT = '.webp';

    protected UrlTool $urlTool;
    protected ImageManager $imageManager;
    protected Logger $logger;
    protected ConfigDb $imageConfig;

    private array $mimeTypes = [
        'png' => 'image/png', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'gif' => 'image/gif', 'webp' => 'image/webp',
        // Ajoutez ici d'autres mimes si nécessaire (pdf, doc, etc.)
    ];

    public function __construct()
    {
        if (!defined('DS')) {
            define('DS', DIRECTORY_SEPARATOR);
        }

        $this->urlTool = new UrlTool();
        $this->logger = Logger::getInstance();
        // Initialisation Intervention Image v3
        $this->imageManager = new ImageManager(new Driver());
        // C'est ici qu'on charge la classe qui contient la requête SQL globale
        $this->imageConfig = new ConfigDb();
    }

    /**
     * Méthode principale d'upload multiple
     */
    public function multipleImageUpload(string $module, string $attribute, string $root, array $directories = [], array $options = []): array
    {
        $results = [];
        $postKey = $options['postKey'] ?? 'img_multiple';

        // 1. Normalisation
        $files = $this->normalizeFiles($_FILES[$postKey] ?? []);

        if (empty($files)) {
            return [['status' => false, 'msg' => 'Aucun fichier reçu.']];
        }

        // 2. Setup des dossiers
        $relativePath = $root . '/' . implode('/', $directories);
        $targetDir = $this->urlTool->dirUpload($relativePath, true);

        // 3. Récupération dynamique des tailles pour CE module
        $resizeConfig = $this->imageConfig->fetchImageSizes($module, $attribute);

        $currentSuffix = (int)($options['suffix'] ?? 0);
        $baseName = $options['name'] ?? 'image';

        foreach ($files as $file) {
            if ($file['error'] !== UPLOAD_ERR_OK) continue;

            // Incrément suffixe
            if (!empty($options['suffix_increment'])) {
                $currentSuffix++;
            }

            // A. Extension et Noms
            $originalExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (empty($originalExt)) $originalExt = 'jpg';

            $filenameNoExt = $baseName . '_' . $currentSuffix; // ex: page-contact_25
            $finalFilename = $filenameNoExt . '.' . $originalExt; // ex: page-contact_25.jpg

            $targetFilePath = $targetDir . $finalFilename;

            try {
                // B. Sauvegarde physique du MASTER (Format Original)
                if (!move_uploaded_file($file['tmp_name'], $targetFilePath)) {
                    throw new \Exception("Erreur déplacement fichier.");
                }

                // C. Génération : Master WebP + Toutes les déclinaisons (JPG + WebP)
                $this->generateVariations($targetFilePath, $targetDir, $filenameNoExt, $originalExt, $resizeConfig);

                $results[] = [
                    'status' => true,
                    'file'   => $finalFilename,
                    'msg'    => 'Upload OK'
                ];

            } catch (\Throwable $e) {
                $this->logger->log($e, 'php', 'error', Logger::LOG_MONTH, Logger::LOG_LEVEL_ERROR);
                $results[] = ['status' => false, 'msg' => $e->getMessage()];
            }
        }

        return $results;
    }

    /**
     * Génère le WebP Maître ET toutes les déclinaisons configurées
     */
    protected function generateVariations(
        string $sourceFile,
        string $targetDir,
        string $filenameNoExt,
        string $originalExt,
        array $configs
    ): void {
        // 1. Lecture
        $image = $this->imageManager->read($sourceFile);

        // 2. Master WebP
        $image->toWebp(quality: 80)->save($targetDir . $filenameNoExt . self::WEBP_EXT);

        // 3. Déclinaisons
        if (!empty($configs)) {
            foreach ($configs as $conf) {
                // Clone propre
                $variant = clone $image;

                $prefix = $conf['prefix'];
                $width  = (int)$conf['width'];
                $height = (int)$conf['height'];

                if (isset($conf['type']) && $conf['type'] === 'crop') {
                    $variant->cover($width, $height);
                } else {
                    $variant->scale(width: $width, height: $height);
                }

                // Sauvegarde JPG/PNG
                $destName = $prefix . '_' . $filenameNoExt . '.' . $originalExt;
                $variant->save($targetDir . $destName, quality: 80);

                // Sauvegarde WebP
                $destNameWebp = $prefix . '_' . $filenameNoExt . self::WEBP_EXT;
                $variant->toWebp(quality: 80)->save($targetDir . $destNameWebp);

                // IMPORTANT : Libérer la mémoire de la variante immédiatement
                unset($variant);
            }
        }

        // IMPORTANT : Libérer l'image maître
        unset($image);
        // Force le nettoyage PHP (utile pour les boucles d'images lourdes)
        if (function_exists('gc_collect_cycles')) {
            gc_collect_cycles();
        }
    }

    private function normalizeFiles(array $files): array
    {
        $normalized = [];
        if (isset($files['name']) && is_array($files['name'])) {
            foreach ($files['name'] as $idx => $name) {
                if (empty($name)) continue;
                $normalized[] = [
                    'name'     => $name,
                    'type'     => $files['type'][$idx],
                    'tmp_name' => $files['tmp_name'][$idx],
                    'error'    => $files['error'][$idx],
                    'size'     => $files['size'][$idx]
                ];
            }
        }
        return $normalized;
    }
}