<?php

function console(string $output) 
{
    $console = new Symfony\Component\Console\Output\ConsoleOutput;
    $console->writeln($output);
}

function start_profiling()
{
    $GLOBALS['profiling'] = microtime(true);
}

function profile(string $reference)
{
    console($reference . ' ' . round(microtime(true) -  $GLOBALS['profiling'], 3));
}