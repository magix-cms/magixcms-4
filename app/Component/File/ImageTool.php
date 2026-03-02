<?php

declare(strict_types=1);

namespace App\Component\File;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Magepattern\Component\Debug\Logger;
use App\Component\Routing\UrlTool;
use App\Component\Db\ConfigDb;

class ImageTool
{
    protected const WEBP_EXT = '.webp';

    protected UrlTool $urlTool;
    protected ImageManager $imageManager;
    protected Logger $logger;

    protected ConfigDb $configDb;

    public function __construct()
    {
        if (!defined('DS')) {
            define('DS', DIRECTORY_SEPARATOR);
        }

        $this->urlTool = new UrlTool();
        $this->logger = Logger::getInstance();
        $this->imageManager = new ImageManager(new Driver());

        $this->configDb = new ConfigDb();
    }

    public function module(): array
    {
        return ['catalog', 'news', 'pages', 'logo', 'plugins'];
    }

    public function resize(): array
    {
        return ['basic', 'adaptive'];
    }

    public function getImageInfos(string $src): array
    {
        $info = @getimagesize($src);
        if (!$info) {
            return ['width' => 0, 'height' => 0, 'type' => 0, 'attr' => ''];
        }

        return [
            'width'  => $info[0],
            'height' => $info[1],
            'type'   => $info[2],
            'attr'   => $info[3]
        ];
    }

    public function getConfigItems(string $module, string $attribute): array
    {
        if (!isset($this->imgConfig[$module][$attribute])) {
            // L'appel propre et typé à notre nouvelle classe !
            $imgConf = $this->configDb->fetchImageSizes($module, $attribute);

            if (empty($imgConf)) {
                return [];
            }
            $this->imgConfig[$module][$attribute] = $imgConf;
        }
        return $this->imgConfig[$module][$attribute];
    }

    private function setImageData(string $path, string $name, array $conf): array
    {
        $image = [];
        $fullPath = rtrim($this->urlTool->basePath($path), DS) . DS . $conf['prefix'] . '_' . $name;

        if (file_exists($fullPath)) {
            $imageInfos = $this->getImageInfos($fullPath);
            $filename = pathinfo($name, PATHINFO_FILENAME);

            $image = [
                'src'      => '/' . ltrim($path, '/') . $conf['prefix'] . '_' . $name,
                'src_webp' => '/' . ltrim($path, '/') . $conf['prefix'] . '_' . $filename . self::WEBP_EXT,
                'w'        => $conf['resize'] === 'basic' ? $imageInfos['width'] : $conf['width'],
                'h'        => $conf['resize'] === 'basic' ? $imageInfos['height'] : $conf['height'],
                'crop'     => $conf['resize'],
                'ext'      => mime_content_type($fullPath)
            ];
        }
        return $image;
    }

    public function setModuleImage(string $module, string $attribute, string $name = '', ?int $id = null, string $alt = '', string $title = ''): array
    {
        $image = [];
        $config = $this->getConfigItems($module, $attribute);

        if (!empty($name)) {
            $imgPath = 'upload/' . $module . ($attribute !== $module ? '/' . $attribute : '') . ($id ? '/' . $id . '/' : '/');
            foreach ($config as $v) {
                $image[$v['type']] = $this->setImageData($imgPath, $name, $v);
            }
            $image['name']  = $name;
            $image['alt']   = $alt;
            $image['title'] = $title;
        } else {
            $defaultPath = 'img/default/' . $module . ($attribute !== $module ? '/' . $attribute . '/' : '/');
            $default = '';

            foreach ($config as $v) {
                if ($default === '') {
                    $default = file_exists($this->urlTool->basePath($defaultPath) . $v['prefix'] . '_default.png') ? 'default.png' : 'default.jpg';
                }
                $image[$v['type']] = $this->setImageData($defaultPath, $default, $v);
            }
        }
        return $image;
    }

