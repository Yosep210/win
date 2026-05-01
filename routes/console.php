<?php

use Database\Seeders\LegacyMasterDataSeeder;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('legacy:import-masterdata', function () {
    require_once base_path('database/seeders/LegacyMasterDataSeeder.php');

    $seeder = new LegacyMasterDataSeeder;
    $seeder->setContainer(app())->setCommand($this);
    $seeder->run();
})->purpose('Import legacy master data from pengenalan_dbs.sql');
