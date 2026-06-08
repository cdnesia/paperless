<div class="card border-0 shadow-sm">
    <div class="card-header border-0 py-3 d-flex justify-content-between align-items-center">
        <h6 class="card-title mb-0">
            <i class="fi fi-rr-exchange me-1"></i> Pemetaan Field (Kolom) per Tabel
        </h6>
        <span class="badge bg-info">{{ count($tables) }} tabel</span>
    </div>

    <div class="card-body pt-0">
        @if (empty($tables))
            <div class="text-center py-4 text-muted">
                <p class="mb-0">Tidak ada tabel yang terdeteksi di database lama.</p>
            </div>
        @else
            @foreach ($tables as $key => $tbl)
            <div class="border rounded mb-3 field-table-section" data-table="{{ $key }}">
                {{-- Table Header --}}
                <div class="d-flex align-items-center justify-content-between px-3 py-2 bg-light border-bottom">
                    <div>
                        <strong class="small">{{ $tbl['label'] }}</strong>
                        <code class="ms-2 small text-muted">{{ $tbl['name'] }}</code>
                    </div>
                    <button class="btn btn-outline-primary btn-sm auto-map-fields" data-table="{{ $key }}">
                        <i class="fi fi-rr-magic-wand me-1"></i> Auto-Detect
                    </button>
                </div>

                {{-- Field Mapping Rows --}}
                <div class="p-2">
                    <div class="table-responsive">
                        <table class="table table-sm small mb-0" style="min-width:550px;">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:35%;">Field Baru</th>
                                    <th style="width:5%;" class="text-center">←</th>
                                    <th style="width:35%;">Kolom Lama</th>
                                    <th style="width:25%;">Sample Data</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($tbl['suggestions'] as $newField => $candidates)
                                <tr>
                                    <td class="fw-semibold">
                                        <code class="text-dark">{{ $newField }}</code>
                                    </td>
                                    <td class="text-center text-muted">←</td>
                                    <td>
                                        <select class="form-select form-select-sm field-map" style="font-size:11px;"
                                            data-table="{{ $key }}" data-field="{{ $newField }}">
                                            <option value="">-- Kosongkan --</option>
                                            @foreach ($tbl['old_columns'] as $col)
                                                @php
                                                    $isMatch = in_array($col, $candidates);
                                                    $isFirst = $col === ($candidates[0] ?? null);
                                                @endphp
                                                <option value="{{ $col }}"
                                                    {{ $isFirst ? 'selected' : '' }}
                                                    {{ $isMatch && !$isFirst ? 'data-suggested="1"' : '' }}>
                                                    {{ $col }}
                                                    {{ $isMatch ? '⭐' : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="text-muted sample-data" style="font-size:10px;" data-table="{{ $key }}" data-field="{{ $newField }}">
                                        —
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endforeach
        @endif
    </div>

    {{-- Footer --}}
    <div class="card-footer border-top bg-light py-2 px-3">
        <div class="d-flex justify-content-between align-items-center">
            <button class="btn btn-outline-secondary btn-sm" id="btnBackFieldMap">
                <i class="fi fi-rr-arrow-left me-1"></i> Kembali ke Mapping
            </button>
            <button class="btn btn-success btn-sm" id="btnGoExecute">
                <i class="fi fi-rr-rocket me-1"></i> Lanjut Eksekusi
            </button>
        </div>
    </div>
</div>

<script>
(function() {
    'use strict';

    // Build field mapping JSON: { surat: { new_field: old_col }, disposisi: {...}, file: {...} }
    window.getFieldMapping = function() {
        var map = {};
        document.querySelectorAll('.field-map').forEach(function(sel) {
            var table = sel.dataset.table;
            var field = sel.dataset.field;
            if (!map[table]) map[table] = {};
            if (sel.value) map[table][field] = sel.value;
        });
        return JSON.stringify(map);
    };

    // Auto-detect: select first suggested match for each field
    document.querySelectorAll('.auto-map-fields').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var table = btn.dataset.table;
            document.querySelectorAll('.field-map[data-table="' + table + '"]').forEach(function(sel) {
                // Find first option with data-suggested or the first non-empty
                var best = sel.querySelector('option[data-suggested]');
                if (best) {
                    sel.value = best.value;
                } else {
                    // Keep current value
                }
                highlightRow(sel);
            });
        });
    });

    // Highlight matched rows
    function highlightRow(sel) {
        var row = sel.closest('tr');
        if (sel.value) {
            row.style.background = '#f5fdf8';
        } else {
            row.style.background = '';
        }
    }

    document.querySelectorAll('.field-map').forEach(function(sel) {
        sel.addEventListener('change', function() { highlightRow(sel); });
        highlightRow(sel);
    });

    // Load sample data (first row from old table)
    loadSamples();

    function loadSamples() {
        var samples = {};
        var fetches = [];

        document.querySelectorAll('.field-table-section').forEach(function(section) {
            var tableName = section.querySelector('code').textContent.trim();
            var tableKey = section.dataset.table;
            if (!tableName) return;

            var promise = fetch('', {
                // We can't fetch sample data from client-side — use a lightweight approach
                // Read the first option text from selects as placeholder
            });
        });

        // Show first value of each old column as hint
        document.querySelectorAll('.field-table-section').forEach(function(section) {
            var tableKey = section.dataset.table;
            var oldCols = [];
            section.querySelectorAll('.field-map').forEach(function(sel) {
                if (sel.value) oldCols.push(sel.value);
            });

            // Show "pilih kolom" hint
            document.querySelectorAll('.sample-data[data-table="' + tableKey + '"]').forEach(function(td) {
                td.textContent = '— (lihat setelah eksekusi)';
            });
        });
    }

    // Back to step 2
    document.getElementById('btnBackFieldMap').addEventListener('click', function() {
        document.dispatchEvent(new CustomEvent('migrationGoStep', { detail: { step: 2 } }));
    });

    // Go to execute (step 4)
    document.getElementById('btnGoExecute').addEventListener('click', function() {
        document.dispatchEvent(new CustomEvent('migrationGoStep', { detail: { step: 4 } }));
    });

    window.initFieldMappingEvents = function() {};
})();
</script>
