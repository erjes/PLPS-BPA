// === Lazy Loading Table ===
const colCount = typeof isAdmin !== 'undefined' && isAdmin ? 17 : 15;
let tablePage = 1;
let tableLastPage = 1;
let tableLoading = false;
let tableTotal = 0;
let tableLoadedCount = 0;
let tableAbortController = null;
let selectedIds = new Set();

function getTableFilters() {
    const params = new URLSearchParams();
    // Get active filters from dropdowns
    document.querySelectorAll('.ms-dropdown').forEach(dropdown => {
        const name = dropdown.dataset.name;
        const allCbs = dropdown.querySelectorAll('.ms-options input[type=checkbox]');
        const checked = dropdown.querySelectorAll('.ms-options input[type=checkbox]:checked');
        
        if (checked.length > 0 && checked.length < allCbs.length) {
            checked.forEach(cb => params.append(name + '[]', cb.value));
        }
    });
    // Get search inputs
    const searchNama = document.getElementById('searchNama').value.trim();
    const searchNim = document.getElementById('searchNim').value.trim();
    if (searchNama) params.set('search_nama', searchNama);
    if (searchNim) params.set('search_nim', searchNim);
    return params;
}

function loadTablePage(page) {
    if (tableLoading || page > tableLastPage) return;
    tableLoading = true;

    const statusEl = document.getElementById('tableStatus');
    if (page === 1) {
        statusEl.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memuat data...';
        statusEl.style.display = '';
    } else {
        showSkeletonRows();
    }

    if (tableAbortController) tableAbortController.abort();
    tableAbortController = new AbortController();

    const params = getTableFilters();
    params.set('page', page);

    fetch('/api/table-data?' + params.toString(), {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        signal: tableAbortController.signal
    })
    .then(res => res.json())
    .then(data => {
        const tbody = document.getElementById('tableBody');

        tbody.innerHTML = '';
        tableLoadedCount = 0;

        if (data.data.length === 0) {
            tbody.innerHTML = `<tr><td colspan="${colCount}" style="text-align:center;padding:30px;color:#94a3b8">Tidak ada data yang sesuai</td></tr>`;
            statusEl.style.display = 'none';
            document.getElementById('tableInfo').style.display = 'none';
            tableLoading = false;
            return;
        }

        data.data.forEach(row => {
            const tr = document.createElement('tr');
            tr.dataset.id = row.id;
            let html = '';
            if (typeof isAdmin !== 'undefined' && isAdmin) {
                const checked = selectedIds.has(row.id) ? 'checked' : '';
                html += `<td><input type="checkbox" class="row-cb" value="${row.id}" ${checked} onchange="toggleRowSelect(this)"></td>`;
            }
            html += `
                <td>${row.no}</td>
                <td>${esc(row.program)}</td>
                <td>${esc(row.sub_program)}</td>
                <td>${esc(row.fakultas)}</td>
                <td>${esc(row.prodi)}</td>
                <td>${esc(row.nim)}</td>
                <td>${esc(row.nama)}</td>
                <td>${esc(row.tahun_ajaran)}</td>
                <td>${esc(row.semester)}</td>
                <td>${esc(row.semester_ta)}</td>
                <td>${esc(row.kegiatan)}</td>
                <td>${esc(row.penyelenggara)}</td>
                <td>${esc(row.mitra)}</td>
                <td>${esc(row.dosen_pembimbing)}</td>
                <td>${row.sks}</td>
            `;
            if (typeof isAdmin !== 'undefined' && isAdmin) {
                html += `<td><button class="btn" style="padding:4px 8px;font-size:12px;background:#7B1113;color:#fff" onclick="openEditModal(${row.id}, this)" title="Edit"><i class="fas fa-edit"></i></button></td>`;
            }
            tr.innerHTML = html;
            tbody.appendChild(tr);
        });

        tableLoadedCount = data.data.length;
        tablePage = data.current_page;
        tableLastPage = data.last_page;
        tableTotal = data.total;
        let start = data.from || 0;
        let end = data.to || 0;

        const infoEl = document.getElementById('tableInfo');
        infoEl.style.display = 'flex';
        document.getElementById('pageText').textContent = `${start} - ${end} / ${tableTotal}`;
        
        const prevBtn = document.getElementById('prevPageBtn');
        const nextBtn = document.getElementById('nextPageBtn');
        
        if(tablePage <= 1) {
            prevBtn.disabled = true;
            prevBtn.style.opacity = '0.5';
        } else {
            prevBtn.disabled = false;
            prevBtn.style.opacity = '1';
        }
        
        if(tablePage >= tableLastPage) {
            nextBtn.disabled = true;
            nextBtn.style.opacity = '0.5';
        } else {
            nextBtn.disabled = false;
            nextBtn.style.opacity = '1';
        }

        statusEl.style.display = 'none';

        tableLoading = false;
    })
    .catch(err => {
        if (err.name !== 'AbortError') {
            console.error('Table fetch error:', err);
            document.getElementById('tableStatus').innerHTML = '<i class="fas fa-exclamation-circle" style="color:#dc2626"></i> Gagal memuat data';
        }
        tableLoading = false;
    });
}

