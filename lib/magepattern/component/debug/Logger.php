<?php
/*
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Mage Pattern.
# The toolkit PHP for developer
# Copyright (C) 2012 - 2026 Gerits Aurelien contact[at]gerits-aurelien[dot]be
#
# OFFICIAL TEAM MAGE PATTERN:
#
#   * Gerits Aurelien (Author - Developer) contact[at]gerits-aurelien[dot]be
#
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.

# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
# Redistributions of source code must retain the above copyright notice,
# this list of conditions and the following disclaimer.
#
# Redistributions in binary form must reproduce the above copyright notice,
# this list of conditions and the following disclaimer in the documentation
# and/or other materials provided with the distribution.
#
# DISCLAIMER
*/

namespace Magepattern\Component\Debug;

use Magepattern\Component\File\FileTool;
use Magepattern\Component\Tool\PathTool;

/**
 * Class Logger
 * Gère la journalisation des événements avec support Singleton et multi-instances.
 */
class Logger
{
    const LOG_MONTH = 'MONTH';
    const LOG_YEAR  = 'YEAR';

    const LOG_LEVEL_DEBUG   = 0;
    const LOG_LEVEL_INFO    = 1;
    const LOG_LEVEL_WARNING = 2;
    const LOG_LEVEL_ERROR   = 3;

    protected static ?Logger $_instance = null;

    protected int $log_level;
    protected string $log_path;
    protected string $log_details;

    protected array $lastLog = [
        'filename' => '',
        'folder'   => ''
    ];

    protected bool $logger_ready = false;

    public float $ms = 0.0;
    public float $last = 0.0;

    /**
     * Logger constructor.
     */
    public function __construct()
    {
        $this->log_path    = defined('MP_LOG_DIR') ? MP_LOG_DIR : __DIR__ . '/logs';
        $this->log_details = defined('MP_LOG_DETAILS') ? MP_LOG_DETAILS : 'low';
        $this->log_level   = self::LOG_LEVEL_INFO;

        $this->checkLogPath($this->log_path);
    }

    /**
     * Accès au Singleton.
     */
    public static function getInstance(): self
    {
        return self::$_instance ??= new self();
    }

    /**
     * Valide et prépare le répertoire de stockage des logs.
     */
    public function checkLogPath(string $path): void
    {
        if (empty($path)) {
            $path = __DIR__ . '/logs';
        }

        if (is_dir($path)) {
            $this->log_path = realpath($path);
            $this->logger_ready = true;
        } else {
            // Tentative de création via FileTool ou mkdir natif
            try {
                if (class_exists(FileTool::class)) {
                    FileTool::mkdir([$path]);
                } else {
                    mkdir($path, 0777, true);
                }
                $this->log_path = realpath($path);
                $this->logger_ready = true;
            } catch (\Throwable) {
                $this->logger_ready = false;
            }
        }
    }

    /**
     * @param int $level
     * @return void
     */
    public function setLogLevel(int $level): void
    {
        if (in_array($level, [self::LOG_LEVEL_DEBUG, self::LOG_LEVEL_INFO, self::LOG_LEVEL_WARNING, self::LOG_LEVEL_ERROR])) {
            $this->log_level = $level;
        }
    }

    /**
     * @param string $path
     * @return void
     */
    public function setLogPath(string $path = ''): void
    {
        $this->checkLogPath($path);
    }

    /**
     * @param string $level
     * @return void
     */
    public function setLogDetails(string $level): void
    {
        if (in_array($level, ['full', 'high', 'medium', 'low'])) {
            $this->log_details = $level;
        }
    }

    /**
     * Construit le chemin complet du fichier de log.
     * @param string $folder
     * @param string $filename
     * @param string $archive
     * @return string|false
     */
    private function getLogFilePath(string $folder, string $filename, string $archive): string|false
    {
        if (!$this->logger_ready) {
            $this->checkLogPath($this->log_path);
            if (!$this->logger_ready) return false;
        }

        if (empty($folder) || empty($filename)) {
            return false;
        }

        $path = $this->log_path . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR;

        // Création du dossier de base
        if (!is_dir($path)) {
            class_exists(FileTool::class) ? FileTool::mkdir([$path]) : mkdir($path, 0777, true);
        }

        if ($archive !== '') {
            $date  = new \DateTime();
            $year  = $date->format('Y');
            $month = $date->format('m');
            $path .= $year . DIRECTORY_SEPARATOR;

            if (!is_dir($path)) {
                class_exists(FileTool::class) ? FileTool::mkdir([$path]) : mkdir($path, 0777, true);
            }

            $filename = match ($archive) {
                self::LOG_MONTH => $year . $month . '_' . $filename,
                self::LOG_YEAR  => $year . '_' . $filename,
                default         => $filename
            };
        }

        return $path . $filename . '.log';
    }

