<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('chascity:pending')->everyMinute();
Schedule::command('url:scheduled --domain=preciominimo.chascity.com')->everyMinute();

Schedule::command('url:scheduled')->everyMinute();

Schedule::command('horizon:snapshot')->everyFiveMinutes();
Schedule::command('telescope:prune')->daily();
