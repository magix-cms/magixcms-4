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

namespace Magepattern\Component\Tool;

use Magepattern\Component\Debug\Logger;

class TimerTool
{
    private static array $instances = [];
    private float $start = 0;
    private float $pauseTime = 0;
    private float $totalPausedDuration = 0;
    private int $currentStep = 0;
    private array $steps = [];

    /**
     * Accès Multiton : Permet d'appeler le timer depuis n'importe où
     */
    public static function getInstance(string $name = 'default'): self
    {
        if (!isset(self::$instances[$name])) {
            self::$instances[$name] = new self();
        }
        return self::$instances[$name];
    }

    public function start(): self
    {
        $this->reset();
        $this->start = microtime(true);
        $this->recordStep('start');
        return $this;
    }

    public function lap(?string $label = null): void
    {
        $this->currentStep++;
        $this->recordStep($label ?? "step_{$this->currentStep}");
    }

    private function recordStep(string $label): void
    {
        $now = microtime(true);
        $this->steps[$this->currentStep] = [
            'label'   => $label,
            'at'      => $now,
            'time'    => round($now - $this->start - $this->totalPausedDuration, 4),
            'memory'  => $this->formatBytes(memory_get_usage()),
            'peak'    => $this->formatBytes(memory_get_peak_usage())
        ];
    }

    public function pause(): void { if ($this->pauseTime === 0.0) $this->pauseTime = microtime(true); }

    public function resume(): void
    {
        if ($this->pauseTime !== 0.0) {
            $this->totalPausedDuration += (microtime(true) - $this->pauseTime);
            $this->pauseTime = 0;
        }
    }

    /**
     * Arrête et loggue automatiquement si c'est trop lent
     * @param float $slowThreshold Seuil en secondes (ex: 1.5)
     */
    public function stop(float $slowThreshold = 0): array
    {
        $this->lap('end');
        $total = end($this->steps)['time'];

        $report = [
            'total_time'   => $total . 's',
            'peak_memory'  => $this->formatBytes(memory_get_peak_usage()),
            'steps'        => $this->steps
        ];

        if ($slowThreshold > 0 && $total > $slowThreshold) {
            Logger::getInstance()->log("Performance Warning: Script slow ($total s)", "perf", "warning");
        }

        return $report;
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes > 1024 && $i < count($units) - 1) { $bytes /= 1024; $i++; }
        return round($bytes, 2) . ' ' . $units[$i];
    }

    private function reset(): void
    {
        $this->start = $this->pauseTime = $this->totalPausedDuration = 0;
        $this->currentStep = 0;
        $this->steps = [];
    }
}