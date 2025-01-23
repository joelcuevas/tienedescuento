<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('chascity:discover --limit=15')->everyMinute();

foreach (config('crawlers.domains') as $domain => $config) {
    Schedule::command("url:scheduled --domain={$domain} --limit={$config['allow']}")->cron("*/{$config['every']} * * * *");
}

Schedule::command('horizon:snapshot')->everyFiveMinutes();
