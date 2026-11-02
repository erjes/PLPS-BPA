<?php

namespace App\Jobs;

use App\Exports\MahasiswaMbkmExport;
use App\Models\ExportHistory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Excel as ExcelWriterType;
use Maatwebsite\Excel\Writer;
use Throwable;

/**
 * Menulis file .xlsx hasil export di background queue.
 *
 * Kenapa memakai Writer::export() dan bukan $export->store()?
 * Karena MahasiswaMbkmExport mengimplementasikan ShouldQueue, ->store() akan
 * memecah export menjadi satu job per chunk dan SETIAP job membaca ulang lalu
 * menulis ulang seluruh workbook (O(n^2)). Untuk 16.583 baris itu butuh ~78
 * detik, sedangkan satu pass Writer::export() hanya ~9 detik dengan pemakaian
 * memory yang sama. Pembacaan database tetap di-chunk (1.000 baris) oleh
 * maatwebsite/excel lewat concern FromQuery + WithCustomChunkSize.
 */
class GenerateMahasiswaMbkmExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Satu percobaan saja: file ditulis ulang penuh, jadi retry otomatis
     * tidak memberi manfaat dan hanya menambah beban worker.
     */
    public int $tries = 1;

    /**
     * Batas waktu job (detik). Worker harus dijalankan dengan --timeout
     * yang sama atau lebih besar.
     */
    public int $timeout = 1800;

    public function __construct(
        public int $exportHistoryId,
        public array $filters = []
    ) {
    }

    public function handle(): void
    {
        $history = ExportHistory::find($this->exportHistoryId);

        if (!$history) {
            return;
        }

        $this->prepareRuntime();

        $history->markAsProcessing();

        $export = new MahasiswaMbkmExport($this->filters, $history->id);

        try {
            /** @var Writer $writer */
            $writer = app(Writer::class);

            // Menulis seluruh baris dalam satu pass, database tetap dibaca
            // per chunk (WithCustomChunkSize).
            $temporaryFile = $writer->export($export, ExcelWriterType::XLSX);

            $disk = Storage::disk($history->disk);
            $stream = $temporaryFile->readStream();

            try {
                // Stream ke disk: file hasil tidak pernah dimuat penuh ke memory.
                $disk->put($history->path, $stream);
            } finally {
                if (is_resource($stream)) {
                    fclose($stream);
                }

                $temporaryFile->delete();
            }
        } catch (Throwable $exception) {
            $history->markAsFailed($exception->getMessage());
            report($exception);

            throw $exception;
        }

        $history->markAsCompleted(
            $export->processedCount(),
            $disk->size($history->path)
        );
    }

    public function failed(?Throwable $exception): void
    {
        ExportHistory::find($this->exportHistoryId)?->markAsFailed(
            $exception?->getMessage() ?? 'Proses export gagal dijalankan.'
        );
    }

    /**
     * Worker CLI boleh memakai limit memory yang lebih besar daripada web
     * request, karena PhpSpreadsheet menyimpan seluruh cell di memory.
     */
    private function prepareRuntime(): void
    {
        if (PHP_SAPI !== 'cli') {
            return;
        }

        @ini_set('memory_limit', (string) config('export.memory_limit', '512M'));
        @set_time_limit((int) $this->timeout);
    }
}
