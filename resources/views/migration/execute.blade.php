<div class="card border-0 shadow-sm mb-3">
    <div class="card-header border-0 py-3">
        <h6 class="card-title mb-0">
            <i class="fi fi-rr-rocket me-1"></i> Konfirmasi & Eksekusi Migrasi
        </h6>
    </div>
    <div class="card-body">
        {{-- Warning --}}
        <div class="alert alert-warning small d-flex align-items-start gap-2 mb-3">
            <i class="fi fi-rr-exclamation-triangle mt-1"></i>
            <div>
                <strong>Perhatian!</strong> Pastikan mapping pengguna (Step 2) dan field mapping (Step 3) sudah benar.<br>
                Data menggunakan <code>firstOrCreate</code> — tidak akan duplikasi jika dijalankan ulang.
            </div>
        </div>

        {{-- Checkboxes --}}
        <div class="border rounded p-3 mb-3">
            <h6 class="small fw-bold text-uppercase text-muted mb-3">
                <i class="fi fi-rr-list-check me-1"></i> Pilih Data
            </h6>
            <div class="row g-2">
                <div class="col-md-4">
                    <div class="form-check card border p-2 rounded">
                        <input class="form-check-input" type="checkbox" id="chkUsers" checked>
                        <label class="form-check-label small d-block" for="chkUsers">
                            <strong><i class="fi fi-rr-users me-1"></i> Pengguna</strong>
                            <br><span class="text-muted">Buat user baru dari data lama</span>
                        </label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-check card border p-2 rounded">
                        <input class="form-check-input" type="checkbox" id="chkSurat" checked>
                        <label class="form-check-label small d-block" for="chkSurat">
                            <strong><i class="fi fi-rr-envelope me-1"></i> Surat</strong>
                            <br><span class="text-muted">Semua surat keluar/masuk</span>
                        </label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-check card border p-2 rounded">
                        <input class="form-check-input" type="checkbox" id="chkDisposisi" checked>
                        <label class="form-check-label small d-block" for="chkDisposisi">
                            <strong><i class="fi fi-rr-share me-1"></i> Disposisi</strong>
                            <br><span class="text-muted">Semua data disposisi</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        {{-- Summary --}}
        <div class="border rounded p-3 mb-3 bg-light">
            <h6 class="small fw-bold text-uppercase text-muted mb-2">
                <i class="fi fi-rr-info me-1"></i> Ringkasan
            </h6>
            <div class="row g-2 small">
                <div class="col-6 col-md-3">
                    <div class="bg-white rounded p-2 border text-center">
                        <div class="text-muted">User Dipetakan</div>
                        <strong class="text-success" id="summaryMapped">-</strong>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="bg-white rounded p-2 border text-center">
                        <div class="text-muted">User Baru</div>
                        <strong class="text-warning" id="summaryCreated">-</strong>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="bg-white rounded p-2 border text-center">
                        <div class="text-muted">Surat</div>
                        <strong id="summarySurat">{{ $stats['surat'] ?? 0 }}</strong>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="bg-white rounded p-2 border text-center">
                        <div class="text-muted">Disposisi</div>
                        <strong id="summaryDisposisi">{{ $stats['disposisi'] ?? 0 }}</strong>
                    </div>
                </div>
            </div>
        </div>

        {{-- Result Area --}}
        <div id="executeResult"></div>

        {{-- Buttons --}}
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary btn-sm" id="btnBackExecute">
                <i class="fi fi-rr-arrow-left me-1"></i> Kembali ke Field Mapping
            </button>
            <button class="btn btn-success btn-sm" id="btnExecute">
                <i class="fi fi-rr-rocket me-1"></i> Jalankan Migrasi
            </button>
        </div>
    </div>
</div>

<script>
(function() {
    'use strict';

    // Read mapping from step 2
    var mappingData = (typeof window.getCurrentMapping === 'function')
        ? window.getCurrentMapping()
        : '[]';
    var mapping = [];
    try { mapping = JSON.parse(mappingData); } catch(e) { mapping = []; }

    var mappedCount = mapping.filter(function(m) { return !m.create_new; }).length;
    var createdCount = mapping.filter(function(m) { return m.create_new; }).length;

    document.getElementById('summaryMapped').textContent = mappedCount;
    document.getElementById('summaryCreated').textContent = createdCount;

    // Back to step 2
    document.getElementById('btnBackExecute').addEventListener('click', function() {
        document.dispatchEvent(new CustomEvent('migrationGoStep', { detail: { step: 3 } }));
    });

    // Run migration
    document.getElementById('btnExecute').addEventListener('click', function() {
        var btn = this;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Memproses...';

        var resultDiv = document.getElementById('executeResult');
        resultDiv.innerHTML = '';

        var csrf = document.querySelector('meta[name="csrf-token"]');
        var token = csrf ? csrf.getAttribute('content') : '';

        fetch('{{ route("migration.execute") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                user_mapping: mappingData,
                field_mapping: (typeof window.getFieldMapping === 'function') ? window.getFieldMapping() : '{}',
                migrate_users: document.getElementById('chkUsers').checked,
                migrate_surat: document.getElementById('chkSurat').checked,
                migrate_disposisi: document.getElementById('chkDisposisi').checked
            })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                var html = '<div class="card border-success shadow-sm mb-0"><div class="card-body text-center p-4">';
                html += '<i class="fi fi-rr-check-circle text-success" style="font-size:64px;"></i>';
                html += '<h4 class="mt-3 text-success">Migrasi Berhasil!</h4>';
                html += '<div class="row g-2 mt-3 text-start">';
                html += '<div class="col-6"><div class="border rounded p-2 small text-center"><span class="text-muted">User Dibuat</span><br><strong>' + (data.results.users_created || 0) + '</strong></div></div>';
                html += '<div class="col-6"><div class="border rounded p-2 small text-center"><span class="text-muted">User Dipetakan</span><br><strong>' + (data.results.users_mapped || 0) + '</strong></div></div>';
                html += '<div class="col-6"><div class="border rounded p-2 small text-center"><span class="text-muted">Surat</span><br><strong>' + (data.results.surat_migrated || 0) + '</strong></div></div>';
                html += '<div class="col-6"><div class="border rounded p-2 small text-center"><span class="text-muted">Disposisi</span><br><strong>' + (data.results.disposisi_migrated || 0) + '</strong></div></div>';
                html += '</div>';
                if (data.results.errors && data.results.errors.length > 0) {
                    html += '<div class="alert alert-warning mt-3 small text-start mb-0"><strong>Peringatan:</strong><br>' + data.results.errors.join('<br>') + '</div>';
                }
                html += '</div></div>';
                resultDiv.innerHTML = html;
                btn.innerHTML = '<i class="fi fi-rr-check me-1"></i> Selesai';
            } else {
                resultDiv.innerHTML = '<div class="alert alert-danger"><i class="fi fi-rr-exclamation-triangle me-1"></i> ' + data.message + '</div>';
                btn.disabled = false;
                btn.innerHTML = '<i class="fi fi-rr-rocket me-1"></i> Coba Lagi';
            }
        })
        .catch(function(err) {
            resultDiv.innerHTML = '<div class="alert alert-danger"><i class="fi fi-rr-exclamation-triangle me-1"></i> Gagal: ' + err.message + '</div>';
            btn.disabled = false;
            btn.innerHTML = '<i class="fi fi-rr-rocket me-1"></i> Coba Lagi';
        });
    });

})();
</script>
