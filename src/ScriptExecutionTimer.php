<?php

namespace Biboletin\ScriptExecutionTimer;

use InvalidArgumentException;

class ScriptExecutionTimer
{
    protected array $timers = [];
    
    protected array $durations = [];
    
    public function start(string $name): void
    {
        $this->timers[$name] = microtime(true);
    }
    
    public function stop(string $name): void
    {
        if (!isset($this->timers[$name])) {
            throw new InvalidArgumentException("Timer with name '" . $name . "' has not been started.");
        }

        if (isset($this->timers[$name])) {
            // Convert to milliseconds
            $this->durations[$name] = (microtime(true) - $this->timers[$name]) * 1000;
            unset($this->timers[$name]);
        }
    }
    
    public function getDuration(string $name): float
    {
        if (!isset($this->durations[$name])) {
            throw new InvalidArgumentException("Timer with name '" . $name . "' has not been stopped or does not exist.");
        }

        return $this->durations[$name];
    }
    
    public function getAllDurations(): array
    {
        return $this->durations;
    }
    
    public function reset(): void
    {
        $this->timers = [];
        $this->durations = [];
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
    
    public function getAllDurationValues(): array
    {
        return $this->durations;
    }
    
    public function getServerTimingHeader(): string
    {
        $parts = [];
        foreach ($this->durations as $name => $duration) {
            $label = str_replace([' ', ',', ';'], '_', ucfirst($name));
            $parts[] = sprintf('%s;dur=%.2f;desc="%s"', $name, $duration, $label);
        }

        return implode(', ', $parts);
    }
    
    public function sendHeader(): void
    {
        if (!headers_sent()) {
            $header = $this->getServerTimingHeader();
            if ($header) {
                header('Server-Timing: ' . $header);
            }
        } else {
            throw new InvalidArgumentException("Headers have already been sent, cannot send Server-Timing header.");
        }
    }
}
