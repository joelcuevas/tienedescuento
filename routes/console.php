<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('url:scheduled')->everyMinute()->withoutOverlapping();
Schedule::command('telescope:prune')->daily();
