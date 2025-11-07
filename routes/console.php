<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command("dreamworm:send-follow-up-sms")->dailyAt("10:00");

Schedule::command('reminder:send')->dailyAt('18:00');

Schedule::command('backup:clean')->dailyAt('1:00');
Schedule::command('backup:run --only-db')->dailyAt('01:30');
