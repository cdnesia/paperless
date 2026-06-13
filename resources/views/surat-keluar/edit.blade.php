@extends('layouts.app')
@section('title', 'Detail Surat Keluar')
@section('content')
    @php $isLocked = in_array($suratKeluar->status, ['s', 'e']); @endphp
    <div class="container-fluid">
        <div class="app-page-head d-flex mb-2 flex-wrap align-items-center justify-content-between">
            <div class="clearfix mb-2">
                <h6 class="app-page-title">
                    <i class="fi fi-rr-file-edit me-1"></i>
                    Edit Surat Keluar
                </h6>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header border-0 pb-2">
                        <h6 class="card-title mb-0">
                            <i class="fi fi-rr-document-signed me-1"></i> Form Edit Surat Keluar
                        </h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('surat-keluar.update', $suratKeluar) }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            {{-- HEADER SURAT --}}
                            <div class="border rounded p-3 mb-3 bg-light">
                                <h6 class="text-uppercase text-muted small mb-3 fw-bold">
                                    <i class="fi fi-rr-memo-circle-check me-1"></i> Informasi Surat
                                </h6>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="nomor_surat" class="form-label small fw-semibold">Nomor Surat <span
                                            class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-sm @error('nomor_surat') is-invalid @enderror"
                                            id="nomor_surat" name="nomor_surat"
                                            value="{{ old('nomor_surat', $suratKeluar->nomor_surat) }}"
                                            placeholder="Contoh: 001/UN-XX/SK/2026"
                                            {{ $isLocked ? 'readonly' : '' }}>
                                        @error('nomor_surat')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="tanggal_surat" class="form-label small fw-semibold">Tanggal Surat <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control form-control-sm @error('tanggal_surat') is-invalid @enderror"
                                            id="tanggal_surat" name="tanggal_surat"
                                            value="{{ old('tanggal_surat', $suratKeluar->tanggal_surat ? $suratKeluar->tanggal_surat->format('Y-m-d') : '') }}"
                                            {{ $isLocked ? 'readonly' : '' }}>
                                        @error('tanggal_surat')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-12">
                                        <label for="perihal" class="form-label small fw-semibold">Perihal / Subjek <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-sm @error('perihal') is-invalid @enderror"
                                            id="perihal" name="perihal" value="{{ old('perihal', $suratKeluar->perihal) }}"
                                            placeholder="Contoh: Undangan Rapat Koordinasi Program Studi"
                                            {{ $isLocked ? 'readonly' : '' }}>
                                        @error('perihal')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="jenis_surat" class="form-label small fw-semibold">Klasifikasi Surat <span class="text-danger">*</span></label>
                                        <select class="form-select form-select-sm select2 @error('jenis_surat') is-invalid @enderror"
                                            id="jenis_surat" name="jenis_surat" {{ $isLocked ? 'disabled' : '' }}>
                                            <option value="">— Pilih Klasifikasi —</option>
                                            <option value="internal"
                                                {{ old('jenis_surat', $suratKeluar->jenis_surat) == 'internal' ? 'selected' : '' }}>📂 Internal (Lingkup Universitas)</option>
                                            <option value="eksternal"
                                                {{ old('jenis_surat', $suratKeluar->jenis_surat) == 'eksternal' ? 'selected' : '' }}>📤 Eksternal (Pihak Luar)</option>
                                            <option value="broadcast"
                                                {{ old('jenis_surat', $suratKeluar->jenis_surat) == 'broadcast' ? 'selected' : '' }}>📢 Broadcast (Semua Unit)</option>
                                        </select>
                                        @if ($isLocked)
                                            <input type="hidden" name="jenis_surat" value="{{ $suratKeluar->jenis_surat }}">
                                        @endif
                                        @error('jenis_surat')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold">Status</label>
                                        <input type="text" class="form-control form-control-sm"
                                            value="{{ ['d'=>'Draft','r'=>'Telaah','a'=>'Siap Dikirim','s'=>'Terkirim','e'=>'Diarsipkan'][$suratKeluar->status] ?? $suratKeluar->status }}" disabled>
                                        <input type="hidden" name="status" value="{{ $suratKeluar->status }}">
                                    </div>
                                </div>
                            </div>

                            {{-- PENERIMA / DISTRIBUSI --}}
                            <div class="border rounded p-3 mb-3">
                                <h6 class="text-uppercase text-muted small mb-3 fw-bold">
                                    <i class="fi fi-rr-users me-1"></i> Penerima / Distribusi
                                </h6>

                                <label for="tujuan" class="form-label small fw-semibold">Ditujukan Kepada <span class="text-danger">*</span></label>
                                <input name="tujuan" id="tujuan" type="text"
                                    class="form-control form-control-sm @error('tujuan') is-invalid @enderror"
                                    value="{{ is_array(old('tujuan')) ? json_encode(old('tujuan')) : old('tujuan') }}"
                                    placeholder="Ketik nama atau email penerima..."
                                    {{ $isLocked ? 'disabled' : '' }}>
                                <small class="text-muted">Ketik untuk mencari. Anda dapat memilih lebih dari satu penerima.</small>
                                @error('tujuan')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- DOKUMEN / LAMPIRAN --}}
                            <div class="border rounded p-3 mb-3">
                                <h6 class="text-uppercase text-muted small mb-3 fw-bold">
                                    <i class="fi fi-rr-document me-1"></i> Dokumen &amp; Lampiran
                                </h6>

                            @php $metode = $suratKeluar->google_doc_id ? 'gdocs' : 'upload'; @endphp

                            @if ($metode === 'gdocs')
                                <div class="d-flex">
                                    @if (!in_array($suratKeluar->status, ['a', 's', 'e']))
                                        <a href="{{ route('surat-keluar.edit', $suratKeluar) }}" class="btn btn-outline-primary btn-sm">
                                            <i class="fi fi-rr-file-edit me-1"></i> Edit Google Docs
                                        </a>
                                    @endif
                                </div>
                            @else
                                <div class="row g-3">
                                    @if ($suratKeluar->file_pdf)
                                        <div class="col-md-6">
                                            <label class="form-label small fw-semibold">Dokumen Surat (PDF)</label>
                                            <div>
                                                <a href="{{ $suratKeluar->pdfUrl() }}" target="_blank"
                                                    class="btn btn-outline-success btn-sm" rel="noopener">
                                                    <i class="fi fi-rr-file-pdf me-1"></i> Lihat Surat
                                                </a>
                                            </div>
                                        </div>
                                    @endif

                                    <div class="col-md-6">
                                        <label for="lampiran" class="form-label small fw-semibold">Lampiran Pendukung</label>
                                        @if ($suratKeluar->lampiran)
                                            <div class="mb-1">
                                                <a href="{{ $suratKeluar->lampiranUrl() }}" target="_blank"
                                                    class="small text-info" rel="noopener">
                                                    <i class="fi fi-rr-paperclip me-1"></i> Lihat Lampiran Saat Ini
                                                </a>
                                            </div>
                                        @endif
                                        <input type="file" class="form-control form-control-sm @error('lampiran') is-invalid @enderror"
                                            id="lampiran" name="lampiran" accept="application/pdf">
                                        <small class="text-muted">Format PDF, maks. 10 MB</small>
                                        @error('lampiran')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            @endif
                            </div>

                            <div class="d-flex gap-2 pt-2">
                                @if (!$isLocked)
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i class="fi fi-rr-check me-1"></i> Simpan Perubahan
                                    </button>
                                @endif
                                <a href="{{ route('surat-keluar.show', $suratKeluar) }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="fi fi-rr-arrow-left me-1"></i> Batal
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('css')
    <link rel="stylesheet" href="{{ asset('') }}assets/libs/tagify/tagify.css">
