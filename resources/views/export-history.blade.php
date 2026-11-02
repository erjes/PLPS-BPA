@extends('layouts.app')

@section('title', 'Riwayat Export - PLPS')

@section('styles')
    @vite(['resources/css/dashboard.css'])
@endsection

@section('content')

    <style>
        .export-page-title { font-size: 20px; font-weight: 700; color: #7B1113; margin: 0 0 18px; display: flex; align-items: center; gap: 10px; }
        .export-note { display: flex; gap: 12px; align-items: flex-start; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 14px 16px; margin-bottom: 18px; font-size: 13px; color: #475569; line-height: 1.6; }
        .export-note i { color: #2173b5; font-size: 16px; margin-top: 2px; }
        .export-table { width: 100%; border-collapse: collapse; font-size: 13px; }
        .export-table thead th { text-align: left; padding: 10px 12px; border-bottom: 2px solid #e2e8f0; font-size: 11px; text-transform: uppercase; letter-spacing: .04em; color: #64748b; white-space: nowrap; }
        .export-table tbody td { padding: 12px; border-bottom: 1px solid #f1f5f9; color: #334155; vertical-align: middle; }
        .export-table tbody tr:last-child td { border-bottom: none; }
        .export-badge { display: inline-block; padding: 4px 10px; border-radius: 999px; font-size: 11px; font-weight: 700; white-space: nowrap; }
        .export-progress { height: 6px; background: #e2e8f0; border-radius: 999px; overflow: hidden; margin-top: 6px; min-width: 110px; }
        .export-progress > span { display: block; height: 100%; background: linear-gradient(135deg, #7B1113, #A41E1E); border-radius: 999px; transition: width .3s ease; }
        .export-file { display: flex; align-items: center; gap: 8px; font-weight: 600; color: #0f766e; }
        .export-muted { color: #94a3b8; font-size: 12px; }
        .export-filters { color: #475569; font-size: 12px; line-height: 1.6; max-width: 260px; }
        .export-error { color: #b91c1c; font-size: 12px; max-width: 260px; line-height: 1.5; }
        .export-empty { text-align: center; padding: 48px 20px; color: #94a3b8; }
        .export-empty i { font-size: 34px; display: block; margin-bottom: 12px; color: #cbd5e1; }
        .export-pagination { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-top: 18px; font-size: 12px; color: #64748b; }
        .export-pagination a, .export-pagination span.disabled { padding: 7px 14px; border-radius: 8px; border: 1.5px solid #cbd5e1; color: #475569; text-decoration: none; font-weight: 600; }
        .export-pagination span.disabled { color: #cbd5e1; border-color: #e2e8f0; }
        .export-pagination a:hover { border-color: #94a3b8; }
    </style>

    <h1 class="export-page-title"><i class="fas fa-history"></i> Riwayat Export</h1>

    <div class="card" style="padding: 20px 24px;">
        <div class="export-note">
            <i class="fas fa-circle-info"></i>
            <div>
                File Excel dibuat di <strong>background queue</strong>, jadi Anda bisa menutup halaman ini.
                Selama status masih <strong>Menunggu antrean</strong> / <strong>Sedang diproses</strong>, file belum bisa diunduh.
                Halaman ini akan menyegarkan dirinya otomatis ketika masih ada export yang berjalan.
            </div>
        </div>

        @if($exports->isEmpty())
            <div class="export-empty">
                <i class="fas fa-file-excel"></i>
                <p>Belum ada riwayat export pada sesi ini.</p>
                <a href="{{ url('/dashboard') }}" class="btn btn-primary" style="text-decoration:none;">
                    <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
                </a>
            </div>
        @else
            <div style="overflow-x:auto;">
                <table class="export-table">
                    <thead>
                        <tr>
                            <th>File</th>
                            <th>Filter</th>
                            <th>Status</th>
                            <th>Baris</th>
                            <th>Ukuran</th>
                            <th>Dibuat</th>
                            <th style="text-align:right;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($exports as $export)
                            @php
                                $filterLabels = [
                                    'program_id' => 'Program',
                                    'sub_program_id' => 'Sub Program',
                                    'fakultas_id' => 'Fakultas',
                                    'prodi_id' => 'Prodi',
                                    'mitra_id' => 'Mitra',
                                    'penyelenggara' => 'Penyelenggara',
                                    'semester_ta' => 'Semester TA',
                                    'tahun_ajaran' => 'Tahun Ajaran',
                                    'search_nama' => 'Cari Nama',
                                    'search_nim' => 'Cari NIM',
                                ];

                                $activeFilters = collect($export->filters ?? [])
                                    ->filter(fn ($value) => !empty($value))
                                    ->map(function ($value, $key) use ($filterLabels) {
                                        $count = is_array($value) ? count($value) : 1;

                                        return ($filterLabels[$key] ?? $key) . ': ' . $count . ' dipilih';
                                    });
                            @endphp
                            <tr>
                                <td>
                                    <div class="export-file">
                                        <i class="fas fa-file-excel"></i>
                                        <span>{{ $export->filename }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="export-filters">
                                        @if($activeFilters->isEmpty())
                                            <span class="export-muted">Tanpa filter (semua data)</span>
                                        @else
                                            {{ $activeFilters->implode(', ') }}
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <span class="export-badge" style="{{ $export->statusStyle() }}">{{ $export->statusLabel() }}</span>
                                    @if($export->isPending())
                                        <div class="export-progress" title="{{ $export->progressPercent() }}%">
                                            <span style="width: {{ $export->progressPercent() }}%"></span>
                                        </div>
                                        @if($export->processed_rows > 0)
                                            <div class="export-muted">{{ number_format($export->processed_rows, 0, ',', '.') }} baris diproses</div>
                                        @endif
                                    @endif
                                    @if($export->isFailed() && $export->error_message)
                                        <div class="export-error" style="margin-top:6px;">{{ \Illuminate\Support\Str::limit($export->error_message, 140) }}</div>
                                    @endif
                                </td>
                                <td>
                                    @if($export->isCompleted())
                                        {{ number_format($export->total_rows, 0, ',', '.') }}
                                    @else
                                        <span class="export-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($export->isCompleted())
                                        {{ $export->humanFileSize() }}
                                    @else
                                        <span class="export-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <div>{{ $export->created_at?->format('d/m/Y H:i') }}</div>
                                    <div class="export-muted">{{ $export->created_at?->diffForHumans() }}</div>
                                </td>
                                <td style="text-align:right;">
                                    @if($export->isCompleted() && $export->fileExists())
                                        <a href="{{ route('export.download', $export) }}" class="btn btn-outline"
                                            style="color:#0f766e;border-color:#0f766e;text-decoration:none;padding:7px 14px;">
                                            <i class="fas fa-download"></i> Unduh
                                        </a>
                                    @elseif($export->isFailed())
                                        <span class="export-muted">Export gagal</span>
                                    @else
                                        <span class="export-muted"><i class="fas fa-spinner fa-spin"></i> Menunggu</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($exports->hasPages())
                <div class="export-pagination">
                    @if($exports->onFirstPage())
                        <span class="disabled"><i class="fas fa-chevron-left"></i> Sebelumnya</span>
                    @else
                        <a href="{{ $exports->previousPageUrl() }}"><i class="fas fa-chevron-left"></i> Sebelumnya</a>
                    @endif

                    <span>Halaman {{ $exports->currentPage() }} dari {{ $exports->lastPage() }}
                        ({{ number_format($exports->total(), 0, ',', '.') }} export)</span>

                    @if($exports->hasMorePages())
                        <a href="{{ $exports->nextPageUrl() }}">Berikutnya <i class="fas fa-chevron-right"></i></a>
                    @else
                        <span class="disabled">Berikutnya <i class="fas fa-chevron-right"></i></span>
                    @endif
                </div>
            @endif
        @endif
    </div>

    <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
        <a href="{{ url('/dashboard') }}" class="btn btn-outline" style="text-decoration:none;">
            <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
        </a>
        <a href="{{ route('export.history') }}" class="btn btn-primary" style="text-decoration:none;">
            <i class="fas fa-rotate"></i> Muat Ulang
        </a>
        @if(!$exports->isEmpty())
            <button type="button" class="btn btn-red" onclick="openClearHistoryModal()"
                style="margin-left:auto;background:#dc2626">
                <i class="fas fa-trash-alt"></i> Hapus Semua Riwayat
            </button>
        @endif
    </div>

    {{-- CLEAR HISTORY CONFIRMATION MODAL --}}
    <div class="edit-modal-overlay" id="clearHistoryOverlay" onmousedown="if(event.target===this)this.dataset.close='1';"
        onmouseup="if(this.dataset.close==='1' && event.target===this){closeClearHistoryModal();} this.dataset.close='0';">
        <div class="edit-modal" style="max-width:440px;text-align:center">
            <div class="edit-modal-header" style="border-bottom:none;padding-bottom:0">
                <h3 style="color:#b91c1c"><i class="fas fa-exclamation-triangle" style="color:#dc2626"></i> Konfirmasi Hapus
                    Riwayat</h3>
                <button class="edit-modal-close" onclick="closeClearHistoryModal()">&times;</button>
            </div>
            <div class="edit-modal-body" style="padding:20px 24px">
                <div
                    style="width:60px;height:60px;border-radius:50%;background:#fef2f2;color:#dc2626;display:flex;align-items:center;justify-content:center;font-size:28px;margin:0 auto 16px">
                    <i class="fas fa-trash-alt"></i>
                </div>
                <p style="font-size:15px;color:#1e293b;font-weight:600;margin-bottom:8px">Yakin ingin menghapus seluruh
                    riwayat export?</p>
                <p style="font-size:13px;color:#64748b;line-height:1.5">
                    Sebanyak <strong>{{ number_format($exports->total(), 0, ',', '.') }}</strong> riwayat export beserta
                    file Excel yang sudah dibuat akan dihapus permanen. Tindakan ini tidak dapat dibatalkan.
                </p>
            </div>
            <div class="edit-modal-footer"
                style="justify-content:center;gap:12px;background:#f8fafc;border-top:1px solid #e2e8f0;border-radius:0 0 16px 16px">
                <button class="btn btn-outline" onclick="closeClearHistoryModal()" style="padding:10px 20px">Batal</button>
                <button class="btn btn-red" id="confirmClearHistoryBtn" onclick="submitClearHistory()"
                    style="padding:10px 24px;background:#dc2626">
                    <i class="fas fa-trash-alt"></i> Ya, Hapus Semua
                </button>
            </div>
        </div>
    </div>

    {{-- Form di bawah ini hanya dikirim setelah user menekan "Ya, Hapus Semua" --}}
    <form id="clearExportHistoryForm" method="POST" action="{{ route('export.history.clear') }}" style="display:none">
        @csrf
        @method('DELETE')
    </form>

    @if($hasPendingExport)
        <script>
            // Segarkan halaman selama masih ada export yang diproses, tetapi
            // tahan dulu kalau modal konfirmasi hapus sedang terbuka.
            (function pollExportStatus() {
                setTimeout(() => {
                    const overlay = document.getElementById('clearHistoryOverlay');
                    if (overlay && overlay.classList.contains('show')) {
                        pollExportStatus();
                        return;
                    }
                    window.location.reload();
                }, 5000);
            })();
        </script>
    @endif

    <script>
        function openClearHistoryModal() {
            document.getElementById('clearHistoryOverlay').classList.add('show');
        }

        function closeClearHistoryModal() {
            document.getElementById('clearHistoryOverlay').classList.remove('show');
        }

        function submitClearHistory() {
            const btn = document.getElementById('confirmClearHistoryBtn');

            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menghapus...';
            }

            document.getElementById('clearExportHistoryForm').submit();
        }
    </script>

@endsection
