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

    //  SÉCURITÉ 1 : Liste blanche stricte des extensions ET Mimes autorisés
    private array $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    private array $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

    public function __construct()
    {
        if (!defined('DS')) {
            define('DS', DIRECTORY_SEPARATOR);
        }

        $this->urlTool = new UrlTool();
        $this->logger = Logger::getInstance();
        $this->imageManager = new ImageManager(new Driver());
        $this->imageConfig = new ConfigDb();
    }

    /**
     * Méthode de validation centralisée (Avant écriture)
     */
    private function validateImageSecurity(array $file): bool|string
    {
        // 1. Vérification de l'extension
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $this->allowedExtensions, true)) {
            return "Extension non autorisée. Seules les images (jpg, png, webp, gif) sont acceptées.";
        }

        // 2. Vérification du vrai Type MIME du fichier temporaire (Anti-usurpation d'extension)
        if (!file_exists($file['tmp_name'])) {
            return "Fichier temporaire introuvable.";
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);

        if (!in_array($mime, $this->allowedMimes, true)) {
            return "Le contenu du fichier est invalide. Il ne correspond pas à une image sécurisée.";
        }

        // 3. Prévention des attaques par double extension (ex: shell.php.jpg)
        // On s'assure qu'il n'y a pas d'extension '.php' cachée dans le nom
        if (preg_match('/\.php/i', $file['name'])) {
            return "Nom de fichier suspect détecté.";
        }

        return true; // Le fichier est sain
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

            // VÉRIFICATION DE SÉCURITÉ AVANT TOUT DÉPLACEMENT
            $securityCheck = $this->validateImageSecurity($file);
            if ($securityCheck !== true) {
                $results[] = ['status' => false, 'msg' => $securityCheck];
                continue; // On passe au fichier suivant
            }

            // Incrément suffixe
            if (!empty($options['suffix_increment'])) {
                $currentSuffix++;
            }

            // A. Extension et Noms
            $originalExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $filenameNoExt = $baseName . '_' . $currentSuffix;
            $finalFilename = $filenameNoExt . '.' . $originalExt;
            $targetFilePath = $targetDir . $finalFilename;

            try {
                // B. Sauvegarde physique du MASTER
                if (!move_uploaded_file($file['tmp_name'], $targetFilePath)) {
                    throw new \Exception("Erreur de déplacement du fichier.");
                }

                // C. Génération : Master WebP + Toutes les déclinaisons (JPG + WebP)
                $this->generateVariations($targetFilePath, $targetDir, $filenameNoExt, $originalExt, $resizeConfig);

                $results[] = [
                    'status' => true,
                    'file'   => $finalFilename,
                    'msg'    => 'Upload OK'
                ];

            } catch (\Throwable $e) {
                // Rollback (Nettoyage immédiat de la menace)
                if (file_exists($targetFilePath)) {
                    unlink($targetFilePath);
                }

                $this->logger->log($e, 'php', 'error', Logger::LOG_MONTH, Logger::LOG_LEVEL_ERROR);
                $results[] = ['status' => false, 'msg' => "Fichier corrompu ou illisible. Il a été rejeté."];
            }
        }

        return $results;
    }

    /**
     * Méthode pour l'upload d'un fichier image UNIQUE
     */
    public function singleImageUpload(string $module, string $attribute, string $root, array $directories = [], array $options = []): array
    {
        $postKey = $options['postKey'] ?? 'img_single';
        $file = $_FILES[$postKey] ?? null;

        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            return ['status' => false, 'msg' => 'Aucun fichier valide reçu.'];
        }

        // VÉRIFICATION DE SÉCURITÉ AVANT TOUT DÉPLACEMENT
        $securityCheck = $this->validateImageSecurity($file);
        if ($securityCheck !== true) {
            return ['status' => false, 'msg' => $securityCheck];
        }

        // 1. Setup des dossiers
        $pathParts = [];
        if (!empty($root)) $pathParts[] = rtrim($root, '/');
        if (!empty($directories)) $pathParts[] = implode('/', $directories);

        $relativePath = implode('/', $pathParts);
        $targetDir = $this->urlTool->dirUpload($relativePath, true);

        // 2. Récupération des tailles
        $resizeConfig = $this->imageConfig->fetchImageSizes($module, $attribute);

        $currentSuffix = (int)($options['suffix'] ?? 0);
        $baseName = $options['name'] ?? 'image';

        $originalExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filenameNoExt = $baseName . ($currentSuffix > 0 ? '_' . $currentSuffix : '');
        $finalFilename = $filenameNoExt . '.' . $originalExt;
        $targetFilePath = $targetDir . $finalFilename;

        try {
            if (!move_uploaded_file($file['tmp_name'], $targetFilePath)) {
                throw new \Exception("Erreur de déplacement physique du fichier.");
            }

            // 3. Génération WebP et tailles config_img
            $this->generateVariations($targetFilePath, $targetDir, $filenameNoExt, $originalExt, $resizeConfig);

            return ['status' => true, 'file' => $finalFilename, 'msg' => 'Upload OK'];

        } catch (\Throwable $e) {
            // Rollback (Nettoyage immédiat de la menace)
            if (isset($targetFilePath) && file_exists($targetFilePath)) {
                unlink($targetFilePath);
            }

            $this->logger->log($e, 'php', 'error');
            return ['status' => false, 'msg' => "Fichier corrompu ou illisible. Il a été rejeté."];
        }
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
        // 1. Lecture (Si le fichier n'est pas une vraie image, Intervention lancera une exception ici)
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

                $typeVal = isset($conf['type']) ? strtolower(trim((string)$conf['type'])) : '';
                $resizeVal = isset($conf['resize']) ? strtolower(trim((string)$conf['resize'])) : '';

                if (in_array($typeVal, ['adaptive', 'crop']) || in_array($resizeVal, ['adaptive', 'crop'])) {
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

                unset($variant);
            }
        }

        unset($image);
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

    public function getOriginalImagesList(string $module, string $attribute): array
    {
        $baseDir = $_SERVER['DOCUMENT_ROOT'] . '/upload/' . $module;
        if (!is_dir($baseDir)) {
            return [];
        }

        $resizeConfig = $this->imageConfig->fetchImageSizes($module, $attribute);
        $prefixes = array_column($resizeConfig, 'prefix');
        $files = [];

        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($baseDir));

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $filename = $file->getFilename();
                $ext = strtolower($file->getExtension());
                $path = $file->getPath() . '/';

                if (!in_array($ext, $this->allowedExtensions)) continue;

                $isVariant = false;
                foreach ($prefixes as $p) {
                    if (str_starts_with($filename, $p . '_')) {
                        $isVariant = true;
                        break;
                    }
                }
                if ($isVariant) continue;

                $filenameNoExt = pathinfo($filename, PATHINFO_FILENAME);
                if ($ext === 'webp' && (file_exists($path . $filenameNoExt . '.jpg') || file_exists($path . $filenameNoExt . '.png'))) {
                    continue;
                }

                $files[] = $file->getPathname();
            }
        }

        return $files;
    }

    public function processImageBatch(array $filesList, string $module, string $attribute, int $offset, int $limit): int
    {
        $resizeConfig = $this->imageConfig->fetchImageSizes($module, $attribute);
        if (empty($resizeConfig)) return 0;

        $chunk = array_slice($filesList, $offset, $limit);
        $processedCount = 0;

        foreach ($chunk as $filePath) {
            if (file_exists($filePath)) {
                $path = dirname($filePath) . '/';
                $filename = basename($filePath);
                $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                $filenameNoExt = pathinfo($filename, PATHINFO_FILENAME);

                try {
                    $this->generateVariations($filePath, $path, $filenameNoExt, $ext, $resizeConfig);
                    $processedCount++;
                } catch (\Throwable $e) {
                    $this->logger->log($e, 'php', 'error');
                }
            }
        }

        if (function_exists('gc_collect_cycles')) gc_collect_cycles();

        return $processedCount;
    }
}