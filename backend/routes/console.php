<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Schedule::command('lexflow:priorities-recalculate')->dailyAt('00:10');

Artisan::command('lexflow:ping', function () {
    $this->info('LexFlow console routes loaded.');
});
