<?php

function console_log(string $log)
{
    $console = new Symfony\Component\Console\Output\ConsoleOutput;
    $console->writeln($log);
}

function cloudwatch_log(string $log, array $context = [])
{
    \Log::channel('cloudwatch')->info($log, $context);
}

function start_profiling(string $log)
{
    if (! config('logging.profiler')) {
        return;
    }

    $now = microtime(true);

    $GLOBALS['profiling_session'] = uniqid();
    $GLOBALS['profiling_start'] = $now;
    $GLOBALS['profiling_previous'] = $now;

    cloudwatch_log($log.' - Profiling started', [
        'session' => $GLOBALS['profiling_session'],
    ]);
}

function profile(string $log)
{
    if (! config('logging.profiler')) {
        return;
    }

    $now = microtime(true);

    cloudwatch_log($log, [
        'session' => $GLOBALS['profiling_session'],
        'stopwatch' => round($now - $GLOBALS['profiling_start'], 3),
        'increment' => round($now - $GLOBALS['profiling_previous'], 3),
    ]);

    $GLOBALS['profiling_previous'] = $now;
}
