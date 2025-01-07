<?php

function country()
{
    return match(request()->countryCode) {
        'mx' => 'México',
    };
}

function mmyy()
{
    return ucwords(now()->translatedFormat('F Y'));
}

function logger_console(string $log)
{
    $console = new Symfony\Component\Console\Output\ConsoleOutput;
    $console->writeln($log);
}

function logger_cloudwatch(string $log, array $context = [])
{
    \Log::channel('cloudwatch')->info($log, $context);
}

function logger_dashboard(string $log, array $context = [])
{
    if (! config('logging.dashboard')) {
        return;
    }

    \Log::channel('dashboard')->info($log, $context);
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

    logger_cloudwatch($log.' - Profiling started', [
        'session' => $GLOBALS['profiling_session'],
    ]);
}

function profile(string $log)
{
    if (! config('logging.profiler')) {
        return;
    }

    $now = microtime(true);

    logger_cloudwatch($log, [
        'session' => $GLOBALS['profiling_session'],
        'stopwatch' => round($now - $GLOBALS['profiling_start'], 3),
        'increment' => round($now - $GLOBALS['profiling_previous'], 3),
    ]);

    $GLOBALS['profiling_previous'] = $now;
}
