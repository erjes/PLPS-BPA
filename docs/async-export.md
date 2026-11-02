# Asynchronous Excel Export

Export `.xlsx` pada dashboard sekarang dijalankan di background queue
(`maatwebsite/excel` + `ShouldQueue`), bukan lagi di dalam HTTP request.
Ini menghilangkan error HTTP 500 (memory limit / execution timeout) saat
data lebih dari ±7.000 baris.

## Alur

1. User klik **Export → Excel** pada dashboard.
2. `DataPlpsController::exportExcel()` membuat row di tabel `export_histories`
   (status `queued`, `total_rows` dihitung sekali untuk progress bar), lalu
   men- dispatch `App\Jobs\GenerateMahasiswaMbkmExport($history->id, $filters)`.
3. Job berjalan di queue worker: status menjadi `processing`, lalu
   `MahasiswaMbkmExport` (FromQuery + WithCustomChunkSize) menulis file dalam
   satu pass — database dibaca per 1.000 baris, file di-stream ke disk.
   Progress (`processed_rows`) di-update setiap 500 baris.
4. Job menandai row menjadi `completed` beserta `total_rows` dan `file_size`;
   bila gagal, status menjadi `failed` + pesan error (tidak ada file rusak yang
   bisa diunduh).
5. File bisa diunduh dari halaman **Riwayat Export** (`/export-history`) atau
   `GET /export-history/{id}/download`.

Status yang mungkin: `queued` → `processing` → `completed` / `failed`.

> Catatan: `MahasiswaMbkmExport` tetap mengimplementasikan `ShouldQueue`
> (sesuai spesifikasi), jadi `(new MahasiswaMbkmExport($filters))->store(...)`
> juga aman dipanggil langsung. Namun writer bawaan package membuat satu job per
> chunk dan **menulis ulang seluruh workbook** di setiap job (O(n^2)); untuk
> 16.583 baris itu ~78 detik, sedangkan jalur job aplikasi ini ~9 detik dengan
> peak memory yang sama. Karena itu controller memakai job tersebut.

## Menjalankan queue worker

Ada dua cara. **Cara default aplikasi ini: Laravel Scheduler** — `queue:work`
tidak perlu dijalankan manual.

### A. Laravel Scheduler + cron (konfigurasi aktif)

`routes/console.php` (Laravel 11/12; Laravel 10 ke bawah: method `schedule()`
pada `app/Console/Kernel.php`):

```php
Schedule::command('queue:work --stop-when-empty --tries=3 --timeout=3600 --memory=512')
    ->everyMinute()
    ->withoutOverlapping(60);
```

Satu baris cron di server/cPanel:

```cron
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

Setiap menit `schedule:run` menyalakan worker; `--stop-when-empty` mematikan
worker lagi setelah antrean kosong, dan `withoutOverlapping(60)` mencegah dua
worker berjalan bersamaan selagi export besar belum selesai. Prasyarat:
`CACHE_STORE` harus `database`/`redis`/`file` (dipakai untuk cache lock).

Verifikasi: `php artisan schedule:list` dan `php artisan schedule:run`.

Local development: `php artisan schedule:work` (scheduler berjalan terus) atau
`composer dev` seperti biasa.

### B. Worker permanen via Supervisor (trafik tinggi / multi-server)

```ini
[program:plps-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/plps-bpa/artisan queue:work redis --sleep=3 --tries=3 --timeout=3600 --memory=512
autostart=true
autorestart=true
numprocs=2
user=www-data
redirect_stderr=true
stdout_logfile=/var/www/plps-bpa/storage/logs/worker.log
stopwaitsecs=3600
```

Setelah deploy:

```bash
php artisan queue:restart   # worker memuat ulang kode
```

## Konfigurasi environment

```env
# local
QUEUE_CONNECTION=database

# production (disarankan)
QUEUE_CONNECTION=redis
REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
```

Catatan penting:

- `QUEUE_CONNECTION=sync` akan membuat export kembali berjalan synchronous
  (di dalam request) sehingga masalah timeout bisa muncul lagi. Gunakan
  `database` atau `redis`.
- Variabel opsional export: `EXPORT_DISK` (default `public`),
  `EXPORT_CHUNK_SIZE` (default `1000`), `EXPORT_MEMORY_LIMIT` (default `512M`),
  `EXPORT_RETENTION_DAYS` (default `7`) — lihat `config/export.php`.
- `--retry_after` pada `config/queue.php` harus lebih besar dari `--timeout`
  worker, kalau tidak job bisa dieksekusi dua kali.
- File disimpan di disk `public` (`storage/app/public/exports`). Untuk produksi
  jalankan `php artisan storage:link` bila file juga ingin diakses langsung
  lewat URL publik; download lewat halaman Riwayat Export tidak memerlukannya.
- Bersihkan file lama secara berkala (misal cron harian):
  `find storage/app/public/exports -type f -mtime +7 -delete`

## Tuning memory

- Query database dibaca per chunk 1.000 baris (`MahasiswaMbkmExport::CHUNK_SIZE`).
- Relasi di- eager load (`program`, `subProgram`, `mahasiswa.prodi.fakultas`,
  `kegiatan`, `mitra`) sehingga tidak ada N+1 query per baris.
- Batas memory terbesar tetap berasal dari PhpSpreadsheet yang menyimpan cell
  di memori. Untuk kebutuhan >100.000 baris, pertimbangkan format CSV
  (streaming) atau naikkan `--memory` worker.
