<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Export Disk & Folder
    |--------------------------------------------------------------------------
    |
    | Disk penyimpanan hasil export. File disimpan di sub folder "exports"
    | (lihat DataPlpsController::exportExcel()) dan diunduh lewat route
    | export.download, jadi disk tidak harus public.
    |
    */

    'disk' => env('EXPORT_DISK', 'public'),

    /*
    |--------------------------------------------------------------------------
    | Chunk Size
    |--------------------------------------------------------------------------
    |
    | Jumlah baris yang dibaca dari database per chunk. Turunkan bila query
    | per baris berat, naikkan bila database berada di host yang sama.
    |
    */

    'chunk_size' => (int) env('EXPORT_CHUNK_SIZE', 1000),

    /*
    |--------------------------------------------------------------------------
    | Memory Limit Queue Worker
    |--------------------------------------------------------------------------
    |
    | PhpSpreadsheet menyimpan seluruh cell di memori, jadi worker export
    | butuh limit lebih besar daripada worker biasa. Nilai ini di-set lewat
    | ini_set() di dalam job (hanya saat berjalan di CLI / queue worker).
    |
    */

    'memory_limit' => env('EXPORT_MEMORY_LIMIT', '512M'),

    /*
    |--------------------------------------------------------------------------
    | Retensi File Export
    |--------------------------------------------------------------------------
    |
    | Umur file export (hari) sebelum boleh dibersihkan oleh cron/command.
    |
    */

    'retention_days' => (int) env('EXPORT_RETENTION_DAYS', 7),

];