@endpush
@push('js')
    <script src="{{ asset('') }}assets/libs/tagify/tagify.js"></script>
    <script>
        // Inisialisasi Tagify untuk penerima surat
        function escapeHTML(s) {
            return typeof s === 'string' ? s
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/`|'/g, "&#039;") :
            s;
    }

    @if (!$isLocked)
        var inputTujuan = document.querySelector('input[name="tujuan"]');
        var selectedTujuan = @json(
            $suratKeluar->penerima->map(function ($p) {
                return ['value' => (string) $p->id, 'name' => $p->name, 'email' => $p->email ?? ''];
            }));

        var tagify = new Tagify(inputTujuan, {
            enforceWhitelist: true,
            whitelist: @json($users),
            tagTextProp: 'name',
            maxTags: {{ ($suratKeluar->jenis_surat ?? '') === 'broadcast' ? 'Infinity' : '1' }},
            dropdown: {
                closeOnSelect: false,
                enabled: 0,
                classname: 'users-list',
                searchKeys: ['name', 'email']
            },
            templates: {
                dropdownItem: function(tagData) {
                    return `
                    <div ${this.getAttributes(tagData)}
                        class='tagify__dropdown__item ${tagData.class || ""}'
                        tabindex="0"
                        role="option">
                        ${tagData.avatar ? `
                            <div class='tagify__dropdown__item__avatar-wrap'>
                                <img onerror="this.style.visibility='hidden'" src="${tagData.avatar}">
                            </div>` : ''
                            }
                        <strong>${escapeHTML(tagData.name)}</strong>
                        <span>${escapeHTML(tagData.email)}</span>
                    </div>`;
                    }
                }
            });

            if (selectedTujuan.length > 0) {
                tagify.addTags(selectedTujuan);
            }

            // Setup klasifikasi watcher (pakai Select2 event)
            var $jenisSurat = $('#jenis_surat');

            function updateTujuanState(val) {
                if (!val) {
                    tagify.removeAllTags();
                    tagify.setDisabled(true);
                    inputTujuan.placeholder = 'Pilih Klasifikasi terlebih dahulu...';
                    return;
                }
                tagify.setDisabled(false);
                var isBroadcast = val === 'broadcast';
                tagify.settings.maxTags = isBroadcast ? Infinity : 1;
                inputTujuan.placeholder = isBroadcast ? 'Ketik nama atau email penerima...' : 'Ketik nama atau email penerima';
                if (!isBroadcast && tagify.value.length > 1) {
                    tagify.removeTags(tagify.value.slice(1).map(function(t) { return t.value; }));
                }
            }

            $jenisSurat.on('select2:select select2:clear change', function() {
                updateTujuanState($jenisSurat.val());
            });

            // Initial state
            updateTujuanState($jenisSurat.val());
        @endif
    </script>
@endpush
