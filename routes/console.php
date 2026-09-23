<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Jadwal Backup Otomatis Harian ke Local Disk & Cloudflare R2
Schedule::command('backup:run --only-db')
    ->dailyAt('02:00')
    ->timezone('Asia/Jakarta')
    ->name('daily-db-backup')
    ->withoutOverlapping();

// Pembersihan backup lama sesuai kebijakan retensi
Schedule::command('backup:clean')
    ->dailyAt('03:00')
    ->timezone('Asia/Jakarta')
    ->name('daily-backup-cleanup')
    ->withoutOverlapping();
