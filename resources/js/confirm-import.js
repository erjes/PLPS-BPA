document.addEventListener('DOMContentLoaded', function() {
    const confirmForm = document.getElementById('confirmForm');
    if (!confirmForm) return;

    // Use window.ConfirmImportData injected from Blade
    const config = window.ConfirmImportData || {};
    const tempPath = config.tempPath || '';
    const previewTotalRows = config.previewTotalRows || 0;
    const processChunkRoute = config.processChunkRoute || '';
    const csrfToken = config.csrfToken || '';
    const inputDataUrl = config.inputDataUrl || '/';

    confirmForm.addEventListener('submit', function(e) {
        e.preventDefault();

        // Show Progress Modal
        const progressModal = document.getElementById('progressModal');
        const progressTitle = document.getElementById('progressTitle');
        const progressStatus = document.getElementById('progressStatus');
        const progressBar = document.getElementById('progressBar');
        const progressPercent = document.getElementById('progressPercent');

        if(progressModal) progressModal.style.display = 'flex';
        if(progressTitle) progressTitle.textContent = "Menyimpan ke Database...";
        if(progressStatus) progressStatus.textContent = "Menyiapkan penyimpanan...";
        if(progressBar) progressBar.style.width = "0%";
        if(progressPercent) progressPercent.textContent = "0%";

        // Start importing in chunks
        const chunkSize = 1000;
        importChunk(tempPath, 2, chunkSize, previewTotalRows);
    });

    function importChunk(tempPath, offset, limit, totalRows) {
        const progressStatus = document.getElementById('progressStatus');
        const progressBar = document.getElementById('progressBar');
        const progressPercent = document.getElementById('progressPercent');

        // Update status text
        const endRow = Math.min(offset + limit - 1, totalRows + 1);
        if(progressStatus) progressStatus.textContent = `Menyimpan baris ${offset - 1} s/d ${endRow - 1} dari ${totalRows}...`;

        // Calculate percentage
        const processed = offset - 2;
        const percent = Math.floor((processed / totalRows) * 100);
        if(progressBar) progressBar.style.width = `${percent}%`;
        if(progressPercent) progressPercent.textContent = `${percent}%`;

        // Process chunk request
        fetch(processChunkRoute, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                temp_path: tempPath,
                mode: 'import',
                offset: offset,
                limit: limit
            })
        })
        .then(response => {
            if (!response.ok) {
                const contentType = response.headers.get("content-type");
                if (contentType && contentType.indexOf("application/json") !== -1) {
                    return response.json().then(err => { throw err; });
                } else {
                    return response.text().then(text => {
                        throw { message: `Kesalahan Server (${response.status}): Silakan periksa log server untuk detail.` };
                    });
                }
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                const nextOffset = offset + limit;
                if (nextOffset <= totalRows + 1) {
                    // Process next chunk
                    importChunk(tempPath, nextOffset, limit, totalRows);
                } else {
                    // Done! Update progress to 100%
                    if(progressBar) progressBar.style.width = "100%";
                    if(progressPercent) progressPercent.textContent = "100%";
                    
                    setTimeout(() => {
                        const modal = document.getElementById('progressModal');
                        if(modal) modal.style.display = 'none';
                        // Redirect back to input page with success message
                        window.location.href = inputDataUrl;
                    }, 400);
                }
            } else {
                throw { message: data.message || "Gagal menyimpan data." };
            }
        })
        .catch(err => {
            const modal = document.getElementById('progressModal');
            if(modal) modal.style.display = 'none';
            showGenericError(err.message || `Terjadi kesalahan saat menyimpan baris ${offset} - ${endRow}.`);
        });
    }

    function showGenericError(message) {
        const errorContent = document.getElementById('genericErrorContent');
        const errorModal = document.getElementById('genericErrorModal');
        if(errorContent) errorContent.textContent = message;
        if(errorModal) errorModal.style.display = 'flex';
    }

    // Pagination Logic
    const allRows = document.querySelectorAll('.preview-row');
    const totalRows = allRows.length;
    const perPage = 100;
    let currentPage = 1;
    const totalPages = Math.ceil(totalRows / perPage);

    function renderTable() {
        const start = (currentPage - 1) * perPage;
        const end = Math.min(start + perPage, totalRows);

        allRows.forEach((row, index) => {
            if (index >= start && index < end) {
                row.style.display = 'table-row';
            } else {
                row.style.display = 'none';
            }
        });

        const tableShown = document.getElementById('tableShown');
        const tableTotal = document.getElementById('tableTotal');
        const pageText = document.getElementById('pageText');

        if(tableShown) tableShown.textContent = Math.min(perPage, totalRows - start);
        if(tableTotal) tableTotal.textContent = totalRows;
        if(pageText) pageText.textContent = `${totalRows > 0 ? start + 1 : 0} - ${end} / ${totalRows}`;

        const prevBtn = document.getElementById('prevPageBtn');
        const nextBtn = document.getElementById('nextPageBtn');

        if (currentPage <= 1) {
            if(prevBtn) {
                prevBtn.disabled = true;
                prevBtn.style.opacity = '0.5';
            }
        } else {
            if(prevBtn) {
                prevBtn.disabled = false;
                prevBtn.style.opacity = '1';
            }
        }

        if (currentPage >= totalPages) {
            if(nextBtn) {
                nextBtn.disabled = true;
                nextBtn.style.opacity = '0.5';
            }
        } else {
            if(nextBtn) {
                nextBtn.disabled = false;
                nextBtn.style.opacity = '1';
            }
        }
    }

    // Attach to window so buttons can call them
    window.prevPage = function() {
        if (currentPage > 1) {
            currentPage--;
            renderTable();
        }
    };

    window.nextPage = function() {
        if (currentPage < totalPages) {
            currentPage++;
            renderTable();
        }
    };

    // Attach close button for error modal
    const closeBtns = document.querySelectorAll('.close-btn, .btn-red');
    closeBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const errorModal = document.getElementById('genericErrorModal');
            if(errorModal) errorModal.style.display = 'none';
        });
    });

    // Initial render
    if (totalRows > 0) {
        renderTable();
    }
});
