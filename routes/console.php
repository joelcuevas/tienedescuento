<?php

use Illuminate\Support\Facades\Schedule;

// Schedule::command('chascity:pending')->everyMinute();
// Schedule::command('url:scheduled --domain=preciominimo.chascity.com --limit=25')->everyMinute();

Schedule::command('url:scheduled --limit=50')->everyMinute();

Schedule::command('horizon:snapshot')->everyFiveMinutes();
Schedule::command('telescope:prune')->daily();