    /**
     * @param string $logfile
     * @param string $row
     * @return void
     */
    private function write(string $logfile, string $row): void
    {
        if ($this->logger_ready) {
            file_put_contents($logfile, $row, FILE_APPEND | LOCK_EX);
        }
    }

    /**
     * Enregistre une entrée dans le journal.
     * @param string|\Throwable $entry
     * @param string $folder
     * @param string $filename
     * @param string $archive
     * @param int $level
     * @return void
     */
    public function log(string|\Throwable $entry, string $folder = 'php', string $filename = '', string $archive = self::LOG_MONTH, int $level = self::LOG_LEVEL_INFO): void
    {
        if ($this->log_level > $level) {
            return;
        }

        // Auto-détection du fichier appelant si non spécifié
        if (!$filename) {
            $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
            foreach ($trace as $t) {
                // On ignore les appels internes à la classe Logger
                if (isset($t['class']) && $t['class'] === __CLASS__) continue;
                if (isset($t['file'])) {
                    $filename = basename($t['file'], ".php");
                    break;
                }
            }
            $filename = $filename ?: 'unknown_source';
        }

        $logfile = $this->getLogFilePath($folder, $filename, $archive);

        if ($logfile) {
            $levelName = match ($level) {
                self::LOG_LEVEL_DEBUG   => 'DEBUG',
                self::LOG_LEVEL_INFO    => 'INFO',
                self::LOG_LEVEL_WARNING => 'WARNING',
                self::LOG_LEVEL_ERROR   => 'ERROR',
                default                 => 'UNKNOWN'
            };

            $messageStr = ($entry instanceof \Throwable) ? $entry->getMessage() : (string)$entry;
            $date = new \DateTime();
            $formattedEntry = '';

            switch ($this->log_details) {
                case 'full':
                    $formattedEntry = $date->format('d/m/Y H:i:s') . " | [$levelName] | $messageStr\n";
                    foreach (debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS) as $trace) {
                        if (isset($trace['file'])) {
                            $file = class_exists(PathTool::class) ? str_replace(PathTool::basePath(), '', $trace['file']) : $trace['file'];
                            $formattedEntry .= "  => $file | " . ($trace['line'] ?? '?') . " | " . ($trace['function'] ?? '') . "\n";
                        }
                    }
                    break;

                case 'high':
                    $now = microtime(true);
                    if ($this->lastLog['folder'] !== $folder || $this->lastLog['filename'] !== $filename) {
                        $this->ms = 0.0;
                        $this->last = 0.0;
                    }

                    $start = ($this->ms === 0.0) ? $now : $this->ms;
                    $diff  = ($this->last !== 0.0) ? number_format($now - $this->last, 4) : '0';

                    $bt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
                    $caller = $bt[0];
                    // On cherche le vrai déclencheur hors Singleton/Logger
                    foreach ($bt as $t) {
                        if (!isset($t['class']) || $t['class'] !== __CLASS__) {
                            $caller = $t;
                            break;
                        }
                    }

                    $fileRef = isset($caller['file']) ? (class_exists(PathTool::class) ? str_replace(PathTool::basePath(), '', $caller['file']) : basename($caller['file'])) : 'unknown';

                    $formattedEntry = sprintf(
                        "%s | %s | %s ms (+%s ms) | [%s] | %s",
                        $fileRef,
                        $caller['line'] ?? '?',
                        number_format($now - $start, 4),
                        $diff,
                        $levelName,
                        $messageStr
                    );

                    $this->ms   = $start;
                    $this->last = $now;
                    $this->lastLog = ['folder' => $folder, 'filename' => $filename];
                    break;

                case 'medium':
                    $formattedEntry = $date->format('d/m/Y H:i:s') . " [$levelName] $messageStr";
                    break;

                case 'low':
                default:
                    $formattedEntry = $date->format('d/m/Y H:i:s') . " [$levelName] $messageStr";
                    break;
            }

            $this->write($logfile, rtrim($formattedEntry) . "\n");
        }
    }

    /**
     * @param string $folder
     * @param string $filename
     * @param string $archive
     * @return bool
     */
    public function removeLog(string $folder, string $filename, string $archive = self::LOG_MONTH): bool
    {
        try {
            $logfile = $this->getLogFilePath($folder, $filename, $archive);

            if ($logfile && file_exists($logfile)) {
                $removed = class_exists(FileTool::class) ? FileTool::remove($logfile) : unlink($logfile);
                if ($removed) {
                    $this->log("Log removed: $logfile", 'php', 'logger_system', self::LOG_MONTH, self::LOG_LEVEL_INFO);
                    return true;
                }
            }
            return false;
        } catch (\Throwable $e) {
            $this->log($e, 'php', 'logger_error', self::LOG_MONTH, self::LOG_LEVEL_ERROR);
            return false;
        }
    }
}