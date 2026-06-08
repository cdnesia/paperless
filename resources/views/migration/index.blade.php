@extends('layouts.app')
@section('title', 'Migrasi Data')
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">

            {{-- Header --}}
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="mb-0 small fw-bold text-uppercase text-muted">
                        <i class="fi fi-rr-database me-1"></i> Migrasi Data
                    </h5>
                </div>
                <span class="badge bg-warning text-dark" style="font-size:10px;">
                    <i class="fi fi-rr-shield-exclamation me-1"></i> Super Admin
                </span>
            </div>

            {{-- Step Indicator --}}
            <div class="d-flex align-items-center gap-2 mb-3" id="stepIndicator" style="font-size:11px;">
                <span class="step-dot active" data-step="1">1</span>
                <span class="step-txt active" data-step="1">Overview</span>
                <span class="text-muted mx-1">›</span>
                <span class="step-dot" data-step="2">2</span>
                <span class="step-txt" data-step="2">Mapping</span>
                <span class="text-muted mx-1">›</span>
                <span class="step-dot" data-step="3">3</span>
                <span class="step-txt" data-step="3">Field</span>
                <span class="text-muted mx-1">›</span>
                <span class="step-dot" data-step="4">4</span>
                <span class="step-txt" data-step="4">Eksekusi</span>
            </div>

            {{-- Step 1: Overview --}}
            <div id="step1" class="step-content">
                {{-- Connection Status --}}
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header border-0 py-3">
                        <h6 class="card-title mb-0 small fw-bold text-uppercase text-muted">
                            <i class="fi fi-rr-plug me-1"></i> Koneksi Database Lama
                        </h6>
                    </div>
                    <div class="card-body">
                        @if ($connectionOk)
                            <div class="d-flex align-items-center gap-2 text-success">
                                <i class="fi fi-rr-check-circle fs-4"></i>
                                <div>
                                    <strong>Terhubung!</strong><br>
                                    <small class="text-muted">
                                        <code>{{ config('database.connections.plo_old.database') }}@</code><code>{{ config('database.connections.plo_old.host') }}</code>
                                    </small>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-danger mb-0">
                                <i class="fi fi-rr-exclamation-triangle me-1"></i>
                                <strong>Tidak dapat terhubung ke <code>plo_old</code>.</strong><br>
                                <small>Periksa <code>DB_HOST_1</code>, <code>DB_DATABASE_1</code> di <code>.env</code></small>
                            </div>
                        @endif
                    </div>
                </div>

                @if ($connectionOk)
                {{-- Stats --}}
                <div class="row g-3 mb-3">
                    @php
                    $statCards = [
                        ['label' => 'Pengguna', 'value' => $stats['users'], 'table' => $stats['user_table'], 'icon' => 'fi-rr-users', 'color' => 'primary'],
                        ['label' => 'Surat', 'value' => $stats['surat'], 'table' => $stats['surat_table'], 'icon' => 'fi-rr-envelope', 'color' => 'info'],
                        ['label' => 'Disposisi', 'value' => $stats['disposisi'], 'table' => $stats['disposisi_table'], 'icon' => 'fi-rr-share', 'color' => 'success'],
                        ['label' => 'File/Lampiran', 'value' => $stats['files'], 'table' => $stats['file_table'], 'icon' => 'fi-rr-file-pdf', 'color' => 'warning'],
                    ];
                    @endphp
                    @foreach ($statCards as $card)
                    <div class="col-xl-3 col-md-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <p class="text-muted small mb-1">{{ $card['label'] }}</p>
                                        <h2 class="mb-0 fw-bold">{{ $card['value'] }}</h2>
                                    </div>
                                    <div class="rounded-circle bg-{{ $card['color'] }} bg-opacity-10 d-flex align-items-center justify-content-center"
                                        style="width:44px;height:44px;">
                                        <i class="fi {{ $card['icon'] }} text-{{ $card['color'] }} fs-5"></i>
                                    </div>
                                </div>
                                <small class="text-muted">Tabel: <code>{{ $card['table'] ?? '?' }}</code></small>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                {{-- Table List --}}
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header border-0 py-2 d-flex justify-content-between align-items-center">
                        <h6 class="card-title mb-0 small fw-bold text-uppercase text-muted">
                            <i class="fi fi-rr-table me-1"></i> Daftar Tabel di plo_old
                        </h6>
                        <span class="badge bg-light text-dark">{{ count($tables) }} tabel</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0 small">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3" style="width:40px;">#</th>
                                    <th>Nama Tabel</th>
                                    <th class="text-end pe-3" style="width:160px;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $detected = array_filter([$stats['user_table'], $stats['surat_table'], $stats['disposisi_table'], $stats['file_table']]); @endphp
                                @foreach ($tables as $i => $table)
                                @php $isDetected = in_array($table, $detected); @endphp
                                <tr>
                                    <td class="ps-3 text-muted">{{ $i + 1 }}</td>
                                    <td>
                                        <code>{{ $table }}</code>
                                        @if ($isDetected)
                                            <span class="badge bg-info text-dark ms-1">terdeteksi</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-3">
                                        @if ($isDetected)
                                            <span class="text-success small">
                                                <i class="fi fi-rr-check"></i> Akan dimigrasi
                                            </span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="text-end">
                    <button class="btn btn-primary" id="btnNextStep">
                        <i class="fi fi-rr-arrow-right me-1"></i> Lanjut ke Mapping Pengguna
                    </button>
                </div>

                {{-- Sample Data Preview --}}
                @if (!empty($sampleUsers))
                <div class="card border-0 shadow-sm mt-3">
                    <div class="card-header border-0 py-2 d-flex justify-content-between align-items-center">
                        <h6 class="card-title mb-0 small fw-bold text-uppercase text-muted">
                            <i class="fi fi-rr-eye me-1"></i> Sample — {{ $stats['user_table'] ?? 'users' }}
                        </h6>
                        <span class="badge bg-light text-dark small">{{ count($oldColumns) }} kolom</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 260px; overflow-y: auto;">
                            <table class="table table-sm small mb-0" style="min-width: 800px;">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th style="width:30px;">#</th>
                                        @foreach ($oldColumns as $col)
                                            <th class="text-nowrap">
                                                <code>{{ $col }}</code>
                                                @php
                                                    $h = '';
                                                    if (preg_match('/^(id|user_id|pengirim|sender|created_by|dari)$/i', $col)) $h = '→ID';
                                                    elseif (preg_match('/^(name|nama|fullname)/i', $col)) $h = '→name';
                                                    elseif (preg_match('/^(email|mail)/i', $col)) $h = '→email';
                                                    elseif (preg_match('/^(jabatan|position)/i', $col)) $h = '→role?';
                                                    elseif (preg_match('/^(no_hp|phone|telp|telegram)/i', $col)) $h = '→tg?';
                                                @endphp
                                                @if ($h)
                                                    <span class="text-info" style="font-size:9px;">{{ $h }}</span>
                                                @endif
                                            </th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($sampleUsers as $i => $user)
                                    <tr>
                                        <td class="text-muted">{{ $i + 1 }}</td>
                                        @foreach ($oldColumns as $col)
                                            <td class="text-nowrap" style="max-width:160px; overflow:hidden; text-overflow:ellipsis;">
                                                @php $val = $user[$col] ?? null; @endphp
                                                @if (is_null($val))
                                                    <span class="text-muted">NULL</span>
                                                @elseif (is_bool($val))
                                                    {{ $val ? '✅' : '❌' }}
                                                @else
                                                    <span title="{{ is_scalar($val) ? $val : json_encode($val) }}">{{ is_scalar($val) ? $val : '[json]' }}</span>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endif

                @endif
            </div>

            {{-- Step 2: Mapping (AJAX) --}}

            {{-- Step 2: Mapping (AJAX) --}}
            <div id="step2" class="step-content d-none"></div>

            {{-- Step 3: Field Mapping (AJAX) --}}
            <div id="step3" class="step-content d-none"></div>

            {{-- Step 4: Execute (AJAX) --}}
            <div id="step4" class="step-content d-none"></div>

        </div>
    </div>
