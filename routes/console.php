<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Capture Horizon queue metrics for the dashboard graphs.
Schedule::command('horizon:snapshot')->everyFiveMinutes();

// Keep the failed_jobs table and stored PDFs from growing unbounded.
Schedule::command('queue:prune-failed --hours=168')->daily();
Schedule::command('funds:prune-pdf-exports')->daily();
