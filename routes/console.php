<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('fix:urls')->everyMinute();

Schedule::command('chascity:discover --limit=15')->everyMinute();
Schedule::command('liverpool:discover --limit=25')->everyMinute();

Schedule::command('url:scheduled --domain=preciominimo.chascity.com --limit=40')->everyMinute();
Schedule::command('url:scheduled --domain=www.liverpool.com.mx --limit=25')->everyMinute();
Schedule::command('url:scheduled --domain=www.elpalaciodehierro.com --limit=25')->everyMinute();
Schedule::command('url:scheduled --domain=www.costco.com.mx --limit=50')->everyMinute();

Schedule::command('horizon:snapshot')->everyFiveMinutes();
Schedule::command('telescope:prune')->daily();
