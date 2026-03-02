<?php

declare(strict_types=1);

namespace App\Component\File;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Magepattern\Component\HTTP\Url;
use Magepattern\Component\Debug\Logger;
use App\Component\Routing\UrlTool;
use App\Component\Db\ConfigDb;
// TODO: Importe ici ta classe de configuration de base de données (ex: use App\Backend\Db\ConfigDb;)

class UploadTool
{
    protected const WEBP_EXT = '.webp';

    protected UrlTool $urlTool;
    protected ImageManager $imageManager;
    protected Logger $logger;

    protected ConfigDb $imageConfig;

    private array $mimeTypes = [
        'txt' => 'text/plain',
        'htm' => 'text/html',
        'html' => 'text/html',
        'php' => 'text/html',
        'css' => 'text/css',
        'js' => 'application/javascript',
        'json' => 'application/json',
        'xml' => ['application/xml', 'text/xml'],
        'swf' => 'application/x-shockwave-flash',
        'flv' => 'video/x-flv',

        // images
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'bmp' => 'image/bmp',
        'ico' => 'image/vnd.microsoft.icon',
        'tiff' => 'image/tiff',
        'tif' => 'image/tiff',
        'svg' => 'image/svg+xml',
        'svgz' => 'image/svg+xml',
        'webp' => 'image/webp',

        // archives
        'zip' => 'application/zip',
        'rar' => 'application/x-rar-compressed',
        'exe' => 'application/x-msdownload',
        'msi' => 'application/x-msdownload',
        'cab' => 'application/vnd.ms-cab-compressed',

        // audio/video
        'mp3' => 'audio/mpeg',
        'qt' => 'video/quicktime',
        'mov' => 'video/quicktime',
        'mp4' => 'video/mp4',
        'mpeg' => 'video/mpeg',

        // adobe & office
        'pdf' => 'application/pdf',
        'psd' => 'image/vnd.adobe.photoshop',
        'ai' => 'application/postscript',
        'eps' => 'application/postscript',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    ];

    public string $image = '';
    public string $file = '';
    public array $images = [];
    public array $files = [];

    public function __construct()
    {
        if (!defined('DS')) {
            define('DS', DIRECTORY_SEPARATOR);
        }

        $this->urlTool = new UrlTool();
        $this->logger = Logger::getInstance();
        $this->imageManager = new ImageManager(new Driver());

        $this->imageConfig = new ConfigDb();

        if (isset($_FILES['img']['name'])) $this->image = Url::clean($_FILES['img']['name']);
        if (isset($_FILES['img_multiple']['name'])) $this->images = $_FILES['img_multiple']['name'];
        if (isset($_FILES['file']['name'])) $this->file = Url::clean($_FILES['file']['name']);
        if (isset($_FILES['files']['name'])) $this->files = $_FILES['files']['name'];
    }

    public function mimeContentType(array $data): array
    {
        $mimeContent = null;
        if (isset($data['filename']) && file_exists($data['filename'])) {
            $mimeContent = mime_content_type($data['filename']);
        } elseif (isset($data['mime'])) {
            $mimeContent = $data['mime'];
        }

        if ($mimeContent !== null) {
            foreach ($this->mimeTypes as $key => $value) {
                if (is_array($value) && in_array($mimeContent, $value)) {
                    return ['type' => $key, 'mime' => $mimeContent];
                }
                if ($value === $mimeContent) {
                    return ['type' => $key, 'mime' => $mimeContent];
                }
            }
        }
        return ['type' => null, 'mime' => null];
    }

    private function imageValid(string $filename): bool
    {
        try {
            if (!function_exists('exif_imagetype')) {
                $size = @getimagesize($filename);
                if (!$size) return false;

                return in_array($size['mime'], ['image/gif', 'image/jpeg', 'image/png', 'image/webp']);
            } else {
                $size = exif_imagetype($filename);
                return in_array($size, [IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP]);
            }
        } catch (\Throwable $e) {
            $this->logger->log($e, 'php', 'error', Logger::LOG_MONTH, Logger::LOG_LEVEL_ERROR);
        }
        return false;
    }

    private function imgSizeMin(string $source, int $minw, int $minh): bool
    {
        $size = @getimagesize($source);
        if (!$size) return false;

        [$width, $height] = $size;
        return !($width < $minw || $height < $minh);
    }

