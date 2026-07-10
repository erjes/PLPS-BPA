@extends('layouts.app')

@section('title', 'Data Input - PLPS')

@section('styles')
    @vite(['resources/css/input.css'])
@endsection

@section('content')

<h1 class="page-title">Data Input & Template Excel</h1>
<br><br>

{{-- ERROR MODAL --}}
<div class="modal-overlay" id="errorModal" style="{{ session('import_errors') ? 'display:flex' : 'display:none' }}">
    <div class="modal-box">
        <div class="modal-header">
            <div style="display:flex;align-items:center;gap:10px">
                <span style="font-size:22px">⚠️</span>
                <div>
                    <div style="font-size:16px;font-weight:700">Validasi Gagal</div>
                    <div style="font-size:12px;opacity:.9" id="errorCountText">{{ session('import_errors') ? count(session('import_errors')) : 0 }} error ditemukan</div>
                </div>
            </div>
            <button class="close-btn" onclick="document.getElementById('errorModal').style.display='none'">✕</button>
        </div>
        <div class="modal-body">
            <table class="modal-table">
                <thead><tr><th style="width:90px">Baris</th><th>Keterangan Error</th></tr></thead>
                <tbody id="errorTableBody">
                @if(session('import_errors'))
                    @foreach(session('import_errors') as $error)
                        @php
                            $parts = explode(': ', $error, 2);
                            $lineNum = str_replace('Baris ', '', $parts[0] ?? '');
                            $message = $parts[1] ?? $error;
                            $isSimilarity = str_contains($error, 'Kemungkinan typo') || str_contains($error, 'kemiripan');
                        @endphp
                        <tr style="{{ $isSimilarity ? 'background:#fffbeb' : '' }}">
                            <td><span class="error-badge" style="{{ $isSimilarity ? 'background:#fef3c7;color:#b45309' : '' }}">Baris {{ $lineNum }}</span></td>
                            <td style="color:#374151;line-height:1.5">
                                @if($isSimilarity)<i class="fas fa-exclamation-triangle" style="color:#d97706;margin-right:4px"></i>@endif
                                {{ $message }}
                            </td>
                        </tr>
                    @endforeach
                @endif
                </tbody>
            </table>
        </div>
        <div class="modal-footer">
            <button class="btn btn-red" onclick="document.getElementById('errorModal').style.display='none'">Tutup</button>
        </div>
    </div>
</div>

<div class="modal-overlay" id="genericErrorModal" style="{{ session('error') ? 'display:flex' : 'display:none' }}">
    <div class="modal-box">
        <div class="modal-header" style="background:linear-gradient(135deg,#ef4444,#dc2626);">
            <div style="display:flex;align-items:center;gap:10px">
                <span style="font-size:22px">⚠️</span>
                <div>
                    <div style="font-size:16px;font-weight:700" id="genericErrorTitle">Terjadi Kesalahan</div>
                </div>
            </div>
            <button class="close-btn" onclick="document.getElementById('genericErrorModal').style.display='none'">✕</button>
        </div>
        <div class="modal-body">
            <div style="padding:20px; text-align:center; color:#374151; line-height:1.6" id="genericErrorContent">
                {{ session('error') }}
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-red" onclick="document.getElementById('genericErrorModal').style.display='none'">Tutup</button>
        </div>
    </div>
</div>

{{-- TEMPLATE INFO + FIELD TABLE --}}
<div class="template-grid">
    {{-- Left: Info Card --}}
    <div class="info-card">
        <div class="info-card-title"><i class="fas fa-info-circle"></i> Informasi Template</div>
        <div class="info-note">
            <h4>Yang harus diperhatikan:</h4>
            <ul>
                <li>Nama sheet dan format template tidak boleh diubah.</li>
                <li>Format data harus sesuai seperti text, number, year, dll</li>
                <li>Baris pertama (header) akan otomatis di-skip, jadi pastikan masukkan data mulai dari baris kedua.</li>
                <li>Data duplikat akan otomatis terdeteksi.</li>
                <li>Download template Excel untuk memastikan format data sesuai.</li>
            </ul>
        </div>
        <a href="/input-data/template" class="download-btn">
            <i class="fas fa-download"></i> Download Template Excel
        </a>
    </div>

    {{-- Right: Field Table --}}
    <div class="field-table-card">
        <table class="field-table">
            <thead><tr><th>Field</th><th>Tipe / Format</th><th>Catatan</th></tr></thead>
            <tbody>
                <tr><td>Program</td><td><span class="type-badge type-text">Text</span></td><td>Nama program MBKM (e.g. Asistensi Mengajar)</td></tr>
                <tr><td>Sub Program</td><td><span class="type-badge type-text">Text</span></td><td>Nama sub program (e.g. Kampus Mengajar)</td></tr>
                <tr><td>Fakultas</td><td><span class="type-badge type-text">Text</span></td><td>Harus sesuai referensi fakultas di Telyu</td></tr>
                <tr><td>Program Studi</td><td><span class="type-badge type-text">Text</span></td><td>Nama prodi (e.g. S1 Informatika)</td></tr>
                <tr><td>NIM</td><td><span class="type-badge type-number">Number</span></td><td>Identitas mahasiswa; perlu unik, valid & tidak duplikat</td></tr>
                <tr><td>Nama Mahasiswa</td><td><span class="type-badge type-text">Text</span></td><td>Nama lengkap mahasiswa</td></tr>
                <tr><td>Tahun Ajaran</td><td><span class="type-badge type-year">Year</span></td><td>Contoh: 2020/2021</td></tr>
                <tr><td>Semester</td><td><span class="type-badge type-enum">Enum</span></td><td>GANJIL atau GENAP</td></tr>
                <tr><td>Semester TA</td><td><span class="type-badge type-text">Text</span></td><td>Contoh: 2020/2021 S2</td></tr>
                <tr><td>Program Kegiatan</td><td><span class="type-badge type-text">Text</span></td><td>Nama kegiatan MBKM</td></tr>
                <tr><td>Penyelenggara</td><td><span class="type-badge type-enum">Enum</span></td><td>Eksternal atau Internal</td></tr>
                <tr><td>Mitra</td><td><span class="type-badge type-text">Text</span></td><td>Nama instansi mitra</td></tr>
                <tr><td>Dosen Pembimbing</td><td><span class="type-badge type-text">Text</span></td><td>Nama dosen pembimbing (opsional)</td></tr>
                <tr><td>Jumlah SKS</td><td><span class="type-badge type-number">Number</span></td><td>Jumlah SKS konversi</td></tr>
            </tbody>
        </table>
    </div>
