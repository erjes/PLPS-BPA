@extends('layouts.app')

@section('head')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endsection

@section('styles')
    @vite(['resources/css/dashboard.css'])
@endsection

@section('content')

@if(session('error'))
<div class="card" style="background:#fef2f2;border:1px solid #fecaca;color:#991b1b">{!! nl2br(session('error')) !!}</div>
@endif

@if(!($hasData ?? false))
{{-- EMPTY STATE --}}
<div class="card">
    <div class="empty-state">
        <i class="fas fa-database"></i>
        <p>Belum ada data</p>
    </div>
</div>
@else

{{-- FILTER --}}
<div class="card" id="filterCard">
    <div class="chart-title"><i class="fas fa-filter"></i> Filter Data</div>
    <form method="GET" action="/dashboard" id="filterForm">
        <div class="filter-grid">

            {{-- Program --}}
            <div class="ms-dropdown" data-name="program_id" data-label="Program">
                <div class="ms-trigger" onclick="togglePanel(this)">
                    <span class="ms-label">Semua Program</span>
                    <span class="ms-arrow"><i class="fas fa-chevron-down"></i></span>
                </div>
                <div class="ms-panel">
                    <div class="ms-panel-header">
                        <label><input type="checkbox" class="ms-select-all" onchange="toggleAll(this)"> Pilih Semua</label>
                    </div>
                    <div class="ms-search"><input type="text" placeholder="Cari program..." oninput="searchOptions(this)"></div>
                    <div class="ms-options">
                        @foreach($programs as $p)
                        @php $isChecked = is_array(request('program_id')) && in_array($p->id, request('program_id')); @endphp
                        <label class="ms-option" data-id="{{ $p->id }}" data-initial-count="{{ $p->total }}">
                            <input type="checkbox" name="program_id[]" value="{{ $p->id }}" {{ $isChecked ? 'checked' : '' }} onchange="updateDropdown(this)">
                            <span>{{ $p->nama_program }}</span>
                            <span class="ms-count">{{ $p->total }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Sub Program --}}
            <div class="ms-dropdown" data-name="sub_program_id" data-label="Sub Program">
                <div class="ms-trigger" onclick="togglePanel(this)">
                    <span class="ms-label">Semua Sub Program</span>
                    <span class="ms-arrow"><i class="fas fa-chevron-down"></i></span>
                </div>
                <div class="ms-panel">
                    <div class="ms-panel-header">
                        <label><input type="checkbox" class="ms-select-all" onchange="toggleAll(this)"> Pilih Semua</label>
                    </div>
                    <div class="ms-search"><input type="text" placeholder="Cari sub program..." oninput="searchOptions(this)"></div>
                    <div class="ms-options">
                        @foreach($subPrograms as $sp)
                        @php $isChecked = is_array(request('sub_program_id')) && in_array($sp->id, request('sub_program_id')); @endphp
                        <label class="ms-option" data-id="{{ $sp->id }}" data-initial-count="{{ $sp->total }}">
                            <input type="checkbox" name="sub_program_id[]" value="{{ $sp->id }}" {{ $isChecked ? 'checked' : '' }} onchange="updateDropdown(this)">
                            <span>{{ $sp->nama_sub_program }}</span>
                            <span class="ms-count">{{ $sp->total }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Fakultas --}}
            <div class="ms-dropdown" data-name="fakultas_id" data-label="Fakultas">
                <div class="ms-trigger" onclick="togglePanel(this)">
                    <span class="ms-label">Semua Fakultas</span>
                    <span class="ms-arrow"><i class="fas fa-chevron-down"></i></span>
                </div>
                <div class="ms-panel">
                    <div class="ms-panel-header">
                        <label><input type="checkbox" class="ms-select-all" onchange="toggleAll(this)"> Pilih Semua</label>
                    </div>
                    <div class="ms-search"><input type="text" placeholder="Cari fakultas..." oninput="searchOptions(this)"></div>
                    <div class="ms-options">
                        @foreach($fakultas as $f)
                        @php $isChecked = is_array(request('fakultas_id')) && in_array($f->id, request('fakultas_id')); @endphp
                        <label class="ms-option" data-id="{{ $f->id }}" data-initial-count="{{ $f->total }}">
                            <input type="checkbox" name="fakultas_id[]" value="{{ $f->id }}" {{ $isChecked ? 'checked' : '' }} onchange="updateDropdown(this)">
                            <span>{{ $f->nama_fakultas }}</span>
                            <span class="ms-count">{{ $f->total }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Prodi --}}
            <div class="ms-dropdown" data-name="prodi_id" data-label="Prodi">
                <div class="ms-trigger" onclick="togglePanel(this)">
                    <span class="ms-label">Semua Prodi</span>
                    <span class="ms-arrow"><i class="fas fa-chevron-down"></i></span>
                </div>
                <div class="ms-panel">
                    <div class="ms-panel-header">
                        <label><input type="checkbox" class="ms-select-all" onchange="toggleAll(this)"> Pilih Semua</label>
                    </div>
                    <div class="ms-search"><input type="text" placeholder="Cari prodi..." oninput="searchOptions(this)"></div>
                    <div class="ms-options">
                        @foreach($prodi as $pr)
                        @php $isChecked = is_array(request('prodi_id')) && in_array($pr->id, request('prodi_id')); @endphp
                        <label class="ms-option" data-id="{{ $pr->id }}" data-initial-count="{{ $pr->total }}">
                            <input type="checkbox" name="prodi_id[]" value="{{ $pr->id }}" {{ $isChecked ? 'checked' : '' }} onchange="updateDropdown(this)">
                            <span>{{ $pr->nama_prodi }}</span>
                            <span class="ms-count">{{ $pr->total }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Penyelenggara --}}
            <div class="ms-dropdown" data-name="penyelenggara" data-label="Penyelenggara">
                <div class="ms-trigger" onclick="togglePanel(this)">
                    <span class="ms-label">Semua Penyelenggara</span>
                    <span class="ms-arrow"><i class="fas fa-chevron-down"></i></span>
                </div>
                <div class="ms-panel">
                    <div class="ms-panel-header">
                        <label><input type="checkbox" class="ms-select-all" onchange="toggleAll(this)"> Pilih Semua</label>
                    </div>
                    <div class="ms-options">
                        @php
                            $penyelenggaraValues = (array) request('penyelenggara', []);
                            $countEksternal = \App\Models\DataPlps::where('penyelenggara', 'Eksternal')->count();
                            $countInternal = \App\Models\DataPlps::where('penyelenggara', 'Internal')->count();
                        @endphp
                        <label class="ms-option" data-id="Eksternal" data-initial-count="{{ $countEksternal }}">
                            <input type="checkbox" name="penyelenggara[]" value="Eksternal" {{ in_array('Eksternal', $penyelenggaraValues) ? 'checked' : '' }} onchange="updateDropdown(this)">
                            <span>Eksternal</span>
                            <span class="ms-count">{{ $countEksternal }}</span>
                        </label>
                        <label class="ms-option" data-id="Internal" data-initial-count="{{ $countInternal }}">
                            <input type="checkbox" name="penyelenggara[]" value="Internal" {{ in_array('Internal', $penyelenggaraValues) ? 'checked' : '' }} onchange="updateDropdown(this)">
                            <span>Internal</span>
                            <span class="ms-count">{{ $countInternal }}</span>
                        </label>
                    </div>
                </div>
            </div>

            {{-- Mitra --}}
            <div class="ms-dropdown" data-name="mitra_id" data-label="Mitra">
                <div class="ms-trigger" onclick="togglePanel(this)">
                    <span class="ms-label">Semua Mitra</span>
                    <span class="ms-arrow"><i class="fas fa-chevron-down"></i></span>
                </div>
                <div class="ms-panel">
                    <div class="ms-panel-header">
                        <label><input type="checkbox" class="ms-select-all" onchange="toggleAll(this)"> Pilih Semua</label>
                    </div>
                    <div class="ms-search"><input type="text" placeholder="Cari mitra..." oninput="searchOptions(this)"></div>
                    <div class="ms-options">
                        @foreach($allMitra as $m)
                        @php $isChecked = is_array(request('mitra_id')) && in_array($m->id, request('mitra_id')); @endphp
                        <label class="ms-option" data-id="{{ $m->id }}" data-initial-count="{{ $m->total }}">
                            <input type="checkbox" name="mitra_id[]" value="{{ $m->id }}" {{ $isChecked ? 'checked' : '' }} onchange="updateDropdown(this)">
                            <span>{{ $m->nama_mitra }}</span>
                            <span class="ms-count">{{ $m->total }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Semester TA --}}
            @php
                $semesterTaValues = (array) request('semester_ta', []);
                $isFirstLoad = !request()->has('program_id') && !request()->has('sub_program_id') && !request()->has('fakultas_id') && !request()->has('prodi_id') && !request()->has('penyelenggara') && !request()->has('mitra_id') && !request()->has('semester_ta') && !request()->has('tahun_ajaran');
                if ($isFirstLoad && isset($defaultSemesterTa)) {
                    $semesterTaValues = $defaultSemesterTa;
                }
            @endphp
            <div class="ms-dropdown" data-name="semester_ta" data-label="Semester TA">
                <div class="ms-trigger" onclick="togglePanel(this)">
                    <span class="ms-label">Semua Semester TA</span>
                    <span class="ms-arrow"><i class="fas fa-chevron-down"></i></span>
                </div>
                <div class="ms-panel">
                    <div class="ms-panel-header">
                        <label><input type="checkbox" class="ms-select-all" onchange="toggleAll(this)"> Pilih Semua</label>
                    </div>
                    <div class="ms-search"><input type="text" placeholder="Cari semester TA..." oninput="searchOptions(this)"></div>
                    <div class="ms-options">
                        @foreach($allSemesterTa ?? [] as $staObj)
                        @php $isChecked = in_array($staObj->semester_ta, $semesterTaValues); @endphp
                        <label class="ms-option" data-id="{{ $staObj->semester_ta }}" data-initial-count="{{ $staObj->total }}">
                            <input type="checkbox" name="semester_ta[]" value="{{ $staObj->semester_ta }}" {{ $isChecked ? 'checked' : '' }} onchange="updateDropdown(this)">
                            <span>{{ $staObj->semester_ta }}</span>
                            <span class="ms-count">{{ $staObj->total }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Tahun Ajaran --}}
            <div class="ms-dropdown" data-name="tahun_ajaran" data-label="Tahun Ajaran">
                <div class="ms-trigger" onclick="togglePanel(this)">
                    <span class="ms-label">Semua Tahun Ajaran</span>
                    <span class="ms-arrow"><i class="fas fa-chevron-down"></i></span>
                </div>
                <div class="ms-panel">
                    <div class="ms-panel-header">
                        <label><input type="checkbox" class="ms-select-all" onchange="toggleAll(this)"> Pilih Semua</label>
                    </div>
                    <div class="ms-search"><input type="text" placeholder="Cari tahun ajaran..." oninput="searchOptions(this)"></div>
                    <div class="ms-options">
                        @foreach($allTahunAjaran ?? [] as $taObj)
                        @php $isChecked = is_array(request('tahun_ajaran')) && in_array($taObj->tahun_ajaran, request('tahun_ajaran')); @endphp
                        <label class="ms-option" data-id="{{ $taObj->tahun_ajaran }}" data-initial-count="{{ $taObj->total }}">
                            <input type="checkbox" name="tahun_ajaran[]" value="{{ $taObj->tahun_ajaran }}" {{ $isChecked ? 'checked' : '' }} onchange="updateDropdown(this)">
                            <span>{{ $taObj->tahun_ajaran }}</span>
                            <span class="ms-count">{{ $taObj->total }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
            </div>

        </div>

        {{-- Active Filter Tags --}}
        <div class="filter-tags" id="filterTags"></div>

        <div class="filter-actions">
            <a href="/dashboard" class="btn btn-outline"><i class="fas fa-undo"></i> Reset</a>
            <button type="button" class="btn btn-outline" onclick="openExportModal()"><i class="fas fa-download"></i> Export</button>
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Terapkan Filter</button>
        </div>
    </form>
</div>

<script>
// === Multi-Select Dropdown Logic ===

function togglePanel(trigger) {
    const panel = trigger.nextElementSibling;
    const isOpen = panel.classList.contains('show');

    // Close all other panels first
    document.querySelectorAll('.ms-panel.show').forEach(p => {
        p.classList.remove('show');
        p.closest('.ms-dropdown').querySelector('.ms-trigger').classList.remove('active');
    });

    if (!isOpen) {
        panel.classList.add('show');
        trigger.classList.add('active');
        // Focus search input if exists
        const searchInput = panel.querySelector('.ms-search input');
        if (searchInput) setTimeout(() => searchInput.focus(), 100);
    }
}

// Close panels when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('.ms-dropdown')) {
        document.querySelectorAll('.ms-panel.show').forEach(p => {
            p.classList.remove('show');
            p.closest('.ms-dropdown').querySelector('.ms-trigger').classList.remove('active');
        });
    }
});