    private function reArrayFiles(array $postFiles): array
    {
        $files = [];
        $file_count = count($postFiles['name']);
        $keys = array_keys($postFiles);

        for ($i = 0; $i < $file_count; $i++) {
            foreach ($keys as $key) {
                $files[$i][$key] = $postFiles[$key][$i];
            }
        }
        return $files;
    }

    private function createFormat(string $path, string $filename, string $ext, int $width, int $height, string $resize = 'basic', string $prefix = ''): string
    {
        if (!empty($prefix)) $prefix .= '_';

        try {
            $image = $this->imageManager->read($path . $filename . $ext);

            switch ($resize) {
                case 'adaptive':
                    $image->coverDown($width, $height);
                    break;
                case 'basic':
                default:
                    $image->scaleDown($width, $height);
                    break;
            }

            $image->save($path . $prefix . $filename . $ext, quality: 80);

            if (function_exists('imagewebp')) {
                $image->save($path . $prefix . $filename . self::WEBP_EXT, quality: 80);
            }

            return $path . $filename . $ext;
        } catch (\Throwable $e) {
            $this->logger->log($e, 'php', 'error', Logger::LOG_MONTH, Logger::LOG_LEVEL_ERROR);
        }
        return '';
    }

    public function getUploadImg(array $image, string $path, bool $debug = false): array
    {
        $msg = '';
        $mimeContent = null;
        $cleanName = $image["name"];

        if ($image['error'] === UPLOAD_ERR_OK) {
            if ($this->imageValid($image['tmp_name'])) {
                $tmpImg = $image["tmp_name"];
                $mimeContent = $this->mimeContentType(['filename' => $tmpImg]);

                if (is_uploaded_file($tmpImg)) {
                    $source = $tmpImg;
                    $cleanName = Url::clean($image["name"]);
                    $target = $this->urlTool->basePath($path);

                    if (!is_dir($target)) {
                        mkdir($target, 0755, true);
                    }

                    if (!move_uploaded_file($source, rtrim($target, DS) . DS . $cleanName)) {
                        $msg .= 'Erreur lors de l\'écriture du fichier temporaire.';
                    }
                } else {
                    $msg .= 'Erreur d\'écriture disque.';
                }
            } else {
                $msg .= 'Format invalide (seuls jpg, png, gif, webp sont acceptés).';
            }
        } else {
            $msg .= match ($image['error']) {
                UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'Le fichier est trop volumineux.',
                UPLOAD_ERR_CANT_WRITE => 'Erreur d\'écriture disque.',
                UPLOAD_ERR_NO_FILE => 'Aucun fichier reçu.',
                default => 'Erreur inconnue lors de l\'upload.',
            };
        }

        return [
            'status' => empty($msg),
            'notify' => empty($msg) ? 'upload' : 'upload_error',
            'name' => $cleanName,
            'tmp_name' => $image["tmp_name"],
            'mimecontent' => $mimeContent,
            'msg' => empty($msg) ? 'Upload réussi' : $msg
        ];
    }