</div>

{{-- UPLOAD ZONE --}}
<div class="upload-section">
    <form action="{{ route('input.validate') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
        @csrf
        <div class="upload-zone" id="uploadZone">
            <div class="upload-zone-icon"><i class="fas fa-cloud-upload-alt"></i></div>
            <h3>Tarik & Letakkan File Excel Di Sini</h3>
            <p>Format didukung: .xlsx, .csv</p>
            <button type="button" class="upload-zone-btn" onclick="document.getElementById('fileInput').click()">
                <i class="fas fa-folder-open"></i> Pilih File dari Komputer
            </button>
            <input type="file" id="fileInput" name="file" accept=".xlsx,.csv" required>
        </div>

        <div class="upload-progress" id="uploadProgress">
            <div class="progress-info">
                <div class="file-icon"><i class="fas fa-file-excel"></i></div>
                <div class="file-details">
                    <div class="file-name" id="fileName">-</div>
                    <div class="file-size" id="fileSize">-</div>
                </div>
                <button type="button" style="background:none;border:none;color:#94a3b8;cursor:pointer;font-size:16px" onclick="clearFile()"><i class="fas fa-times"></i></button>
            </div>
            <div class="upload-actions">
                <button type="button" class="btn btn-outline" onclick="clearFile()">Batal</button>
                <button type="submit" class="btn btn-primary" id="uploadBtn">
                    Validasi Data
                </button>
            </div>
        </div>
    </form>
</div>

{{-- HISTORY --}}
<div class="card">
    <div class="history-header">
        <h3><i class="fas fa-history" style="color:#7B1113;margin-right:8px"></i>Riwayat Upload Terakhir</h3>
    </div>

    @if($histories->isEmpty())
        <div class="history-empty">
            <i class="fas fa-inbox" style="font-size:32px;display:block;margin-bottom:10px"></i>
            Belum ada riwayat upload
        </div>
    @else
    <div style="overflow-x:auto">
        <table class="history-table">
            <thead>
                <tr>
                    <th>Nama File</th>
                    <th>Tanggal & Waktu</th>
                    <th>Diunggah Oleh</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($histories as $h)
                <tr>
                    <td><i class="fas fa-file-excel" style="color:#16a34a;margin-right:6px"></i>{{ $h->filename }}</td>
                    <td>{{ $h->created_at->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') }}</td>
                    <td>{{ $h->admin->username ?? '-' }}</td>
                    <td><span class="status-success"><i class="fas fa-check-circle"></i> Berhasil ({{ number_format($h->rows_count) }} baris)</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>

<!-- Progress Overlay Modal -->
<div class="modal-overlay" id="progressModal" style="display:none; z-index: 9999;">
    <div class="modal-box" style="max-width: 500px; text-align: center; padding: 36px 24px;">
        <div style="font-size: 40px; margin-bottom: 16px;" id="progressIcon">
            <i class="fas fa-spinner fa-spin" style="color: #7B1113;"></i>
        </div>
        <h3 style="font-size: 18px; font-weight: 700; color: #1e293b; margin-bottom: 8px;" id="progressTitle">Memvalidasi Data...</h3>
        <p style="font-size: 13px; color: #64748b; margin-bottom: 24px;" id="progressStatus">Mengunggah berkas ke server...</p>
        
        <div style="background: #e2e8f0; border-radius: 999px; height: 10px; overflow: hidden; margin-bottom: 12px; position: relative;">
            <div id="progressBar" style="background: linear-gradient(135deg,#7B1113,#A41E1E); width: 0%; height: 100%; transition: width 0.2s ease-out; border-radius: 999px;"></div>
        </div>
        <div style="font-size: 14px; font-weight: 700; color: #1e293b;" id="progressPercent">0%</div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    window.InputConfig = {
        csrfToken: '{{ csrf_token() }}',
        uploadRoute: '{{ route("input.upload") }}',
        processChunkRoute: '{{ route("input.process-chunk") }}',
        confirmShowRoute: '{{ route("input.confirm.show") }}'
    };
</script>
@vite(['resources/js/input.js'])
@endsection