function toggleAll(selectAllCb) {
    const panel = selectAllCb.closest('.ms-panel');
    const checkboxes = panel.querySelectorAll('.ms-options input[type=checkbox]');
    checkboxes.forEach(cb => {
        if (!cb.closest('.ms-option').classList.contains('hidden')) {
            cb.checked = selectAllCb.checked;
        }
    });
    updateDropdown(selectAllCb);
}

function searchOptions(input) {
    const panel = input.closest('.ms-panel');
    const query = input.value.toLowerCase().trim();
    const options = panel.querySelectorAll('.ms-option');
    let found = 0;
    options.forEach(opt => {
        const text = opt.querySelector('span').textContent.toLowerCase();
        const match = text.includes(query);
        opt.classList.toggle('hidden', !match);
        if (match) found++;
    });
    // Show/hide no-result message
    let noResult = panel.querySelector('.ms-no-result');
    if (found === 0) {
        if (!noResult) {
            noResult = document.createElement('div');
            noResult.className = 'ms-no-result';
            noResult.textContent = 'Tidak ditemukan';
            panel.querySelector('.ms-options').appendChild(noResult);
        }
        noResult.style.display = '';
    } else if (noResult) {
        noResult.style.display = 'none';
    }
    // Update "select all" state
    updateSelectAllState(panel);
}

