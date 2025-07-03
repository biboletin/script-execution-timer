<?php

namespace Biboletin\ScriptExecutionTimer;

use InvalidArgumentException;

/**
 * Class ScriptExecutionTimer
 *
 * A simple utility class to measure script execution time and memory usage.
 * It provides methods to start and stop timers, retrieve durations, memory usage,
 * and send Server-Timing headers.
 */
class ScriptExecutionTimer
{
    protected array $timers = [];

    protected array $durations = [];

    protected array $memoryStart = [];

    protected array $memoryUsage = [];

    protected array $memoryPeak = [];

    public function start(string $name): void
    {
        $this->timers[$name] = microtime(true);
        $this->memoryStart[$name] = memory_get_usage(true);
    }

    public function stop(string $name): void
    {
        if (!isset($this->timers[$name])) {
            throw new InvalidArgumentException("Timer with name '$name' has not been started.");
        }

        $end = microtime(true);
        $duration = ($end - $this->timers[$name]) * 1000;
        $this->durations[$name] = $duration;

        $currentMemory = memory_get_usage(true);
        $this->memoryUsage[$name] = max(0, $currentMemory - $this->memoryStart[$name]);
        $this->memoryPeak[$name] = memory_get_peak_usage(true);
    }

    public function getDuration(string $name): float
    {
        if (!isset($this->durations[$name])) {
            throw new InvalidArgumentException("Timer with name '$name' has not been stopped or does not exist.");
        }
        return $this->durations[$name];
    }

    public function getMemoryUsageInKB(string $name): float
    {
        return round(($this->memoryUsage[$name] ?? 0) / 1024, 2);
    }

    public function getMemoryPeakUsageInKB(string $name): float
    {
        return round(($this->memoryPeak[$name] ?? 0) / 1024, 2);
    }

    public function getServerTimingHeader(): string
    {
        $parts = [];
        foreach ($this->durations as $name => $duration) {
            $dur = round($duration, 2);
            $mem = $this->getMemoryUsageInKB($name);
            $peak = $this->getMemoryPeakUsageInKB($name);
            $parts[] = sprintf(
                '%s;dur=%.2f;desc="Memory Usage: %.2f KB, Peak Memory: %.2f KB"',
                $name,
                $dur,
                $mem,
                $peak
            );
        }
        return implode(', ', $parts);
    }

    public function sendHeader(): void
    {
        if (headers_sent()) {
            throw new InvalidArgumentException('Headers have already been sent, cannot send headers.');
        }

        $serverTiming = $this->getServerTimingHeader();
        if ($serverTiming) {
            header('Server-Timing: ' . $serverTiming);
        }

        $memoryUsageHeader = $this->getMemoryUsageHeader();
        if ($memoryUsageHeader) {
            header('X-Memory-Usage: ' . $memoryUsageHeader);
        }
    }

    /**
     * Returns the total memory usage (current and peak) as a human-readable string.
     *
     * @return string
     */
    public function getMemoryUsageHeader(): string
    {
        $currentUsage = memory_get_usage(true);
        $peakUsage = memory_get_peak_usage(true);

        return sprintf(
            'Current: %.2f MB; Peak: %.2f MB',
            $currentUsage / 1024 / 1024,
            $peakUsage / 1024 / 1024
        );
    }

    public function reset(): void
    {
        $this->timers = [];
        $this->durations = [];
        $this->memoryStart = [];
        $this->memoryUsage = [];
        $this->memoryPeak = [];
    }

    public function hasTimer(string $name): bool
    {
        return isset($this->timers[$name]) || isset($this->durations[$name]);
    }

    public function getTimerNames(): array
    {
        return array_keys($this->timers);
    }

    public function getDurationNames(): array
    {
        return array_keys($this->durations);
    }

    public function getAllTimers(): array
    {
        return $this->timers;
    }

    public function getAllDurations(): array
    {
        return $this->durations;
    }
}
