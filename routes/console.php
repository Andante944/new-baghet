<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Имена и цены продажи багета ведёт etichete-crm (источник истины).
// Раз в сутки подтягиваем их по sku; без настроенного URL команда не запускается.
Schedule::command('baghet:sync-crm-catalog')
    ->dailyAt('04:30')
    ->withoutOverlapping()
    ->when(fn () => (string) config('services.crm.catalog_url') !== '');