function updateSelectAllState(panel) {
    const selectAll = panel.querySelector('.ms-select-all');
    if (!selectAll) return;
    const visible = panel.querySelectorAll('.ms-option:not(.hidden) input[type=checkbox]');
    const checked = panel.querySelectorAll('.ms-option:not(.hidden) input[type=checkbox]:checked');
    selectAll.checked = visible.length > 0 && visible.length === checked.length;
    selectAll.indeterminate = checked.length > 0 && checked.length < visible.length;
}

function refreshTriggerLabel(dropdown) {
    const panel = dropdown.querySelector('.ms-panel');
    const trigger = dropdown.querySelector('.ms-trigger');
    const label = trigger.querySelector('.ms-label');
    const dataLabel = dropdown.dataset.label;
    const allCbs = panel.querySelectorAll('.ms-options input[type=checkbox]');
    const checked = panel.querySelectorAll('.ms-options input[type=checkbox]:checked');
    const visibleCbs = panel.querySelectorAll('.ms-option:not(.unavailable) input[type=checkbox]');

    // Remove old badge
    const oldBadge = trigger.querySelector('.ms-badge');
    if (oldBadge) oldBadge.remove();

    if (checked.length === 0 || (checked.length === visibleCbs.length && visibleCbs.length === allCbs.length)) {
        label.textContent = 'Semua ' + dataLabel;
        trigger.classList.remove('has-selection');
    } else if (checked.length === 1) {
        label.textContent = checked[0].closest('.ms-option').querySelector('span').textContent;
        trigger.classList.add('has-selection');
    } else {
        label.textContent = dataLabel;
        trigger.classList.add('has-selection');
        const badge = document.createElement('span');
        badge.className = 'ms-badge';
        badge.textContent = checked.length;
        trigger.insertBefore(badge, trigger.querySelector('.ms-arrow'));
    }
}

function updateDropdown(changedCb) {
    const dropdown = changedCb.closest('.ms-dropdown');
    const panel = dropdown.querySelector('.ms-panel');

    refreshTriggerLabel(dropdown);
    updateSelectAllState(panel);
    updateFilterTags();

    // Trigger cascading filter update via AJAX
    fetchFilterOptions();
}

function updateFilterTags() {
    const container = document.getElementById('filterTags');
    container.innerHTML = '';

    document.querySelectorAll('.ms-dropdown').forEach(dropdown => {
        const dataLabel = dropdown.dataset.label;
        const allCbs = dropdown.querySelectorAll('.ms-options input[type=checkbox]');
        const checked = dropdown.querySelectorAll('.ms-options input[type=checkbox]:checked');

        // Skip adding individual tags if ALL options are checked
        if (checked.length === allCbs.length && allCbs.length > 0) {
            return;
        }

        checked.forEach(cb => {
            const name = cb.closest('.ms-option').querySelector('span').textContent;
            const tag = document.createElement('div');
            tag.className = 'filter-tag';
            tag.innerHTML = `<i class="fas fa-filter"></i> <strong>${dataLabel}:</strong> ${name} <span class="tag-remove" title="Hapus filter" data-cb-id="${cb.name}-${cb.value}">&times;</span>`;
            tag.querySelector('.tag-remove').addEventListener('click', function(e) {
                e.preventDefault();
                cb.checked = false;
                updateDropdown(cb);
            });
            container.appendChild(tag);
        });
    });
}

// === Cascading Filter AJAX Logic ===
let filterFetchTimer = null;
let filterAbortController = null;

