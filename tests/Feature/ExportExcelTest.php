<?php

namespace Tests\Feature;

use App\Jobs\GenerateMahasiswaMbkmExport;
use App\Models\ExportHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ExportExcelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Minimal dataset: 3 baris data_plps dengan relasi lengkap.
     */
    private function seedData(int $rows = 3): void
    {
        $now = now();

        $fakultasId = DB::table('fakultas')->insertGetId([
            'nama_fakultas' => 'Fakultas Teknik',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $prodiId = DB::table('prodis')->insertGetId([
            'nama_prodi' => 'Teknik Informatika',
            'fakultas_id' => $fakultasId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $programId = DB::table('programs')->insertGetId([
            'nama_program' => 'MBKM',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $subProgramId = DB::table('sub_programs')->insertGetId([
            'nama_sub_program' => 'Magang',
            'program_id' => $programId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $kegiatanId = DB::table('kegiatans')->insertGetId([
            'nama_kegiatan' => 'Magang Industri',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $mitraId = DB::table('mitras')->insertGetId([
            'nama_mitra' => 'PT Contoh',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        for ($i = 1; $i <= $rows; $i++) {
            DB::table('mahasiswas')->insert([
                'nim' => '22115000' . $i,
                'nama' => 'Mahasiswa ' . $i,
                'prodi_id' => $prodiId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('data_plps')->insert([
                'program_id' => $programId,
                'sub_program_id' => $subProgramId,
                'nim' => '22115000' . $i,
                'kegiatan_id' => $kegiatanId,
                'mitra_id' => $mitraId,
                'sks' => 20,
                'semester' => 'GANJIL',
                'tahun_ajaran' => '2025/2026',
                'semester_ta' => '2025/2026 GANJIL',
                'penyelenggara' => 'Eksternal',
                'dosen_pembimbing' => 'Dosen ' . $i,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function test_export_excel_is_dispatched_to_the_queue_and_tracked(): void
    {
        Queue::fake();
        $this->seedData(3);

        $response = $this->postJson('/api/export-excel');

        $response->assertStatus(202)
            ->assertJsonPath('success', true)
            ->assertJsonPath('export.status', ExportHistory::STATUS_QUEUED);

        Queue::assertPushed(GenerateMahasiswaMbkmExport::class);

        $this->assertDatabaseCount('export_histories', 1);

        $history = ExportHistory::first();

        $this->assertSame(ExportHistory::STATUS_QUEUED, $history->status);
        $this->assertSame(3, $history->total_rows);
        $this->assertSame('xlsx', $history->format);
        $this->assertStringStartsWith('exports/data_mahasiswa_mbkm_', $history->path);
        $this->assertSame([$history->id], session('export_history_ids'));
    }

    public function test_export_job_writes_the_file_and_marks_the_record_completed(): void
    {
        Storage::fake('public');
        $this->seedData(3);

        // QUEUE_CONNECTION=sync in phpunit.xml, jadi job berjalan langsung.
        $this->postJson('/api/export-excel')->assertStatus(202);

        $history = ExportHistory::first()->refresh();

        $this->assertSame(ExportHistory::STATUS_COMPLETED, $history->status);
        $this->assertSame(3, $history->total_rows);
        $this->assertSame(3, $history->processed_rows);
        $this->assertNotNull($history->completed_at);
        $this->assertGreaterThan(0, $history->file_size);
        Storage::disk('public')->assertExists($history->path);
    }

    public function test_completed_export_can_be_downloaded_from_the_history_page(): void
    {
        Storage::fake('public');
        $this->seedData(1);

        $this->postJson('/api/export-excel')->assertStatus(202);

        $history = ExportHistory::first()->refresh();

        $this->withSession(['export_history_ids' => [$history->id]])
            ->get(route('export.download', $history))
            ->assertOk()
            ->assertDownload($history->filename);
    }

    public function test_queued_export_cannot_be_downloaded_yet(): void
    {
        Storage::fake('public');
        Queue::fake();
        $this->seedData(1);

        $this->postJson('/api/export-excel')->assertStatus(202);

        $history = ExportHistory::first();

        $this->withSession(['export_history_ids' => [$history->id]])
            ->get(route('export.download', $history))
            ->assertStatus(409);
    }

    public function test_history_page_lists_exports_from_the_current_session(): void
    {
        Queue::fake();
        $this->seedData(1);

        $this->postJson('/api/export-excel')->assertStatus(202);

        $history = ExportHistory::first();

        $this->get(route('export.history'))
            ->assertOk()
            ->assertSee('Riwayat Export')
            ->assertSee($history->filename)
            ->assertSee($history->statusLabel());
    }
}
