<?php

namespace App\Exports;

use App\Models\DataPlps;
use App\Models\ExportHistory;
use App\Support\DataPlpsFilter;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithCustomChunkSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Throwable;

/**
 * Export Data PLPS / MBKM ke file .xlsx.
 *
 * - FromQuery            : data diambil lewat query builder, TIDAK memakai
 *                          get()/all(); maatwebsite/excel membaca per chunk.
 * - WithCustomChunkSize  : 1.000 baris per chunk dari database (bisa dioverride
 *                          lewat config/export.php).
 * - WithMapping          : mapping baris yang ringan, tanpa query tambahan
 *                          karena semua relasi sudah di eager load.
 * - WithHeadings         : definisi header kolom.
 * - ShouldQueue          : membuat ->store()/->queue() TIDAK PERNAH menulis file
 *                          di dalam HTTP request (selalu lewat queue worker).
 *
 * Penulisan file dilakukan oleh App\Jobs\GenerateMahasiswaMbkmExport dalam satu
 * pass. Alasannya: writer bawaan package menulis ulang seluruh workbook di
 * setiap chunk job (O(n^2)), sedangkan satu pass jauh lebih cepat dengan
 * pemakaian memory yang sama. Detail pengukuran ada di docblock job tersebut.
 */
class MahasiswaMbkmExport implements FromQuery, ShouldQueue, WithCustomChunkSize, WithHeadings, WithMapping
{
    use Exportable;

    /**
     * Default jumlah baris per chunk.
     */
    public const CHUNK_SIZE = 1000;

    /**
     * Seberapa sering progress (processed_rows) ditulis ke database.
     */
    public const PROGRESS_INTERVAL = 500;

    /**
     * Relasi yang wajib di eager load, kalau tidak map() akan memicu satu
     * query per baris (N+1 problem).
     */
    public const EAGER_LOAD = [
        'program',
        'subProgram',
        'mahasiswa.prodi.fakultas',
        'kegiatan',
        'mitra',
    ];

    /**
     * Jumlah baris yang sudah dimapping pada proses ini.
     */
    protected int $processedRows = 0;

    /**
     * @param  array  $filters  Filter dashboard, lihat DataPlpsFilter::FILTER_KEYS.
     *                          Hanya scalar/array agar objek tetap serializable
     *                          ketika dikirim ke queue worker.
     * @param  int|null  $exportHistoryId  Row export_histories untuk update progress.
     */
    public function __construct(
        protected array $filters = [],
        protected ?int $exportHistoryId = null
    ) {
    }

    /**
     * Query builder (di-chunk otomatis oleh maatwebsite/excel).
     */
    public function query(): Builder
    {
        $query = DataPlps::query()
            ->with(self::EAGER_LOAD)
            // chunk()/forPage() memakai OFFSET-LIMIT: tanpa ORDER BY yang pasti
            // baris bisa terlewat atau dobel.
            ->orderBy('data_plps.id');

        return DataPlpsFilter::apply($query, $this->filters);
    }

    public function headings(): array
    {
        return [
            'Program',
            'Sub Program',
            'Fakultas',
            'Program Studi',
            'NIM',
            'Nama Mahasiswa',
            'Tahun Ajaran',
            'Semester',
            'Semester TA',
            'Kegiatan',
            'Penyelenggara',
            'Mitra',
            'Dosen Pembimbing',
            'Jumlah Konversi SKS',
        ];
    }

    /**
     * @param  DataPlps  $row
     */
    public function map($row): array
    {
        $this->trackProgress();

        return [
            $row->program->nama_program ?? '-',
            $row->subProgram->nama_sub_program ?? '-',
            $row->mahasiswa->prodi->fakultas->nama_fakultas ?? '-',
            $row->mahasiswa->prodi->nama_prodi ?? '-',
            $row->nim,
            $row->mahasiswa->nama ?? '-',
            $row->tahun_ajaran,
            $row->semester,
            $row->semester_ta,
            $row->kegiatan->nama_kegiatan ?? '-',
            $row->penyelenggara,
            $row->mitra->nama_mitra ?? '-',
            $row->dosen_pembimbing ?? '-',
            $row->sks,
        ];
    }

    public function chunkSize(): int
    {
        return (int) config('export.chunk_size', self::CHUNK_SIZE);
    }

    /**
     * Jumlah baris yang benar-benar sudah masuk ke file (dipakai job untuk
     * mengisi total_rows secara akurat, termasuk sisa batch terakhir).
     */
    public function processedCount(): int
    {
        return $this->processedRows;
    }

    /**
     * Simpan progress ke export_histories tanpa menambah satu query per baris.
     */
    protected function trackProgress(): void
    {
        $this->processedRows++;

        if (!$this->exportHistoryId || $this->processedRows % self::PROGRESS_INTERVAL !== 0) {
            return;
        }

        ExportHistory::query()
            ->whereKey($this->exportHistoryId)
            // monotonic: aman juga kalau export dijalankan lewat jalur queue
            // bawaan package (satu job per chunk, counter mulai dari 0 lagi).
            ->where('processed_rows', '<', $this->processedRows)
            ->update(['processed_rows' => $this->processedRows]);
    }

    /**
     * Dipanggil oleh queue job ketika proses export gagal.
     */
    public function failed(Throwable $exception): void
    {
        if (!$this->exportHistoryId) {
            return;
        }

        ExportHistory::find($this->exportHistoryId)?->markAsFailed($exception->getMessage());
    }
}