function getActiveFilters() {
    const filters = {};
    document.querySelectorAll('.ms-dropdown').forEach(dropdown => {
        const name = dropdown.dataset.name;
        const allCbs = dropdown.querySelectorAll('.ms-options input[type=checkbox]');
        const checked = dropdown.querySelectorAll('.ms-options input[type=checkbox]:checked');
        
        if (checked.length > 0 && checked.length < allCbs.length) {
            filters[name] = Array.from(checked).map(cb => cb.value);
        }
    });
    return filters;
}

function fetchFilterOptions() {
    // Debounce: wait 200ms before fetching
    if (filterFetchTimer) clearTimeout(filterFetchTimer);
    if (filterAbortController) filterAbortController.abort();

    filterFetchTimer = setTimeout(() => {
        const filters = getActiveFilters();
        const hasAnyFilter = Object.keys(filters).length > 0;

        // If no filter is active, reset all dropdowns to show all options with initial counts
        if (!hasAnyFilter) {
            document.querySelectorAll('.ms-dropdown').forEach(dropdown => {
                dropdown.classList.remove('filter-loading');
                dropdown.querySelectorAll('.ms-option').forEach(opt => {
                    opt.classList.remove('unavailable');
                    const countEl = opt.querySelector('.ms-count');
                    if (countEl) {
                        countEl.textContent = opt.dataset.initialCount || '0';
                    }
                });
                const panel = dropdown.querySelector('.ms-panel');
                if (panel) updateSelectAllState(panel);
                refreshTriggerLabel(dropdown);
            });
            updateFilterTags();
            return;
        }

        // Build query string
        const params = new URLSearchParams();
        for (const [key, values] of Object.entries(filters)) {
            values.forEach(v => params.append(key + '[]', v));
        }

        // Show loading state on all dropdowns
        document.querySelectorAll('.ms-dropdown').forEach(d => d.classList.add('filter-loading'));

        filterAbortController = new AbortController();

        fetch('/api/filter-options?' + params.toString(), {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            signal: filterAbortController.signal
        })
        .then(res => res.json())
        .then(data => {
            // Update each dropdown with new options
            document.querySelectorAll('.ms-dropdown').forEach(dropdown => {
                const filterName = dropdown.dataset.name;
                const options = data[filterName];
                if (!options) return;

                // Build a map of id -> total from the API response
                const availableMap = {};
                options.forEach(opt => {
                    availableMap[String(opt.id)] = opt.total;
                });

                // Update each option in this dropdown
                dropdown.querySelectorAll('.ms-option').forEach(optEl => {
                    const optId = String(optEl.dataset.id);
                    const countEl = optEl.querySelector('.ms-count');
                    const cb = optEl.querySelector('input[type=checkbox]');

                    if (cb) cb.disabled = false;
                    if (availableMap.hasOwnProperty(optId)) {
                        // Option is available
                        optEl.classList.remove('unavailable');
                        if (countEl) countEl.textContent = availableMap[optId];
                    } else {
                        // Option has no data under the current filters — show 0 and make it faded
                        optEl.classList.add('unavailable');
                        if (countEl) countEl.textContent = '0';
                    }
                });

                // Update select-all state and trigger label
                const panel = dropdown.querySelector('.ms-panel');
                if (panel) updateSelectAllState(panel);
                refreshTriggerLabel(dropdown);

                dropdown.classList.remove('filter-loading');
            });

            // Update filter tags after all dropdowns are updated
            updateFilterTags();
        })
        .catch(err => {
            if (err.name !== 'AbortError') {
                console.error('Filter fetch error:', err);
            }
            document.querySelectorAll('.ms-dropdown').forEach(d => d.classList.remove('filter-loading'));
        });
    }, 200);
}

// Initialize all dropdowns on page load
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.ms-dropdown').forEach(dropdown => {
        const firstCb = dropdown.querySelector('.ms-options input[type=checkbox]');
        if (firstCb) {
            refreshTriggerLabel(dropdown);
            const panel = dropdown.querySelector('.ms-panel');
            updateSelectAllState(panel);
        }
    });

    // Update filter tags
    updateFilterTags();

    // If there are active filters on page load, fetch cascading options
    const activeFilters = getActiveFilters();
    if (Object.keys(activeFilters).length > 0) {
        fetchFilterOptions();
    }
});
</script>

{{-- SUMMARY CARDS --}}
<div class="summary-grid">
    <div class="summary-card">
        <div class="summary-icon" style="background:#fef2f2;color:#dc2626"><i class="fas fa-user-graduate"></i></div>
        <div><div class="value">{{ number_format($totalMahasiswa) }}</div><div class="label">Total Mahasiswa PLPS</div></div>
    </div>
    <div class="summary-card">
        <div class="summary-icon" style="background:#eff6ff;color:#2563eb"><i class="fas fa-building"></i></div>
        <div><div class="value">{{ number_format($totalMitra) }}</div><div class="label">Total Mitra</div></div>
    </div>
    <div class="summary-card">
        <div class="summary-icon" style="background:#f0fdf4;color:#16a34a"><i class="fas fa-folder-open"></i></div>
        <div><div class="value">{{ number_format($totalProgram) }}</div><div class="label">Total Program</div></div>
    </div>
    <div class="summary-card">
        <div class="summary-icon" style="background:#fefce8;color:#ca8a04"><i class="fas fa-layer-group"></i></div>
        <div><div class="value">{{ number_format($totalSubProgram) }}</div><div class="label">Total Sub Program</div></div>
    </div>
</div>

{{-- TREN PER SEMESTER (moved above) --}}
<div class="card">
    <div class="chart-title"><i class="fas fa-chart-line" style="color:#7B1113"></i> Grafik Per Semester</div>
    <canvas id="chartTren" height="120"></canvas>
</div>

