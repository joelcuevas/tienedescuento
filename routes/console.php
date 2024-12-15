<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('url:scheduled')->everyFiveMinutes()->withoutOverlapping();
Schedule::command('telescope:prune')->daily();