    private function imagePostUploadProcess(array $upload, string $module, string $attribute, string $root, array $directories = [], array $options = [], bool $debug = false): array
    {
        if (!$upload['status']) return [];

        $dirImg = $this->urlTool->dirUpload($root, true);
        $imageDirectories = $this->urlTool->dirUploadCollection($root, $directories, true);

        $imageConfig = $this->imageConfig->fetchImageSizes($module, $attribute);

        if ($this->imgSizeMin($dirImg . $upload['name'], 25, 25)) {

            if (!empty($options['edit']) && !empty($imageDirectories)) {
                foreach ($imageDirectories as $dirPath) {
                    $imgData = pathinfo($dirPath . $options['edit']);
                    $filename = $imgData['filename'];
                    $mimeContent = $this->mimeContentType(['filename' => $dirPath . $options['edit']]);
                    $ext = '.' . ($mimeContent['type'] ?? 'jpg');

                    foreach ($imageConfig as $key => $value) {
                        $prefix = (isset($options['prefix']) ? (is_array($options['prefix']) ? $options['prefix'][$key] : $options['prefix']) : $value['prefix']) . '_';
                        @unlink($dirPath . $prefix . $filename . $ext);
                        @unlink($dirPath . $prefix . $filename . self::WEBP_EXT);
                    }
                    @unlink($dirPath . $filename . $ext);
                    @unlink($dirPath . $filename . self::WEBP_EXT);
                }
            }

            $fileInfo = pathinfo($upload['name']);
            $ext = '.' . $fileInfo['extension'];
            $originName = $fileInfo['filename'];
            $filename = $originName;

            if (!empty($options['name'])) {
                $filename = $options['name'] . (!empty($options['suffix']) && !is_array($options['suffix']) ? '_' . $options['suffix'] : '');
                rename($dirImg . $originName . $ext, $dirImg . $filename . $ext);
            } elseif (!empty($options['suffix']) && !is_array($options['suffix'])) {
                $filename = $originName . '_' . $options['suffix'];
                rename($dirImg . $originName . $ext, $dirImg . $filename . $ext);
            }

            $source = $dirImg . $filename . $ext;

            if (!empty($imageDirectories)) {
                foreach ($imageDirectories as $dirPath) {
                    if (!empty($imageConfig)) {
                        foreach ($imageConfig as $key => $value) {
                            $prefix = (isset($options['prefix']) ? (is_array($options['prefix']) ? $options['prefix'][$key] : $options['prefix']) : $value['prefix']) . '_';
                            $suffix = (isset($options['suffix']) && is_array($options['suffix'])) ? $options['suffix'][$key] : '';

                            $this->createFormat($dirPath, $filename . $suffix, $ext, (int)$value['width'], (int)$value['height'], $value['resize'], trim($prefix, '_'));
                        }

                        try {
                            $image = $this->imageManager->read($source);
                            $image->save($dirPath . $filename . $ext, quality: 90);
                        } catch (\Throwable $e) {
                            $this->logger->log($e, 'php', 'error', Logger::LOG_MONTH, Logger::LOG_LEVEL_ERROR);
                        }
                    }
                }
                if (!empty($dirImg)) @unlink($source);
            }

            if (isset($options['original_remove']) && $options['original_remove']) {
                foreach ($imageDirectories as $dirPath) {
                    @unlink($dirPath . $filename . $ext);
                }
            }

            return [
                'file' => $filename . $ext,
                'status' => $upload['status'],
                'notify' => $upload['notify'],
                'msg' => $upload['msg']
            ];
        } else {
            @unlink($dirImg . $upload['name']);
        }

        return [];
    }

    public function imageUpload(string $module, string $attribute, string $root, array $directories = [], array $options = [], bool $debug = false): array
    {
        $default = [
            'postKey' => 'img',
            'name' => '',
            'edit' => false,
            'suffix' => null,
            'suffix_increment' => false,
            'original_remove' => false,
            'progress' => true,
            'template' => null
        ];

        $options = array_merge($default, $options);
        $postKey = $options['postKey'];

        if (isset($_FILES[$postKey]['name']) && !empty($_FILES[$postKey]['name'])) {
            try {
                $resultUpload = $this->getUploadImg($_FILES[$postKey], $this->urlTool->dirUpload($root, false), $debug);
                return $this->imagePostUploadProcess($resultUpload, $module, $attribute, $root, $directories, $options, $debug);
            } catch (\Throwable $e) {
                $this->logger->log($e, 'php', 'error', Logger::LOG_MONTH, Logger::LOG_LEVEL_ERROR);
            }
        }
        return [];
    }

    public function multipleImageUpload(string $module, string $attribute, string $root, array $directories = [], array $options = [], bool $debug = false): array
    {
        $default = [
            'postKey' => 'img_multiple',
            'name' => '',
            'edit' => false,
            'suffix' => null,
            'suffix_increment' => false,
            'original_remove' => false,
            'progress' => true,
            'template' => null
        ];

        $options = array_merge($default, $options);
        $postKey = $options['postKey'];

        if (isset($_FILES[$postKey]['name']) && !empty($_FILES[$postKey]['name'])) {
            try {
                $resultData = [];
                $uploadedFiles = $this->reArrayFiles($_FILES[$postKey]);

                if (!empty($uploadedFiles)) {
                    $resultUpload = [];
                    foreach ($uploadedFiles as $file) {
                        $resultUpload[] = $this->getUploadImg($file, $this->urlTool->dirUpload($root, false), $debug);
                    }

                    foreach ($resultUpload as $upload) {
                        if (isset($options['suffix']) && $options['suffix_increment']) {
                            $options['suffix']++;
                        }
                        $resultData[] = $this->imagePostUploadProcess($upload, $module, $attribute, $root, $directories, $options, $debug);
                    }

                    return $resultData;
                }
            } catch (\Throwable $e) {
                $this->logger->log($e, 'php', 'error', Logger::LOG_MONTH, Logger::LOG_LEVEL_ERROR);
            }
        }
        return [];
    }
}