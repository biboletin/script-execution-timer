<?php

use Biboletin\ScriptExecutionTimer\ScriptExecutionTimer;

include __DIR__ . '/vendor/autoload.php';

$timer = new ScriptExecutionTimer();

$timer->start('test1');

// Simulate some processing time
// usleep(500000); // 0.5 seconds
$timer->stop('test1');
$timer->start('test2');
// Simulate some more processing time
usleep(300000); // 0.3 seconds

// Simulate some more memory usage
for ($i = 0; $i < 100000; $i++) {
    $array[] = str_repeat('a', 512); // Allocate 1KB of memory
}

$timer->stop('test2');

$timer->sendHeader();
echo "Timer durations:\n";
var_dump($timer->getAllDurations());
//
// dd(
//     $timer->getDuration('test1'), // Should return the duration of 'test1' in milliseconds
//     $timer->getDuration('test2'), // Should return the duration of 'test2' in milliseconds
//     $timer->getAllDurations(), // Should return an array with all durations
//     $timer->hasTimer('test1'), // Should return true
//     $timer->hasTimer('test2'), // Should return true
//     $timer->getTimerNames() // Should return an array with names of active timers
// );
