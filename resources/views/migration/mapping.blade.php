<div class="card border-0 shadow-sm">
    <div class="card-header border-0 py-3 d-flex justify-content-between align-items-center">
        <h6 class="card-title mb-0">
            <i class="fi fi-rr-user-connection me-1"></i> Mapping Pengguna Lama → Baru
        </h6>
        <div class="d-flex gap-2 align-items-center">
            <span class="badge bg-info">{{ count($suggestions) }} user lama</span>
            <span class="badge bg-success" id="badgeMapped" style="display:none;">
                <span id="mappedBadgeCount">0</span> dipetakan
            </span>
        </div>
    </div>

    <div class="card-body pt-0">
        {{-- Toolbar --}}
        <div class="d-flex gap-2 mb-3 pt-2">
            <button class="btn btn-outline-primary btn-sm" id="btnAutoMap">
                <i class="fi fi-rr-magic-wand me-1"></i> Auto-Map by Email
            </button>
            <button class="btn btn-outline-warning btn-sm" id="btnCreateAll">
                <i class="fi fi-rr-user-plus me-1"></i> Buat Semua Baru
            </button>
            <button class="btn btn-outline-danger btn-sm ms-auto" id="btnClearMap">
                <i class="fi fi-rr-eraser me-1"></i> Reset
            </button>
        </div>

        {{-- Table --}}
        @if (count($suggestions) > 0)
        <div class="table-responsive" style="max-height: 55vh; overflow-y: auto;">
            <table class="table table-sm align-middle mb-0" style="min-width: 700px;">
                <thead class="table-light sticky-top small">
                    <tr>
                        <th style="width:35px;">#</th>
                        <th style="width:35%;">Pengguna Lama</th>
                        <th style="width:35px;">→</th>
                        <th style="width:35%;">Mapping ke</th>
                        <th style="width:100px;">Status</th>
                    </tr>
                </thead>
                <tbody class="small">
                    @foreach ($suggestions as $i => $row)
                    @php $old = $row['old']; @endphp
                    <tr class="mapping-row" data-old-id="{{ $old->id ?? '' }}">
                        <td class="text-muted text-center">{{ $i + 1 }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center flex-shrink-0"
                                    style="width:28px;height:28px;font-size:11px;font-weight:700;color:var(--bs-primary);">
                                    {{ strtoupper(mb_substr($old->name ?? $old->nama ?? '?', 0, 1)) }}
                                </div>
                                <div class="overflow-hidden">
                                    <div class="fw-semibold text-truncate">{{ $old->name ?? $old->nama ?? 'Tanpa Nama' }}</div>
                                    <div class="text-muted text-truncate user-old-email" style="font-size:11px;">{{ $old->email ?? 'Tanpa email' }}</div>
                                    @if (!empty($old->jabatan) || !empty($old->unit_kerja) || !empty($old->department))
                                        <div class="text-muted text-truncate" style="font-size:10px;">{{ $old->jabatan ?? $old->unit_kerja ?? $old->department ?? '' }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="text-center text-muted">→</td>
                        <td>
                            <select class="form-select form-select-sm user-map" style="font-size:11px;"
                                data-old-id="{{ $old->id ?? '' }}">
                                <option value="">-- Lewati --</option>
                                <option value="__create__" {{ empty($row['suggested_new_id']) ? 'selected' : '' }}>✨ Buat baru</option>
                                <option disabled>──────────</option>
                                @foreach ($newUsers as $nu)
                                    <option value="{{ $nu->id }}"
                                        {{ isset($row['suggested_new_id']) && $row['suggested_new_id'] == $nu->id ? 'selected' : '' }}>
                                        {{ $nu->name }} ({{ $nu->email }})
                                    </option>
                                @endforeach
                            </select>
                            @if (!empty($row['suggested_new_id']) && !empty($row['suggested_new_name']))
                                <div class="text-success mt-1" style="font-size:10px;">
                                    <i class="fi fi-rr-check"></i> {{ $row['suggested_new_name'] }}
                                </div>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="map-status badge bg-secondary" style="font-size:10px;">—</span>
                            <span class="map-status-mapped badge bg-success d-none" style="font-size:10px;">
                                <i class="fi fi-rr-check"></i>
                            </span>
                            <span class="map-status-create badge bg-warning text-dark d-none" style="font-size:10px;">
                                <i class="fi fi-rr-plus"></i> Baru
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
            <div class="text-center py-5 text-muted">
                <i class="fi fi-rr-database" style="font-size:48px;"></i>
                <p class="mt-2">Tidak ada pengguna di database lama.</p>
            </div>
        @endif
    </div>

    {{-- Footer --}}
    <div class="card-footer border-top bg-light py-2 px-3">
        <div class="d-flex justify-content-between align-items-center">
            <button class="btn btn-outline-secondary btn-sm" id="btnBackMapping">
                <i class="fi fi-rr-arrow-left me-1"></i> Kembali
            </button>
            <div class="d-flex align-items-center gap-3">
                <small class="text-muted">
                    <strong id="mappedCount">0</strong>/<strong>{{ count($suggestions) }}</strong> dipetakan
                </small>
                <button class="btn btn-primary btn-sm" id="btnGoFieldMap">
                    <i class="fi fi-rr-arrow-right me-1"></i> Lanjut Field Mapping
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    'use strict';
    var allSelects = document.querySelectorAll('.user-map');

    function syncStatus(sel) {
        var row = sel.closest('tr');
        var val = sel.value;
        row.querySelector('.map-status').classList.toggle('d-none', val !== '');
        row.querySelector('.map-status-mapped').classList.toggle('d-none', !val || val === '__create__');
        row.querySelector('.map-status-create').classList.toggle('d-none', val !== '__create__');
        row.style.background = val === '__create__' ? '#fffdf5' : (val ? '#f5fdf8' : '');
    }

    function countMapped() {
        var c = 0;
        allSelects.forEach(function(s) { if (s.value) c++; });
        var el = document.getElementById('mappedCount');
        if (el) el.textContent = c;
        var b = document.getElementById('badgeMapped');
        if (b) {
            document.getElementById('mappedBadgeCount').textContent = c;
            b.style.display = c > 0 ? '' : 'none';
        }
    }

    window.getCurrentMapping = function() {
        var m = [];
        allSelects.forEach(function(s) {
            if (s.value) m.push({
                old_id: s.getAttribute('data-old-id'),
                new_id: s.value === '__create__' ? null : parseInt(s.value, 10),
                create_new: s.value === '__create__'
            });
        });
        return JSON.stringify(m);
    };

    allSelects.forEach(function(s) {
        s.addEventListener('change', function() { syncStatus(s); countMapped(); });
        syncStatus(s);
    });
    countMapped();

    document.getElementById('btnBackMapping').addEventListener('click', function() {
        document.dispatchEvent(new CustomEvent('migrationGoStep', { detail: { step: 1 } }));
    });

    document.getElementById('btnGoFieldMap').addEventListener('click', function() {
        var c = 0;
        allSelects.forEach(function(s) { if (s.value) c++; });
        if (!c) return alert('Belum ada pengguna yang dipetakan.');
        document.dispatchEvent(new CustomEvent('migrationGoStep', { detail: { step: 3 } }));
    });

    document.getElementById('btnAutoMap').addEventListener('click', function() {
        allSelects.forEach(function(s) {
            var row = s.closest('tr');
            var emailEl = row.querySelector('.user-old-email');
            var oldEmail = (emailEl ? emailEl.textContent : '').trim().toLowerCase();
            if (!oldEmail || oldEmail === 'tanpa email') {
                s.value = '__create__';
            } else {
                var found = false;
                Array.from(s.options).forEach(function(o) {
                    if (o.disabled || !o.value || o.value === '__create__') return;
                    var m = o.text.match(/\(([^)]+)\)/);
                    if (m && m[1].toLowerCase().trim() === oldEmail) { s.value = o.value; found = true; }
                });
                if (!found) s.value = '__create__';
            }
            syncStatus(s);
        });
        countMapped();
    });

    document.getElementById('btnCreateAll').addEventListener('click', function() {
        if (!confirm('Semua ' + allSelects.length + ' user lama akan dibuat baru. Lanjutkan?')) return;
        allSelects.forEach(function(s) { s.value = '__create__'; syncStatus(s); });
        countMapped();
    });

    document.getElementById('btnClearMap').addEventListener('click', function() {
        allSelects.forEach(function(s) { s.value = ''; syncStatus(s); });
        countMapped();
    });

    window.initMappingEvents = function() { countMapped(); };
})();
</script>
