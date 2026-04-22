<?php

declare(strict_types=1);

namespace App\Component\Cache;

use Magepattern\Component\File\CacheTool;
use Magepattern\Component\File\FileTool;

class CacheManager
{
    private static ?CacheTool $instance = null;

    public static function init(string $driver = 'files'): ?CacheTool
    {
        if (self::$instance !== null) {
            return self::$instance;
        }

        if ($driver === 'none') return null;

        if ($driver === 'files') {
            // 🟢 CORRECTION : Le bon dossier est "sql" et non "data"
            $cacheDir = ROOT_DIR . 'var' . DS . 'caches' . DS . 'sql';
            $securePath = FileTool::createSecureCacheDir($cacheDir);
            self::$instance = new CacheTool($securePath, 3600);
        }

        return self::$instance;
    }

    public static function get(): ?CacheTool
    {
        if (self::$instance === null) self::init('files');
        return self::$instance;
    }

    /**
     * PURGE TOTALE DU FRONTEND (SQL + SMARTY)
     */
    /**
     * PURGE TOTALE DU FRONTEND (SQL + SMARTY CACHES + SMARTY COMPILED)
     */
    public static function clearFrontend(string $sqlTag = ''): void
    {
        // 1. Purge douce par Tag
        $cache = self::get();
        if ($cache && !empty($sqlTag) && method_exists($cache, 'clearByTag')) {
            $cache->clearByTag($sqlTag);
        }

        // 2. PURGE PHYSIQUE DES DONNÉES SQL (.json)
        $sqlDir = ROOT_DIR . 'var' . DS . 'caches' . DS . 'sql' . DS;
        if (is_dir($sqlDir)) {
            FileTool::remove($sqlDir);
            if (!is_dir($sqlDir)) {
                @mkdir($sqlDir, 0775, true);
            }
        }

        // 3. Purge physique des Templates HTML (Smarty Cache)
        $tplDir = ROOT_DIR . 'var' . DS . 'tpl_caches' . DS;
        if (is_dir($tplDir)) {
            FileTool::remove($tplDir);
            if (!is_dir($tplDir)) {
                @mkdir($tplDir, 0775, true);
            }
        }

        // 🟢 4. NOUVEAU : Purge des templates COMPILÉS (Crucial après une installation)
        $compileDir = ROOT_DIR . 'var' . DS . 'templates_c' . DS;
        if (is_dir($compileDir)) {
            FileTool::remove($compileDir);
            if (!is_dir($compileDir)) {
                @mkdir($compileDir, 0775, true);
            }
        }
    }
}