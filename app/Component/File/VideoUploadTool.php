<?php

declare(strict_types=1);

namespace App\Component\File;

/*
 * This file is part of MAGIX CMS.
 * Copyright (C) 2008 - 2026 Gerits Aurelien (Magix CMS).
 * License: GPLv3
 */

use Magepattern\Component\Debug\Logger;
use App\Component\Routing\UrlTool;

class VideoUploadTool
{
    protected UrlTool $urlTool;
    protected Logger $logger;

    // SÉCURITÉ : Liste blanche stricte des extensions ET Mimes autorisés pour la vidéo
    private array $allowedExtensions = ['mp4', 'avi', 'mpeg', 'mov', 'qt'];
    private array $allowedMimes = [
        'video/mp4',
        'video/x-msvideo',
        'video/mpeg',
        'video/quicktime'
    ];

    public function __construct()
    {
        if (!defined('DS')) {
            define('DS', DIRECTORY_SEPARATOR);
        }

        $this->urlTool = new UrlTool();
        $this->logger = Logger::getInstance();
    }

    /**
     * Méthode de validation centralisée pour les vidéos (Avant écriture)
     */
    private function validateVideoSecurity(array $file): bool|string
    {
        // 1. Vérification de l'extension
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $this->allowedExtensions, true)) {
            return "Extension non autorisée. Seules les vidéos (mp4, avi, mpeg, mov, qt) sont acceptées.";
        }

        // 2. Vérification du vrai Type MIME du fichier temporaire
        if (!file_exists($file['tmp_name'])) {
            return "Fichier temporaire introuvable.";
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime, $this->allowedMimes, true)) {
            return "Le contenu du fichier est invalide. Il ne correspond pas à un format vidéo sécurisé.";
        }

        // 3. Prévention des attaques par double extension
        if (preg_match('/\.php/i', $file['name'])) {
            return "Nom de fichier suspect détecté.";
        }

        return true;
    }

    /**
     * Upload d'un fichier vidéo UNIQUE (ex: pour l'envoi temporaire avant Dailymotion)
     */
    public function singleVideoUpload(string $root, array $directories = [], array $options = []): array
    {
        $postKey = $options['postKey'] ?? 'file';
        $file = $_FILES[$postKey] ?? null;

        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            return ['status' => false, 'msg' => 'Aucun fichier valide reçu ou erreur d\'upload.'];
        }

        // VÉRIFICATION DE SÉCURITÉ
        $securityCheck = $this->validateVideoSecurity($file);
        if ($securityCheck !== true) {
            return ['status' => false, 'msg' => $securityCheck];
        }

        // 1. Setup des dossiers
        $pathParts = [];
        if (!empty($root)) $pathParts[] = rtrim($root, '/');
        if (!empty($directories)) $pathParts[] = implode('/', $directories);

        $relativePath = implode('/', $pathParts);
        $targetDir = $this->urlTool->dirUpload($relativePath, true);

        // 2. Formatage du nom de fichier
        $currentSuffix = (int)($options['suffix'] ?? 0);
        $baseName = $options['name'] ?? 'video';

        // Sécurisation du nom de base pour éviter les caractères spéciaux
        $baseName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $baseName);

        $originalExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filenameNoExt = $baseName . ($currentSuffix > 0 ? '_' . $currentSuffix : '');
        $finalFilename = $filenameNoExt . '.' . $originalExt;
        $targetFilePath = $targetDir . $finalFilename;

        try {
            if (!move_uploaded_file($file['tmp_name'], $targetFilePath)) {
                throw new \Exception("Erreur de déplacement physique du fichier vidéo.");
            }

            // Retourne le chemin complet pour faciliter le traitement ultérieur (API Dailymotion)
            return [
                'status'    => true,
                'file'      => $finalFilename,
                'full_path' => $targetFilePath,
                'msg'       => 'Upload vidéo réussi.'
            ];

        } catch (\Throwable $e) {
            if (isset($targetFilePath) && file_exists($targetFilePath)) {
                unlink($targetFilePath);
            }

            $this->logger->log($e, 'php', 'error');
            return ['status' => false, 'msg' => "Fichier corrompu ou illisible lors de l'écriture."];
        }
    }

    /**
     * Supprime un fichier vidéo localement (utile après un push réussi vers une API tierce)
     */
    public function removeLocalVideo(string $filePath): bool
    {
        if (file_exists($filePath) && is_file($filePath)) {
            return unlink($filePath);
        }
        return false;
    }
}