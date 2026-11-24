<?php

use App\Imports\SpkWorkbookImport;
use App\Services\AlternativeSyncService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Maatwebsite\Excel\Facades\Excel;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('spk:import-alternatives', function (AlternativeSyncService $alternativeSync) {
    $path = base_path('data.xlsx');

    if (! file_exists($path)) {
        $this->error('File data.xlsx tidak ditemukan di root project.');

        return 1;
    }

    $importer = new SpkWorkbookImport;
    Excel::import($importer, $path);

    $alternatives = $importer->alternatives();
    $alternativeSync->sync($alternatives);

    $this->info('Import selesai: '.count($alternatives).' alternatif dari sheet Data.');

    return 0;
})->purpose('Sinkronkan daftar HP dari sheet Data di data.xlsx');
