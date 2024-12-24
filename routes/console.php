<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('chascity:pending --limit=15')->everyMinute();
Schedule::command('url:scheduled --domain=preciominimo.chascity.com --limit=15')->everyMinute();

Schedule::command('liverpool:pending --limit=50')->everyMinute();
Schedule::command('url:scheduled --domain=www.liverpool.com.mx --limit=50')->everyMinute();

Schedule::command('horizon:snapshot')->everyFiveMinutes();
Schedule::command('telescope:prune')->daily();