{{-- TOP 5 ROW: Fakultas | Prodi | Program --}}
<div class="chart-row-3">
    <div class="card">
        <div class="chart-title"><i class="fas fa-chart-bar" style="color:#7B1113"></i> Top 5 Fakultas</div>
        <canvas id="chartFakultas" height="250"></canvas>
    </div>
    <div class="card">
        <div class="chart-title"><i class="fas fa-chart-bar" style="color:#7B1113"></i> Top 5 Prodi Terbanyak</div>
        <canvas id="chartProdi" height="250"></canvas>
    </div>
    <div class="card">
        <div class="chart-title"><i class="fas fa-chart-bar" style="color:#7B1113"></i> Top 5 Program Terbanyak</div>
        <canvas id="chartProgram" height="250"></canvas>
    </div>
</div>

{{-- MITRA RANKING (INTERNAL & EKSTERNAL) --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px">
    {{-- MITRA EKSTERNAL --}}
    <div class="card">
        <div class="chart-title"><i class="fas fa-circle" style="color:#2173b5"></i> Mitra Eksternal</div>
        <div style="display:flex;align-items:center;margin-bottom:20px">
            <div style="width:100px;height:100px;position:relative;flex-shrink:0">
                <canvas id="chartMitraEksternal"></canvas>
                <div style="position:absolute;top:0;left:0;right:0;bottom:0;display:flex;flex-direction:column;align-items:center;justify-content:center;font-size:16px;font-weight:700;color:#1e293b">
                    <span id="pctEksternal">0%</span>
                    <span style="font-size:10px;font-weight:500;color:#64748b;margin-top:-2px">dari total</span>
                </div>
            </div>
            <div style="flex:1;text-align:center">
                <div style="font-size:36px;font-weight:800;color:#2173b5;line-height:1">{{ number_format($totalMitraEksternal) }}</div>
                <div style="font-size:12px;color:#64748b;font-weight:500;margin-top:6px">Total Mitra Eksternal</div>
            </div>
        </div>
        <div style="font-size:12px;font-weight:700;margin-bottom:12px;text-transform:uppercase;color:#475569">Top 5 Mitra Eksternal</div>
        @if($topMitraEksternal->isEmpty())
            <p style="color:#94a3b8;font-size:13px;text-align:center">Tidak ada data mitra eksternal</p>
        @else
            <ul class="mitra-list">
            @php $maxEks = $topMitraEksternal->max('total'); @endphp
            @foreach($topMitraEksternal as $i => $mt)
                <li style="display:flex;align-items:center;padding:10px 0;gap:14px;border-bottom:none">
                    <div style="width:16px;text-align:right;font-size:13px;font-weight:600;color:#64748b">{{ $i+1 }}</div>
                    <div style="flex:1">
                        <div style="font-size:13px;margin-bottom:6px;font-weight:500;color:#334155">{{ $mt->nama_mitra }}</div>
                        <div style="background:#f1f5f9;height:6px;border-radius:3px;width:100%;overflow:hidden">
                            <div style="height:100%;background:#2173b5;border-radius:3px;width:{{ $maxEks > 0 ? ($mt->total / $maxEks) * 100 : 0 }}%"></div>
                        </div>
                    </div>
                    <div style="font-size:13px;font-weight:600;width:32px;text-align:right;color:#1e293b">{{ number_format($mt->total) }}</div>
                </li>
            @endforeach
            </ul>
        @endif
    </div>

    {{-- MITRA INTERNAL --}}
    <div class="card">
        <div class="chart-title"><i class="fas fa-circle" style="color:#0f766e"></i> Mitra Internal</div>
        <div style="display:flex;align-items:center;margin-bottom:20px">
            <div style="width:100px;height:100px;position:relative;flex-shrink:0">
                <canvas id="chartMitraInternal"></canvas>
                <div style="position:absolute;top:0;left:0;right:0;bottom:0;display:flex;flex-direction:column;align-items:center;justify-content:center;font-size:16px;font-weight:700;color:#1e293b">
                    <span id="pctInternal">0%</span>
                    <span style="font-size:10px;font-weight:500;color:#64748b;margin-top:-2px">dari total</span>
                </div>
            </div>
            <div style="flex:1;text-align:center">
                <div style="font-size:36px;font-weight:800;color:#0f766e;line-height:1">{{ number_format($totalMitraInternal) }}</div>
                <div style="font-size:12px;color:#64748b;font-weight:500;margin-top:6px">Total Mitra Internal</div>
            </div>
        </div>
        <div style="font-size:12px;font-weight:700;margin-bottom:12px;text-transform:uppercase;color:#475569">Top 5 Mitra Internal</div>
        @if($topMitraInternal->isEmpty())
            <p style="color:#94a3b8;font-size:13px;text-align:center">Tidak ada data mitra internal</p>
        @else
            <ul class="mitra-list">
            @php $maxInt = $topMitraInternal->max('total'); @endphp
            @foreach($topMitraInternal as $i => $mt)
                <li style="display:flex;align-items:center;padding:10px 0;gap:14px;border-bottom:none">
                    <div style="width:16px;text-align:right;font-size:13px;font-weight:600;color:#64748b">{{ $i+1 }}</div>
                    <div style="flex:1">
                        <div style="font-size:13px;margin-bottom:6px;font-weight:500;color:#334155">{{ $mt->nama_mitra }}</div>
                        <div style="background:#f1f5f9;height:6px;border-radius:3px;width:100%;overflow:hidden">
                            <div style="height:100%;background:#0f766e;border-radius:3px;width:{{ $maxInt > 0 ? ($mt->total / $maxInt) * 100 : 0 }}%"></div>
                        </div>
                    </div>
                    <div style="font-size:13px;font-weight:600;width:32px;text-align:right;color:#1e293b">{{ number_format($mt->total) }}</div>
                </li>
            @endforeach
            </ul>
        @endif
    </div>
</div>

{{-- DISTRIBUSI PER PROGRAM MBKM --}}
<div class="card" style="margin-bottom:20px;padding-bottom:30px">
    <div style="font-size:14px;font-weight:700;margin-bottom:24px;text-transform:uppercase;color:#1e293b;letter-spacing:0.5px">Distribusi Per Program MBKM</div>
    @if($distribusiProgram->isEmpty())
        <p style="color:#94a3b8;font-size:13px;text-align:center">Tidak ada data program</p>
    @else
        <div style="display:flex;flex-direction:column;gap:14px">
        @php
            $maxProg = $distribusiProgram->max('total');
            $progColorsList = ['#2173b5', '#673db0', '#0f766e', '#ca8a04', '#ea580c', '#4d7c0f', '#0369a1', '#b91c1c'];
        @endphp
        @foreach($distribusiProgram as $i => $prog)
            <div style="display:flex;align-items:center;gap:20px">
                <div style="width:260px;font-size:14px;font-weight:500;color:#334155">{{ $prog->nama_program }}</div>
                <div style="flex:1;background:transparent;height:12px;display:flex;align-items:center">
                    <div style="height:12px;background:{{ $progColorsList[$i % count($progColorsList)] }};border-radius:6px;width:{{ $maxProg > 0 ? ($prog->total / $maxProg) * 100 : 0 }}%"></div>
                </div>
                <div style="width:80px;text-align:right;font-size:14px;font-weight:500;color:#1e293b">{{ number_format($prog->total, 0, ',', '.') }}</div>
            </div>
        @endforeach
        </div>
    @endif
</div>

{{-- DATA MAHASISWA MBKM TABLE --}}
<div class="card" id="tableCard">
    <div class="chart-title"><i class="fas fa-table" style="color:#7B1113"></i> Data Mahasiswa MBKM</div>
    <div class="search-bar" id="tableSearchBar">
        <input type="text" id="searchNama" placeholder="Cari Nama Mahasiswa..." value="{{ request('search_nama') }}">
        <input type="text" id="searchNim" placeholder="Cari NIM..." value="{{ request('search_nim') }}">
        <button type="button" class="btn btn-primary" onclick="resetAndLoadTable()"><i class="fas fa-search"></i> Cari</button>
    </div>

    @if($isAdmin ?? false)
    <div class="bulk-toolbar" id="bulkToolbar">
        <span class="bulk-count"><i class="fas fa-check-square"></i> <span id="selectedCount">0</span> data dipilih</span>
        <button type="button" class="btn btn-danger" onclick="bulkDeleteSelected()"><i class="fas fa-trash"></i> Hapus Terpilih</button>
        <button type="button" class="btn btn-outline" onclick="clearAllSelections()" style="font-size:13px"><i class="fas fa-times"></i> Batal</button>
    </div>
    @endif

    <div class="table-wrap" id="tableWrap">
        <table class="data-table" id="lazyTable">
            <thead>
                <tr>
                    @if($isAdmin ?? false)<th style="width:40px"><input type="checkbox" id="selectAllRows" onchange="toggleSelectAll(this)" title="Pilih Semua"></th>@endif
                    <th>No</th><th>Program</th><th>Sub Program</th><th>Fakultas</th><th>Program Studi</th>
                    <th>NIM</th><th>Nama Mahasiswa</th><th>Tahun Ajaran</th><th>Semester</th><th>Semester TA</th>
                    <th>Program Kegiatan</th><th>Penyelenggara</th><th>Mitra</th>
                    <th>Dosen Pembimbing</th><th>Jumlah Konversi SKS</th>
                    @if($isAdmin ?? false)<th style="width:60px">Aksi</th>@endif
                </tr>
            </thead>
            <tbody id="tableBody">
            </tbody>
        </table>
        <!-- No sentinel needed for standard pagination -->
    </div>
    <div class="table-info" id="tableInfo" style="display:none; justify-content:flex-end; align-items:center; padding: 15px 20px;">
        <div class="pagination-controls" style="display:flex; gap:15px; align-items:center;">
            <span id="pageText" style="font-size:13px; font-weight:600; color:#475569">0 - 0 / 0</span>
            <div style="display:flex; gap:5px;">
                <button class="btn btn-outline" id="prevPageBtn" onclick="prevPage()" style="padding:6px 12px" title="Previous Page"><i class="fas fa-chevron-left"></i></button>
                <button class="btn btn-outline" id="nextPageBtn" onclick="nextPage()" style="padding:6px 12px" title="Next Page"><i class="fas fa-chevron-right"></i></button>
            </div>
        </div>
    </div>
    <div class="table-status" id="tableStatus"><i class="fas fa-spinner fa-spin"></i> Memuat data...</div>
</div>

{{-- EDIT MODAL --}}
@if($isAdmin ?? false)
<div class="edit-modal-overlay" id="editModalOverlay" onmousedown="if(event.target===this)this.dataset.close='1';" onmouseup="if(this.dataset.close==='1' && event.target===this){closeEditModal();} this.dataset.close='0';">
    <div class="edit-modal">
        <div class="edit-modal-header">
            <h3><i class="fas fa-edit" style="color:#7B1113"></i> Edit Data</h3>
            <button class="edit-modal-close" onclick="closeEditModal()">&times;</button>
        </div>
        <div class="edit-modal-body">
            <input type="hidden" id="editRowId">
            <div class="edit-field">
                <label>Tahun Ajaran</label>
                <input type="text" id="editTahunAjaran" placeholder="contoh: 2024/2025">
            </div>
            <div class="edit-field">
                <label>Semester</label>
                <select id="editSemester">
                    <option value="GANJIL">GANJIL</option>
                    <option value="GENAP">GENAP</option>
                </select>
            </div>
            <div class="edit-field">
                <label>Semester TA</label>
                <input type="text" id="editSemesterTa" placeholder="contoh: 2024/2025 S1">
            </div>
            <div class="edit-field">
                <label>Kegiatan</label>
                <input type="text" id="editKegiatan" placeholder="Nama kegiatan">
            </div>
            <div class="edit-field">
                <label>Penyelenggara</label>
                <select id="editPenyelenggara">
                    <option value="Eksternal">Eksternal</option>
                    <option value="Internal">Internal</option>
                </select>
            </div>
            <div class="edit-field">
                <label>Mitra</label>
                <input type="text" id="editMitra" placeholder="Nama mitra">
            </div>
            <div class="edit-field">
                <label>Dosen Pembimbing</label>
                <input type="text" id="editDosen" placeholder="Nama dosen pembimbing">
            </div>
            <div class="edit-field">
                <label>Jumlah Konversi SKS</label>
                <input type="number" id="editSks" min="0" placeholder="0">
            </div>
        </div>
        <div class="edit-modal-footer">
            <button class="btn btn-outline" onclick="closeEditModal()">Batal</button>
            <button class="btn btn-primary" id="editSaveBtn" onclick="saveEdit()"><i class="fas fa-save"></i> Simpan</button>
        </div>
    </div>
</div>
@endif

{{-- EXPORT MODAL --}}
<div class="edit-modal-overlay" id="exportModalOverlay" onmousedown="if(event.target===this)this.dataset.close='1';" onmouseup="if(this.dataset.close==='1' && event.target===this){closeExportModal();} this.dataset.close='0';">
    <div class="edit-modal" style="max-width:400px;text-align:center">
        <div class="edit-modal-header">
            <h3><i class="fas fa-download" style="color:#7B1113"></i> Export Data</h3>
            <button class="edit-modal-close" onclick="closeExportModal()">&times;</button>
        </div>
        <div class="edit-modal-body" style="padding:30px">
            <p style="margin-bottom:20px;font-size:14px;color:#475569">Pilih format file untuk mengunduh laporan sesuai filter yang sedang aktif.</p>
            <div style="display:flex;gap:15px;justify-content:center">
                <button type="button" class="btn btn-outline" onclick="doExport('excel')" style="flex:1;padding:15px;color:#0f766e;border-color:#0f766e">
                    <i class="fas fa-file-excel" style="font-size:24px;margin-bottom:8px;display:block"></i> Excel
                </button>
                <button type="button" class="btn btn-outline" onclick="doExport('pdf')" style="flex:1;padding:15px;color:#b91c1c;border-color:#b91c1c">
                    <i class="fas fa-file-pdf" style="font-size:24px;margin-bottom:8px;display:block"></i> PDF
                </button>
            </div>
        </div>
    </div>
</div>

{{-- DELETE CONFIRMATION MODAL --}}
<div class="edit-modal-overlay" id="deleteConfirmOverlay" onmousedown="if(event.target===this)this.dataset.close='1';" onmouseup="if(this.dataset.close==='1' && event.target===this){closeDeleteModal();} this.dataset.close='0';">
    <div class="edit-modal" style="max-width:440px;text-align:center">
        <div class="edit-modal-header" style="border-bottom:none;padding-bottom:0">
            <h3 style="color:#b91c1c"><i class="fas fa-exclamation-triangle" style="color:#dc2626"></i> Konfirmasi Hapus Data</h3>
            <button class="edit-modal-close" onclick="closeDeleteModal()">&times;</button>
        </div>
        <div class="edit-modal-body" style="padding:20px 24px">
            <div style="width:60px;height:60px;border-radius:50%;background:#fef2f2;color:#dc2626;display:flex;align-items:center;justify-content:center;font-size:28px;margin:0 auto 16px">
                <i class="fas fa-trash-alt"></i>
            </div>
            <p style="font-size:15px;color:#1e293b;font-weight:600;margin-bottom:8px">Apakah Anda yakin ingin menghapus data?</p>
            <p style="font-size:13px;color:#64748b;line-height:1.5" id="deleteConfirmText">Tindakan ini akan menghapus data terpilih secara permanen dari database dan tidak dapat dibatalkan.</p>
        </div>
        <div class="edit-modal-footer" style="justify-content:center;gap:12px;background:#f8fafc;border-top:1px solid #e2e8f0;border-radius:0 0 16px 16px">
            <button class="btn btn-outline" onclick="closeDeleteModal()" style="padding:10px 20px">Batal</button>
            <button class="btn btn-red" id="confirmDeleteBtn" onclick="executeBulkDelete()" style="padding:10px 24px;background:#dc2626">
                <i class="fas fa-trash-alt"></i> Ya, Hapus Data
            </button>
        </div>
    </div>
</div>

{{-- DASHBOARD ERROR / ALERT MODAL --}}
<div class="edit-modal-overlay" id="dashboardErrorOverlay" onmousedown="if(event.target===this)this.dataset.close='1';" onmouseup="if(this.dataset.close==='1' && event.target===this){closeDashboardErrorModal();} this.dataset.close='0';">
    <div class="edit-modal" style="max-width:460px">
        <div class="edit-modal-header" style="background:#fef2f2;border-bottom:1px solid #fecaca;border-radius:16px 16px 0 0">
            <h3 style="color:#991b1b;font-size:16px;display:flex;align-items:center;gap:8px">
                <i class="fas fa-exclamation-circle" style="color:#dc2626;font-size:18px"></i> <span id="dashboardErrorTitle">Kesalahan Validasi</span>
            </h3>
            <button class="edit-modal-close" onclick="closeDashboardErrorModal()" style="color:#991b1b">&times;</button>
        </div>
        <div class="edit-modal-body" style="padding:20px 24px">
            <div id="dashboardErrorContent" style="color:#374151;font-size:13.5px;line-height:1.6"></div>
        </div>
        <div class="edit-modal-footer" style="background:#f9fafb;border-top:1px solid #e5e7eb">
            <button class="btn btn-red" onclick="closeDashboardErrorModal()" style="padding:8px 20px">Tutup</button>
        </div>
    </div>
</div>

<script>
    const isAdmin = {{ ($isAdmin ?? false) ? 'true' : 'false' }};
</script>
@vite(['resources/js/dashboard.js'])

{{-- CHART.JS SCRIPTS --}}
<script>
// Color map for fakultas
const fakultasColors = {
    'FTE':'#004f86','FIF':'#c7a12c','FEB':'#239e91','FRI':'#0d8039',
    'FKS':'#59329e','FIK':'#ea580c','FIT':'#00cc52','FKB':'#cc420c',
    'TUP':'#b91c1c','TUS':'#6b7280'
};
function getFakColor(name) {
    return fakultasColors[name.toUpperCase()] || '#9ca3af';
}

// 1. Tren Per Semester (Line) — rendered first since it's at top
const trenLabels = @json($semesters);
const trenSeries = @json($trenSeries);
const trenDatasets = trenSeries.map(s => ({
    label: s.label,
    data: s.data,
    borderColor: getFakColor(s.label),
    backgroundColor: getFakColor(s.label),
    tension: 0.3,
    borderWidth: 3.5,
    pointRadius: 5,
    pointHoverRadius: 7,
    fill: false
}));
new Chart(document.getElementById('chartTren'), {
    type: 'line',
    data: { labels: trenLabels, datasets: trenDatasets },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'top', labels: { usePointStyle: true, padding: 16, font: { size: 11 }}},
            tooltip: { mode: 'index', intersect: false }
        },
        scales: {
            y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#f1f5f9' }},
            x: { grid: { display: false }}
        },
        interaction: { mode: 'nearest', axis: 'x', intersect: false }
    }
});

