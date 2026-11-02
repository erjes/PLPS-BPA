<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Queue Worker Otomatis lewat Scheduler
|--------------------------------------------------------------------------
|
| Project ini memakai Laravel 12, jadi konfigurasi schedule berada di file
| routes/console.php (Laravel 10 ke bawah: method schedule() pada
| app/Console/Kernel.php).
|
| Dengan baris di bawah, queue worker (termasuk job export Excel
| App\Jobs\GenerateMahasiswaMbkmExport) berjalan otomatis dari cron server
| yang memanggil `php artisan schedule:run` setiap menit — tidak perlu lagi
| `php artisan queue:work` manual di terminal.
|
| Penjelasan opsi:
| - --stop-when-empty : worker berhenti sendiri begitu antrean kosong, jadi
|                       proses tidak menggantung (aman untuk cron).
| - --tries=3         : job dicoba maksimal 3 kali sebelum masuk failed_jobs.
| - --timeout=3600    : batas 1 jam per job (export data besar).
| - --memory=512      : batas memory worker (PhpSpreadsheet butuh ruang lebih).
| - everyMinute()     : dicek setiap kali cron schedule:run dijalankan.
| - withoutOverlapping(60) : memakai cache lock. Kalau worker sebelumnya masih
|                       berjalan (export besar belum selesai), run berikutnya
|                       dilewati sehingga tidak ada dua worker paralel.
|                       Angka 60 = menit masa berlaku lock; disamakan dengan
|                       --timeout agar lock otomatis lepas bila proses mati
|                       mendadak (tidak mengunci selama default 24 jam).
|
| Catatan: pastikan CACHE_STORE memakai driver yang mendukung atomic lock
| (database/redis/file) dan cron server sudah terpasang:
|
|   * * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
|
*/
Schedule::command('queue:work --stop-when-empty --tries=3 --timeout=3600 --memory=512')
    ->everyMinute()
    ->withoutOverlapping(60)
    ->description('Queue worker (export Excel & job antrean lain)');