</div>

<style>
.step-dot {
    display: inline-flex; align-items: center; justify-content: center; position: relative;
    width: 18px; height: 18px; border-radius: 50%;
    background: #e9ecef; font-size: 10px; font-weight: 700; color: #6b7280;
    transition: all 0.2s;
}
.step-txt { color: #9ca3af; font-weight: 600; transition: all 0.2s; }
.step-dot.active { background: var(--bs-primary, #0d6efd); color: #fff; }
.step-txt.active { color: var(--bs-primary, #0d6efd); }
.step-dot.completed { background: #10b981; color: transparent; }
.step-dot.completed::after { content: '✓'; font-size: 10px; color: #fff; position: absolute; }
</style>
@endsection

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function() {

    function goToStep(step) {
        document.querySelectorAll('.step-content').forEach(function(el) { el.classList.add('d-none'); });
        var target = document.getElementById('step' + step);
        if (target) target.classList.remove('d-none');

        document.querySelectorAll('.step-dot, .step-txt').forEach(function(el) {
            var s = parseInt(el.getAttribute('data-step') || 0);
            el.classList.remove('active', 'completed');
            if (s < step) el.classList.add('completed');
            if (s === step) el.classList.add('active');
        });

        if (step === 2) loadMapping();
        if (step === 3) loadFieldMapping();
        if (step === 4) loadExecute();
    }

    document.getElementById('btnNextStep').addEventListener('click', function() { goToStep(2); });

    document.addEventListener('migrationGoStep', function(e) { goToStep(e.detail.step); });

    function loadMapping() {
        var container = document.getElementById('step2');
        container.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary mb-3"></div><p class="text-muted">Memuat data mapping...</p></div>';
        fetch('{{ route("migration.mapping") }}', {
            headers: { 'Accept': 'text/html', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.text(); })
        .then(function(html) {
            container.innerHTML = html;
            // innerHTML doesn't execute <script> — re-create them manually
            runScripts(container);
            setTimeout(function() {
                if (typeof window.initMappingEvents === 'function') window.initMappingEvents();
            }, 100);
        })
        .catch(function(err) {
            container.innerHTML = '<div class="alert alert-danger m-3"><i class="fi fi-rr-exclamation-triangle me-1"></i> Gagal: ' + err.message + '</div>';
        });
    }

    function loadFieldMapping() {
        var container = document.getElementById('step3');
        container.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary mb-3"></div><p class="text-muted">Memuat field mapping...</p></div>';
        fetch('{{ route("migration.field-mapping") }}', {
            headers: { 'Accept': 'text/html', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.text(); })
        .then(function(html) {
            container.innerHTML = html;
            runScripts(container);
        })
        .catch(function(err) {
            container.innerHTML = '<div class="alert alert-danger m-3"><i class="fi fi-rr-exclamation-triangle me-1"></i> Gagal: ' + err.message + '</div>';
        });
    }

    function loadExecute() {
        var container = document.getElementById('step3');
        container.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary mb-3"></div><p class="text-muted">Menyiapkan eksekusi...</p></div>';
        fetch('{{ route("migration.execute-page") }}', {
            headers: { 'Accept': 'text/html', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.text(); })
        .then(function(html) {
            container.innerHTML = html;
            runScripts(container);
        })
        .catch(function(err) {
            container.innerHTML = '<div class="alert alert-danger m-3"><i class="fi fi-rr-exclamation-triangle me-1"></i> Gagal: ' + err.message + '</div>';
        });
    }

    // Re-create <script> elements so they execute (innerHTML skips them)
    function runScripts(container) {
        container.querySelectorAll('script').forEach(function(oldScript) {
            var newScript = document.createElement('script');
            // Copy attributes
            Array.from(oldScript.attributes).forEach(function(attr) {
                newScript.setAttribute(attr.name, attr.value);
            });
            newScript.textContent = oldScript.textContent;
            oldScript.replaceWith(newScript);
        });
    }

});
</script>
@endpush
