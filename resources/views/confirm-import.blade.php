@extends('layouts.app')

@section('title', 'Konfirmasi Import - PLPS')

@section('styles')
    @vite(['resources/css/confirm-import.css', 'resources/js/confirm-import.js'])
@endsection

@section('content')

<div class="confirm-card">
    <div class="confirm-header">
        <div class="confirm-header-icon"><i class="fas fa-clipboard-check"></i></div>
        <div>
            <h2>Konfirmasi Penyimpanan Data</h2>
            <p>Tahap akhir sebelum data diintegrasikan ke dalam dashboard global</p>
        </div>
    </div>

    <div class="confirm-body">
        <div class="confirm-stats">
            <div class="confirm-check"><i class="fas fa-check"></i></div>
            <div class="confirm-count">{{ number_format($rowCount) }}</div>
            <div class="confirm-label">Data valid siap disimpan</div>
        </div>

        <div class="detail-grid">
            <div class="detail-card">
                <div class="detail-icon" style="background:#fef2f2;color:#7B1113"><i class="fas fa-file-excel"></i></div>
                <div>
                    <h4>Informasi Berkas</h4>
                    <p style="margin: 4px 0 0; font-weight: 600; color:#1e293b">{{ $originalName ?? 'Data Excel' }}</p>
                    <p style="margin: 4px 0 0; font-size: 13px; color:#64748b">File berhasil diunggah dan telah melewati semua proses validasi format data.</p>
                </div>
            </div>
            
            <div class="detail-card">
                <div class="detail-icon" style="background:#f0fdf4;color:#16a34a"><i class="fas fa-check-circle"></i></div>
                <div>
                    <h4>Status Validasi</h4>
                    <p style="margin: 4px 0 0; font-weight: 600; color:#16a34a">Lolos 100%</p>
                    <p style="margin: 4px 0 0; font-size: 13px; color:#64748b">Tidak ada duplikasi data, tidak ada baris ganda, dan seluruh relasi hierarki Program-Kegiatan berstatus aman.</p>
                </div>
            </div>
        </div>
        <div class="confirm-footer">
        <a href="/input-data" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Batalkan</a>
        <form action="{{ route('input.confirm') }}" method="POST" style="display:inline" id="confirmForm">
            @csrf
            <button type="submit" class="btn btn-primary" id="confirmBtn" style="padding:10px 28px">
                <i class="fas fa-database"></i> Simpan ke Database
            </button>
        </form>
    </div>

        {{-- ─── PREVIEW TABLE ─── --}}
        @if(!empty($previewRows))
        <div class="preview-section">
            <div class="preview-section-header">
                <i class="fas fa-table" style="color:#7B1113;font-size:18px"></i>
                <h3>Preview Data</h3>
                <span class="badge-count">{{ count($previewRows) }} baris</span>
            </div>

            <div class="preview-notice">
                <i class="fas fa-eye"></i>
                <span>Menampilkan preview 50 baris pertama untuk verifikasi. Jika terdapat kesalahan data, silakan perbarui file template Excel dan unggah kembali file tersebut.</span>
            </div>

            <div class="preview-wrap">
                <table class="preview-table">
                    <thead>
                        <tr>
                            <th style="min-width:52px">Baris</th>
                            <th style="min-width:140px">Program</th>
                            <th style="min-width:140px">Sub Program</th>
                            <th style="min-width:100px">Fakultas</th>
                            <th style="min-width:160px">Prodi</th>
                            <th style="min-width:110px">NIM</th>
                            <th style="min-width:180px">Nama Mahasiswa</th>
                            <th style="min-width:100px">Tahun Ajaran</th>
                            <th style="min-width:80px">Semester</th>
                            <th style="min-width:120px">Semester TA</th>
                            <th style="min-width:160px">Program Kegiatan</th>
                            <th style="min-width:90px">Penyelenggara</th>
                            <th style="min-width:200px">Mitra</th>
                            <th style="min-width:160px">Dosen Pembimbing</th>
                            <th style="min-width:60px;text-align:center">SKS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($previewRows as $row)
                        <tr class="preview-row" style="display: none;">
                            <td><span class="row-num">{{ $row['excel_row'] }}</span></td>
                            <td>{{ $row['program'] ?: '-' }}</td>
                            <td>{{ $row['sub_program'] ?: '-' }}</td>
                            <td>{{ $row['fakultas'] ?: '-' }}</td>
                            <td>{{ $row['prodi'] ?: '-' }}</td>
                            <td class="nim-cell">{{ $row['nim'] ?: '-' }}</td>
                            <td>{{ $row['nama'] ?: '-' }}</td>
                            <td>{{ $row['tahun_ajaran'] ?: '-' }}</td>
                            <td>
                                @php $sem = strtoupper($row['semester'] ?? ''); @endphp
                                @if($sem === 'GANJIL')
                                    <span class="sem-badge sem-ganjil">GANJIL</span>
                                @elseif($sem === 'GENAP')
                                    <span class="sem-badge sem-genap">GENAP</span>
                                @else
                                    <span class="empty-cell">{{ $row['semester'] ?: '-' }}</span>
                                @endif
                            </td>
                            <td>{{ $row['semester_ta'] ?: '-' }}</td>
                            <td>{{ $row['kegiatan'] ?: '-' }}</td>
                            <td>
                                @php $pen = $row['penyelenggara'] ?? ''; @endphp
                                @if(strtolower($pen) === 'eksternal')
                                    <span class="pen-badge pen-ek">Eksternal</span>
                                @elseif(strtolower($pen) === 'internal')
                                    <span class="pen-badge pen-in">Internal</span>
                                @else
                                    <span class="empty-cell">{{ $pen ?: '-' }}</span>
                                @endif
                            </td>
                            <td title="{{ $row['mitra'] }}">{{ $row['mitra'] ?: '-' }}</td>
                            <td>{{ $row['dosen_pembimbing'] ?: '-' }}</td>
                            <td class="sks-cell">{{ $row['sks'] ?: '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="table-info" id="tableInfo" style="display:flex; justify-content:space-between; align-items:center; padding: 15px 20px; background:#fff; border: 1.5px solid #e2e8f0; border-top: none; border-radius: 0 0 12px 12px;">
                <span style="font-size:13px; color:#64748b;">Menampilkan <span id="tableShown" style="font-weight:600; color:#1e293b;">0</span> dari <span id="tableTotal" style="font-weight:600; color:#1e293b;">0</span> data</span>
                <div class="pagination-controls" style="display:flex; gap:15px; align-items:center;">
                    <span id="pageText" style="font-size:13px; font-weight:600; color:#475569">0 - 0 / 0</span>
                    <div style="display:flex; gap:5px;">
                        <button class="btn btn-outline" id="prevPageBtn" onclick="prevPage()" style="padding:6px 12px" title="Previous Page"><i class="fas fa-chevron-left"></i></button>
                        <button class="btn btn-outline" id="nextPageBtn" onclick="nextPage()" style="padding:6px 12px" title="Next Page"><i class="fas fa-chevron-right"></i></button>
                    </div>
                </div>
            </div>
        </div>
        @endif
        {{-- ─── END PREVIEW TABLE ─── --}}
    </div>

    
</div>

<!-- Progress Overlay Modal -->
<div class="modal-overlay" id="progressModal" style="display:none; z-index: 9999;">
    <div class="modal-box" style="max-width: 500px; text-align: center; padding: 36px 24px;">
        <div style="font-size: 40px; margin-bottom: 16px;" id="progressIcon">
            <i class="fas fa-database fa-spin" style="color: #7B1113;"></i>
        </div>
        <h3 style="font-size: 18px; font-weight: 700; color: #1e293b; margin-bottom: 8px;" id="progressTitle">Menyimpan ke Database...</h3>
        <p style="font-size: 13px; color: #64748b; margin-bottom: 24px;" id="progressStatus">Menyiapkan penyimpanan data...</p>
        
        <div style="background: #e2e8f0; border-radius: 999px; height: 10px; overflow: hidden; margin-bottom: 12px; position: relative;">
            <div id="progressBar" style="background: linear-gradient(135deg,#7B1113,#A41E1E); width: 0%; height: 100%; transition: width 0.2s ease-out; border-radius: 999px;"></div>
        </div>
        <div style="font-size: 14px; font-weight: 700; color: #1e293b;" id="progressPercent">0%</div>
    </div>
</div>

<!-- Generic Error Modal -->
<div class="modal-overlay" id="genericErrorModal" style="display:none;">
    <div class="modal-box">
        <div class="modal-header" style="background:linear-gradient(135deg,#ef4444,#dc2626);">
            <div style="display:flex;align-items:center;gap:10px">
                <span style="font-size:22px">⚠️</span>
                <div>
                    <div style="font-size: 16px; font-weight: 700;" id="genericErrorTitle">Terjadi Kesalahan</div>
                </div>
            </div>
            <button class="close-btn" type="button">✕</button>
        </div>
        <div class="modal-body">
            <div style="padding:20px; text-align:center; color:#374151; line-height:1.6" id="genericErrorContent">
                -
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-red" type="button">Tutup</button>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    window.ConfirmImportData = {
        tempPath: '{!! session("last_import_path") !!}',
        previewTotalRows: {{ $rowCount ?? 0 }},
        processChunkRoute: '{{ route("input.process-chunk") }}',
        csrfToken: '{{ csrf_token() }}',
        inputDataUrl: '{{ url("/input-data") }}'
    };
</script>
@endsection