// 2. Top 5 Fakultas (Horizontal Bar — bar chart miring)
const fakLabels = @json($mahasiswaPerFakultas->pluck('nama_fakultas'));
const fakData = @json($mahasiswaPerFakultas->pluck('total'));
new Chart(document.getElementById('chartFakultas'), {
    type: 'bar',
    data: {
        labels: fakLabels,
        datasets: [{
            label: 'Mahasiswa',
            data: fakData,
            backgroundColor: fakLabels.map(n => getFakColor(n)),
            borderRadius: 6,
            maxBarThickness: 32
        }]
    },
    options: {
        indexAxis: 'y',
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: { callbacks: { label: ctx => ctx.parsed.x.toLocaleString() + ' Mahasiswa' }}
        },
        scales: {
            x: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#f1f5f9' }},
            y: { grid: { display: false }}
        }
    }
});

// 3. Top 5 Prodi (Horizontal Bar)
const prodiLabels = @json($topProdi->pluck('nama_prodi'));
const prodiData = @json($topProdi->pluck('total'));
const prodiColors = prodiLabels.map((_, i) => {
    const opacity = 1 - (i * 0.08);
    return `rgba(123,17,19,${Math.max(opacity, 0.55)})`;
});
new Chart(document.getElementById('chartProdi'), {
    type: 'bar',
    data: {
        labels: prodiLabels,
        datasets: [{
            label: 'Mahasiswa',
            data: prodiData,
            backgroundColor: prodiColors,
            borderRadius: 6,
            maxBarThickness: 32
        }]
    },
    options: {
        indexAxis: 'y',
        responsive: true,
        layout: { padding: { left: 5 } },
        plugins: {
            legend: { display: false },
            tooltip: { callbacks: { label: ctx => ctx.parsed.x.toLocaleString() + ' Mahasiswa' }}
        },
        scales: {
            x: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#f1f5f9' }},
            y: { grid: { display: false }, ticks: { font: { size: 11 }, autoSkip: false, callback: function(value) {
                const label = this.getLabelForValue(value);
                if (label.length > 15) return label.substring(0, 15) + '...';
                return label;
            }}}
        }
    }
});

