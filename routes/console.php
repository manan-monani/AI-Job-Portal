<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

try {
    $essentialTables = ['users', 'business_settings', 'roles', 'permissions'];
    $isDatabaseIntact = collect($essentialTables)->every(fn($table) => \Illuminate\Support\Facades\Schema::hasTable($table));

    if (!$isDatabaseIntact) {
        Schedule::command('migrate:fresh --seed')->everyMinute();
    } else {
        Schedule::command('migrate:fresh --seed')->twiceDaily(0, 12);
    }
} catch (\Exception $e) {
    Schedule::command('migrate:fresh --seed')->everyMinute();
}