    public function setModuleImages(string $module, string $attribute, array $images, int $id): array
    {
        if (!empty($images)) {
            foreach ($images as &$image) {
                $image['img'] = $this->setModuleImage($module, $attribute, $image['name'] ?? '', $id);
            }
        }
        return $images;
    }

    public function setMoveMultiImg(array $filesData, array $data, array $imgTempCollection, array $imgCollection, bool $debug = false): array
    {
        $resultData = [];
        try {
            if (empty($filesData)) return [];

            $dirImgTemp = $this->urlTool->dirUpload($imgTempCollection['upload_root_dir'], true);
            $dirImg = $this->urlTool->dirUpload($imgCollection['upload_root_dir'], true);

            $fetchConfig = $this->getConfigItems($data['module_img'], $data['attribute_img']);

            foreach ($filesData as $item) {
                $sourceFile = rtrim($dirImgTemp, DS) . DS . ltrim((string)$imgTempCollection['upload_dir'], DS) . DS . $item['name_img'];

                if (!file_exists($sourceFile)) continue;

                $ext = '.' . pathinfo($sourceFile, PATHINFO_EXTENSION);
                $prefix = '';
                $name = bin2hex(random_bytes(8));

                if (isset($data['prefix_name'])) {
                    if (isset($data['prefix_increment']) && $data['prefix_increment']) {
                        $data['prefix_name']++;
                        $prefix = $data['prefix_name'] . '_';
                    } else {
                        $prefix = $data['prefix_name'];
                    }
                }

                if (isset($data['name']) && !empty($data['name'])) {
                    $name = $data['name'];
                }
                $newName = $prefix . $name;

                rename($sourceFile, $dirImg . $newName . $ext);

                if (!empty($fetchConfig)) {
                    $dirImgArray = $this->urlTool->dirUploadCollection($imgCollection['upload_root_dir'], is_array($imgCollection['upload_dir']) ? $imgCollection['upload_dir'] : [$imgCollection['upload_dir']]);

                    foreach ($fetchConfig as $keyConf => $valueConf) {
                        $filesPath = is_array($dirImgArray) ? $dirImgArray[$keyConf] : $dirImgArray[0];
                        if (!is_dir($filesPath)) mkdir($filesPath, 0755, true);

                        try {
                            $thumb = $this->imageManager->read($dirImg . $newName . $ext);
                            $filePrefix = isset($data['prefix']) ? (is_array($data['prefix']) ? $data['prefix'][$keyConf] : '') : '';

                            switch ($valueConf['resize_img'] ?? 'basic') {
                                case 'adaptive':
                                    $thumb->coverDown((int)$valueConf['width_img'], (int)$valueConf['height_img']);
                                    break;
                                case 'basic':
                                default:
                                    $thumb->scaleDown((int)$valueConf['width_img'], (int)$valueConf['height_img']);
                                    break;
                            }

                            $thumb->save($filesPath . $filePrefix . $newName . $ext, quality: 80);

                            if (function_exists('imagewebp') && (!isset($data['webp']) || $data['webp'] !== false)) {
                                $thumb->save($filesPath . $filePrefix . $newName . self::WEBP_EXT, quality: 80);
                            }

                        } catch (\Throwable $e) {
                            $this->logger->log($e, 'php', 'error', Logger::LOG_MONTH, Logger::LOG_LEVEL_ERROR);
                        }
                    }

                    if (isset($imgCollection['upload_dir']) && $imgCollection['upload_dir'] !== '') {
                        $finalDir = rtrim(is_array($dirImgArray) ? current($dirImgArray) : $dirImgArray[0], DS) . DS;
                        rename($dirImg . $newName . $ext, $finalDir . $newName . $ext);
                    }
                }

                $resultData[] = [
                    'file'   => $newName . $ext,
                    'statut' => true,
                    'notify' => 'upload',
                    'msg'    => 'Upload success'
                ];
            }
            return $resultData;
        } catch (\Throwable $e) {
            $this->logger->log($e, 'php', 'error', Logger::LOG_MONTH, Logger::LOG_LEVEL_ERROR);
            return [];
        }
    }
}