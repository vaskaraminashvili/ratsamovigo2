<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('app:backup-database')->dailyAt('03:00')->onOneServer();
Schedule::command('auth:clear-resets')->daily();
Schedule::command('queue:prune-failed --hours=168')->daily();
Schedule::call(fn () => DB::table('notifications')->whereNotNull('read_at')->where('read_at', '<', now()->subDays(30))->delete())
    ->name('prune-read-notifications')
    ->daily();
