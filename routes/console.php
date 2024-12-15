<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('url:scheduled')->everyFiveMinutes()->withoutOverlapping();
