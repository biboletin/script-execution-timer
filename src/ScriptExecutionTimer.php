<?php

namespace Biboletin\ScriptExecutionTimer;

use InvalidArgumentException;

/**
 * Class ScriptExecutionTimer
 *
 * A simple utility to measure script execution time and send Server-Timing headers.
 *
 * @package Biboletin\ScriptExecutionTimer
 */
class ScriptExecutionTimer
{
    /**
     * Stores the start times of timers.
     *
     * @var array<string, float>
     */
    protected array $timers = [];

    /**
     * Stores the durations of timers in milliseconds.
     *
     * @var array<string, float>
     */
    protected array $durations = [];

    /**
     * Starts a timer with the given name.
     *
     * @param string $name The name of the timer.
     *
     * @throws InvalidArgumentException If the timer is already running.
     */
    public function start(string $name): void
    {
        $this->timers[$name] = microtime(true);
    }

    /**
     * Stops the timer with the given name and records its duration.
     *
     * @param string $name The name of the timer.
     *
     * @throws InvalidArgumentException If the timer has not been started.
     */
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

    /**
     * Gets the duration of the timer with the given name.
     *
     * @param string $name The name of the timer.
     *
     * @return float The duration in milliseconds.
     * @throws InvalidArgumentException If the timer has not been stopped or does not exist.
     */
    public function getDuration(string $name): float
    {
        if (!isset($this->durations[$name])) {
            throw new InvalidArgumentException("Timer with name '" . $name . "' has not been stopped or does not exist.");
        }

        return $this->durations[$name];
    }

    /**
     * Gets all recorded durations.
     *
     * @return array<string, float> An associative array of timer names and their durations in milliseconds.
     */
    public function getAllDurations(): array
    {
        return $this->durations;
    }

    /**
     * Resets all timers and durations.
     */
    public function reset(): void
    {
        $this->timers = [];
        $this->durations = [];
    }

    /**
     * Checks if a timer with the given name exists.
     *
     * @param string $name The name of the timer.
     *
     * @return bool True if the timer exists, false otherwise.
     */
    public function hasTimer(string $name): bool
    {
        return isset($this->timers[$name]) || isset($this->durations[$name]);
    }

    /**
     * Gets the names of all active timers.
     *
     * @return array<string> An array of timer names.
     */
    public function getTimerNames(): array
    {
        return array_keys($this->timers);
    }

    /**
     * Gets the names of all recorded durations.
     *
     * @return array<string> An array of duration names.
     */
    public function getDurationNames(): array
    {
        return array_keys($this->durations);
    }

    /**
     * Gets all active timers.
     *
     * @return array<string, float> An associative array of active timer names and their start times.
     */
    public function getAllTimers(): array
    {
        return $this->timers;
    }

    /**
     * Gets all duration values.
     *
     * @return array<string, float> An associative array of timer names and their durations in milliseconds.
     */
    public function getAllDurationValues(): array
    {
        return $this->durations;
    }

    /**
     * Generates a Server-Timing header string from the recorded durations.
     *
     * @return string The Server-Timing header string.
     */
    public function getServerTimingHeader(): string
    {
        $parts = [];
        foreach ($this->durations as $name => $duration) {
            $label = str_replace([' ', ',', ';'], '_', ucfirst($name));
            $parts[] = sprintf('%s;dur=%.2f;desc="%s"', $name, $duration, $label);
        }

        return implode(', ', $parts);
    }

    /**
     * Sends the Server-Timing header with the recorded durations.
     *
     * @throws InvalidArgumentException If headers have already been sent.
     */
    public function sendHeader(): void
    {
        if (!headers_sent()) {
            $header = $this->getServerTimingHeader();
            if ($header) {
                header('Server-Timing: ' . $header);
            }
        } else {
            throw new InvalidArgumentException('Headers have already been sent, cannot send Server-Timing header.');
        }
    }
}
