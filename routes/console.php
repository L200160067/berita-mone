<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// NOTE: Scheduler is NOT used — server is CWP Shared Hosting with no cron access.
// Publishing is handled manually via the Filament Admin publish toggle.