function showSkeletonRows() {
    const tbody = document.getElementById('tableBody');
    for (let i = 0; i < 5; i++) {
        const tr = document.createElement('tr');
        tr.className = 'skeleton-row';
        let cells = '';
        for (let j = 0; j < colCount; j++) {
            const w = 40 + Math.random() * 60;
            cells += `<td><div class="skeleton-bar" style="width:${w}%"></div></td>`;
        }
        tr.innerHTML = cells;
        tbody.appendChild(tr);
    }
}

function resetAndLoadTable() {
    tablePage = 1;
    tableLastPage = 1;
    tableLoadedCount = 0;
    loadTablePage(1);
}

function esc(str) {
    if (str === null || str === undefined) return '-';
    const div = document.createElement('div');
    div.textContent = String(str);
    return div.innerHTML;
}

function prevPage() {
    if (tablePage > 1 && !tableLoading) {
        loadTablePage(tablePage - 1);
    }
}

function nextPage() {
    if (tablePage < tableLastPage && !tableLoading) {
        loadTablePage(tablePage + 1);
    }
}

document.getElementById('searchNama')?.addEventListener('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); resetAndLoadTable(); } });
document.getElementById('searchNim')?.addEventListener('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); resetAndLoadTable(); } });

document.addEventListener('DOMContentLoaded', function() {
    setTimeout(() => loadTablePage(1), 100);
});

// === Selection & Bulk Delete ===
function toggleRowSelect(cb) {
    const id = parseInt(cb.value);
    if (cb.checked) { selectedIds.add(id); } else { selectedIds.delete(id); }
    updateBulkToolbar();
}

function toggleSelectAll(masterCb) {
    document.querySelectorAll('.row-cb').forEach(cb => {
        cb.checked = masterCb.checked;
        const id = parseInt(cb.value);
        if (masterCb.checked) { selectedIds.add(id); } else { selectedIds.delete(id); }
    });
    updateBulkToolbar();
}

function clearAllSelections() {
    selectedIds.clear();
    document.querySelectorAll('.row-cb').forEach(cb => cb.checked = false);
    const selectAll = document.getElementById('selectAllRows');
    if (selectAll) selectAll.checked = false;
    updateBulkToolbar();
}

function updateBulkToolbar() {
    const toolbar = document.getElementById('bulkToolbar');
    if (!toolbar) return;
    const count = selectedIds.size;
    document.getElementById('selectedCount').textContent = count;
    toolbar.classList.toggle('show', count > 0);
}

function bulkDeleteSelected() {
    if (selectedIds.size === 0) return;
    const count = selectedIds.size;
    document.getElementById('deleteConfirmText').textContent = `Tindakan ini akan menghapus ${count} data terpilih secara permanen dari database dan tidak dapat dibatalkan.`;
    document.getElementById('deleteConfirmOverlay').classList.add('show');
}

function closeDeleteModal() {
    document.getElementById('deleteConfirmOverlay').classList.remove('show');
}

function executeBulkDelete() {
    if (selectedIds.size === 0) return;
    const btn = document.getElementById('confirmDeleteBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menghapus...';

    fetch('/api/data-plps/bulk-delete', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ ids: Array.from(selectedIds) })
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-trash-alt"></i> Ya, Hapus Data';
        closeDeleteModal();

        if (data.success) {
            showToast(data.message);
            selectedIds.clear();
            updateBulkToolbar();
            resetAndLoadTable();
        } else {
            showDashboardErrorModal('Gagal Menghapus Data', data.message || 'Terjadi kesalahan saat menghapus data.');
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-trash-alt"></i> Ya, Hapus Data';
        closeDeleteModal();
        showDashboardErrorModal('Gagal Menghapus Data', err.message || 'Terjadi kesalahan koneksi.');
    });
}

function showDashboardErrorModal(title, message) {
    document.getElementById('dashboardErrorTitle').textContent = title;
    const contentDiv = document.getElementById('dashboardErrorContent');
    if (typeof message === 'string' && message.includes('\n')) {
        const lines = message.split('\n').filter(l => l.trim() !== '');
        contentDiv.innerHTML = '<ul style="margin:0;padding-left:18px;display:flex;flex-direction:column;gap:6px">' + 
            lines.map(l => `<li>${esc(l)}</li>`).join('') + 
            '</ul>';
    } else {
        contentDiv.textContent = message;
    }
    document.getElementById('dashboardErrorOverlay').classList.add('show');
}

function closeDashboardErrorModal() {
    document.getElementById('dashboardErrorOverlay').classList.remove('show');
}

function showToast(msg) {
    const existing = document.querySelector('.toast');
    if (existing) existing.remove();
    const toast = document.createElement('div');
    toast.className = 'toast';
    toast.innerHTML = `<i class="fas fa-check-circle"></i> ${msg}`;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

// === Edit Modal ===
let editRowData = {};

function openEditModal(id, btn) {
    const tr = btn.closest('tr');
    const cells = tr.querySelectorAll('td');
    const offset = typeof isAdmin !== 'undefined' && isAdmin ? 1 : 0;
    editRowData = {
        id: id,
        tahun_ajaran: cells[7 + offset]?.textContent?.trim() || '',
        semester: cells[8 + offset]?.textContent?.trim() || '',
        semester_ta: cells[9 + offset]?.textContent?.trim() || '',
        kegiatan: cells[10 + offset]?.textContent?.trim() || '',
        penyelenggara: cells[11 + offset]?.textContent?.trim() || '',
        mitra: cells[12 + offset]?.textContent?.trim() || '',
        dosen_pembimbing: cells[13 + offset]?.textContent?.trim() || '',
        sks: cells[14 + offset]?.textContent?.trim() || '0',
    };

    document.getElementById('editRowId').value = id;
    document.getElementById('editTahunAjaran').value = editRowData.tahun_ajaran;
    document.getElementById('editSemester').value = editRowData.semester;
    document.getElementById('editSemesterTa').value = editRowData.semester_ta;
    document.getElementById('editKegiatan').value = editRowData.kegiatan === '-' ? '' : editRowData.kegiatan;
    document.getElementById('editPenyelenggara').value = editRowData.penyelenggara;
    document.getElementById('editMitra').value = editRowData.mitra === '-' ? '' : editRowData.mitra;
    document.getElementById('editDosen').value = editRowData.dosen_pembimbing === '-' ? '' : editRowData.dosen_pembimbing;
    document.getElementById('editSks').value = parseInt(editRowData.sks) || 0;

    document.getElementById('editModalOverlay').classList.add('show');
}

function closeEditModal() {
    document.getElementById('editModalOverlay').classList.remove('show');
}

function saveEdit() {
    const id = document.getElementById('editRowId').value;
    const btn = document.getElementById('editSaveBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';

    const payload = {
        kegiatan_nama: document.getElementById('editKegiatan').value.trim(),
        mitra_nama: document.getElementById('editMitra').value.trim(),
        penyelenggara: document.getElementById('editPenyelenggara').value,
        semester: document.getElementById('editSemester').value,
        semester_ta: document.getElementById('editSemesterTa').value.trim(),
        tahun_ajaran: document.getElementById('editTahunAjaran').value.trim(),
        dosen_pembimbing: document.getElementById('editDosen').value || null,
        sks: parseInt(document.getElementById('editSks').value) || 0,
    };

    fetch(`/api/data-plps/${id}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(payload)
    })
    .then(async res => {
        const data = await res.json();
        if (!res.ok) {
            throw data;
        }
        return data;
    })
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save"></i> Simpan';
        if (data.success) {
            closeEditModal();
            showToast('Data berhasil diperbarui');
            resetAndLoadTable();
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save"></i> Simpan';
        let errMsg = err.message || 'Gagal menyimpan data';
        if (err.errors) {
            errMsg = Object.values(err.errors).flat().join('\n');
        }
        showDashboardErrorModal('Kesalahan Validasi', errMsg);
    });
}

// === Export Modal ===
function openExportModal() {
    document.getElementById('exportModalOverlay').classList.add('show');
}

function closeExportModal() {
    document.getElementById('exportModalOverlay').classList.remove('show');
}

function doExport(type) {
    const qs = window.location.search;
    if (type === 'excel') {
        window.location.href = '/api/export-excel' + qs;
    } else {
        window.open('/api/export-pdf' + qs, '_blank');
    }
    closeExportModal();
}

// Attach globally since they are used via HTML onclick
window.loadTablePage = loadTablePage;
window.resetAndLoadTable = resetAndLoadTable;
window.prevPage = prevPage;
window.nextPage = nextPage;
window.toggleRowSelect = toggleRowSelect;
window.toggleSelectAll = toggleSelectAll;
window.clearAllSelections = clearAllSelections;
window.bulkDeleteSelected = bulkDeleteSelected;
window.closeDeleteModal = closeDeleteModal;
window.executeBulkDelete = executeBulkDelete;
window.closeDashboardErrorModal = closeDashboardErrorModal;
window.openEditModal = openEditModal;
window.closeEditModal = closeEditModal;
window.saveEdit = saveEdit;
window.openExportModal = openExportModal;
window.closeExportModal = closeExportModal;
window.doExport = doExport;
