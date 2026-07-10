const uploadZone = document.getElementById('uploadZone');
const fileInput = document.getElementById('fileInput');
const uploadProgress = document.getElementById('uploadProgress');
const uploadForm = document.getElementById('uploadForm');

if (uploadZone && fileInput) {
    // Drag & Drop
    ['dragenter', 'dragover'].forEach(e => {
        uploadZone.addEventListener(e, ev => { ev.preventDefault(); uploadZone.classList.add('dragover'); });
    });
    ['dragleave', 'drop'].forEach(e => {
        uploadZone.addEventListener(e, ev => { ev.preventDefault(); uploadZone.classList.remove('dragover'); });
    });
    uploadZone.addEventListener('drop', e => {
        const file = e.dataTransfer.files[0];
        if (file) {
            const dt = new DataTransfer();
            dt.items.add(file);
            fileInput.files = dt.files;
            showFile(file);
        }
    });

    // File input change
    fileInput.addEventListener('change', function() {
        if (this.files[0]) showFile(this.files[0]);
    });
}

function showFile(file) {
    document.getElementById('fileName').textContent = file.name;
    document.getElementById('fileSize').textContent = formatSize(file.size);
    uploadProgress.classList.add('show');
    uploadZone.style.display = 'none';
}

function clearFile() {
    fileInput.value = '';
    uploadProgress.classList.remove('show');
    uploadZone.style.display = '';
}

function formatSize(bytes) {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / 1048576).toFixed(1) + ' MB';
}

// Intercept form submit for AJAX chunk validation
if (uploadForm) {
    uploadForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const file = fileInput.files[0];
        if (!file) return;

        // Show Progress Modal
        const progressModal = document.getElementById('progressModal');
        const progressTitle = document.getElementById('progressTitle');
        const progressStatus = document.getElementById('progressStatus');
        const progressBar = document.getElementById('progressBar');
        const progressPercent = document.getElementById('progressPercent');

        progressModal.style.display = 'flex';
        progressTitle.textContent = "Memvalidasi Data...";
        progressStatus.textContent = "Mengunggah berkas ke server...";
        progressBar.style.width = "0%";
        progressPercent.textContent = "0%";

        // Check file size limit (10MB matching backend validation)
        const maxSize = 10 * 1024 * 1024; // 10MB
        if (file.size > maxSize) {
            progressModal.style.display = 'none';
            showGenericError(`Ukuran file terlalu besar (${formatSize(file.size)}). Maksimal ukuran file adalah 10 MB.`);
            return;
        }

        // Prepare upload data
        const formData = new FormData();
        formData.append('file', file);
        formData.append('_token', window.InputConfig.csrfToken);

        fetch(window.InputConfig.uploadRoute, {
            method: 'POST',
            body: formData,
            headers: {
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                const contentType = response.headers.get("content-type");
                if (response.status === 413) {
                    throw { message: 'Ukuran file terlalu besar (Content Too Large). Konfigurasi server PHP (post_max_size / upload_max_filesize) Anda menolak file ini.' };
                }
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
                const totalRows = data.total_rows;
                const tempPath = data.temp_path;

                if (totalRows <= 0) {
                    throw { message: "Berkas Excel tidak memiliki baris data (kosong)." };
                }

                // Start validating in chunks
                const chunkSize = 1000;
                const allErrors = [];
                validateChunk(tempPath, 2, chunkSize, totalRows, allErrors);
            } else {
                throw { message: data.message || "Gagal mengunggah berkas." };
            }
        })
        .catch(err => {
            progressModal.style.display = 'none';
            let errMsg = err.message || "Terjadi kesalahan koneksi saat mengunggah berkas.";
            if (errMsg.includes('Failed to fetch') || errMsg.includes('NetworkError')) {
                errMsg = "Koneksi terputus. Hal ini biasanya terjadi jika file Excel terlalu besar melebihi batas konfigurasi PHP (post_max_size / upload_max_filesize) pada server Anda.";
            }
            showGenericError(errMsg);
        });
    });
}

function validateChunk(tempPath, offset, limit, totalRows, allErrors) {
    const progressStatus = document.getElementById('progressStatus');
    const progressBar = document.getElementById('progressBar');
    const progressPercent = document.getElementById('progressPercent');

    // Update status text
    const endRow = Math.min(offset + limit - 1, totalRows + 1);
    progressStatus.textContent = `Memvalidasi baris ${offset - 1} s/d ${endRow - 1} dari ${totalRows}...`;

    // Calculate percentage
    const processed = offset - 2;
    const percent = Math.floor((processed / totalRows) * 100);
    progressBar.style.width = `${percent}%`;
    progressPercent.textContent = `${percent}%`;

    // Process chunk request
    fetch(window.InputConfig.processChunkRoute, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': window.InputConfig.csrfToken
        },
        body: JSON.stringify({
            temp_path: tempPath,
            mode: 'validate',
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
            // Collect any validation errors
            if (data.errors && data.errors.length > 0) {
                allErrors.push(...data.errors);
            }

            const nextOffset = offset + limit;
            if (nextOffset <= totalRows + 1) {
                // Process next chunk
                validateChunk(tempPath, nextOffset, limit, totalRows, allErrors);
            } else {
                // Done! Update progress to 100%
                progressBar.style.width = "100%";
                progressPercent.textContent = "100%";
                
                setTimeout(() => {
                    document.getElementById('progressModal').style.display = 'none';

                    if (allErrors.length > 0) {
                        // Render and show errors modal
                        renderValidationErrors(allErrors);
                    } else {
                        // All chunks validated successfully! Redirect to confirm page
                        window.location.href = window.InputConfig.confirmShowRoute;
                    }
                }, 400);
            }
        } else {
            throw { message: data.message || "Gagal memproses validasi data." };
        }
    })
    .catch(err => {
        document.getElementById('progressModal').style.display = 'none';
        showGenericError(err.message || `Terjadi kesalahan saat memvalidasi baris ${offset} - ${endRow}.`);
    });
}

function renderValidationErrors(errors) {
    const errorCountText = document.getElementById('errorCountText');
    const errorTableBody = document.getElementById('errorTableBody');

    errorCountText.textContent = `${errors.length} error ditemukan`;
    errorTableBody.innerHTML = '';

    errors.forEach(error => {
        const splitIndex = error.indexOf(': ');
        let lineNum = '';
        let message = error;
        
        if (splitIndex !== -1 && error.startsWith('Baris')) {
            lineNum = error.substring(0, splitIndex).replace('Baris ', '');
            message = error.substring(splitIndex + 2);
        }

        const isSimilarity = error.includes('Kemungkinan typo') || error.includes('kemiripan');

        const tr = document.createElement('tr');
        if (isSimilarity) tr.style.background = '#fffbeb';

        tr.innerHTML = `
            <td><span class="error-badge" ${isSimilarity ? 'style="background:#fef3c7;color:#b45309"' : ''}>Baris ${lineNum}</span></td>
            <td style="color:#374151;line-height:1.5">
                ${isSimilarity ? '<i class="fas fa-exclamation-triangle" style="color:#d97706;margin-right:4px"></i>' : ''}
                ${message}
            </td>
        `;
        errorTableBody.appendChild(tr);
    });

    document.getElementById('errorModal').style.display = 'flex';
}

function showGenericError(message) {
    document.getElementById('genericErrorContent').textContent = message;
    document.getElementById('genericErrorModal').style.display = 'flex';
}

window.clearFile = clearFile;