// 4. Top 5 Program (Horizontal Bar)
const progLabels = @json($topProgram->pluck('nama_program'));
const progData = @json($topProgram->pluck('total'));
const progColors = ['#005A98', '#D3B048', '#31BAAD', '#109344', '#673DB0'];
new Chart(document.getElementById('chartProgram'), {
    type: 'bar',
    data: {
        labels: progLabels,
        datasets: [{
            label: 'Mahasiswa',
            data: progData,
            backgroundColor: progColors.slice(0, progLabels.length),
            borderRadius: 6,
            maxBarThickness: 32
        }]
    },
    options: {
        indexAxis: 'y',
        responsive: true,
        layout: { padding: { left: 5 } },
        plugins: {
            legend: { display: false },
            tooltip: { callbacks: { label: ctx => ctx.parsed.x.toLocaleString() + ' Mahasiswa' }}
        },
        scales: {
            x: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#f1f5f9' }},
            y: { grid: { display: false }, ticks: { font: { size: 11 }, autoSkip: false, callback: function(value) {
                const label = this.getLabelForValue(value);
                if (label.length > 25) return label.substring(0, 25) + '...';
                return label;
            }}}
        }
    }
});

// MITRA DOUGHNUT CHARTS
const totalEks = {{ $totalMitraEksternal ?? 0 }};
const totalInt = {{ $totalMitraInternal ?? 0 }};
const totalAllMitra = totalEks + totalInt;
const pctEks = totalAllMitra > 0 ? Math.round((totalEks / totalAllMitra) * 100) : 0;
const pctInt = totalAllMitra > 0 ? Math.round((totalInt / totalAllMitra) * 100) : 0;

if(document.getElementById('pctEksternal')) document.getElementById('pctEksternal').innerText = pctEks + '%';
if(document.getElementById('pctInternal')) document.getElementById('pctInternal').innerText = pctInt + '%';

if(document.getElementById('chartMitraEksternal')){
    new Chart(document.getElementById('chartMitraEksternal'), {
        type: 'doughnut',
        data: {
            datasets: [{
                data: [totalEks, totalInt],
                backgroundColor: ['#2173b5', '#e2e8f0'],
                borderWidth: 0,
                cutout: '75%'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { tooltip: { enabled: false }, legend: { display: false } },
            animation: { animateScale: true }
        }
    });
}

if(document.getElementById('chartMitraInternal')){
    new Chart(document.getElementById('chartMitraInternal'), {
        type: 'doughnut',
        data: {
            datasets: [{
                data: [totalInt, totalEks],
                backgroundColor: ['#0f766e', '#e2e8f0'],
                borderWidth: 0,
                cutout: '75%'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { tooltip: { enabled: false }, legend: { display: false } },
            animation: { animateScale: true }
        }
    });
}
</script>

@endif {{-- end hasData --}}

@endsection