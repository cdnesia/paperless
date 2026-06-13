@extends('layouts.app')
@section('title', 'Buat Surat Keluar')
@section('content')
    <div class="container-fluid">
        <div class="app-page-head d-flex mb-2 flex-wrap align-items-center justify-content-between">
            <div class="clearfix mb-2">
                <h6 class="app-page-title">
                    <i class="fi fi-rr-file me-1"></i>
                    Buat Surat Keluar
                </h6>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header border-0 py-3 d-flex justify-content-between align-items-center">
                        <h6 class="card-title mb-0">
                            <i class="fi fi-rr-document-signed me-1"></i> Form Surat Keluar
                        </h6>
                        <span
                            class="badge {{ $metode === 'gdocs' ? 'bg-info' : 'bg-secondary' }} d-inline-flex align-items-center py-2 px-3">
                            <i class="fi {{ $metode === 'gdocs' ? 'fi-rr-file-edit' : 'fi-rr-upload' }} me-1"></i>
                            {{ $metode === 'gdocs' ? 'Google Docs' : 'Upload PDF' }}
                        </span>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('surat-keluar.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="metode_surat" value="{{ $metode }}">

                            <h6 class="text-uppercase text-muted small mb-3 fw-bold">
                                <i class="fi fi-rr-memo-circle-check me-1"></i> Informasi Surat
                            </h6>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="nomor_surat" class="form-label small fw-semibold">Nomor Surat <span
                                            class="text-danger">*</span></label>
                                    <input type="text"
                                        class="form-control form-control-sm @error('nomor_surat') is-invalid @enderror"
                                        id="nomor_surat" name="nomor_surat" value="{{ old('nomor_surat') }}"
                                        placeholder="Contoh: 001/UN-XX/SK/2026">
                                    <small class="text-muted">Kosongkan untuk nomor otomatis</small>
                                    @error('nomor_surat')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="tanggal_surat" class="form-label small fw-semibold">Tanggal Surat <span
                                            class="text-danger">*</span></label>
                                    <input type="date"
                                        class="form-control form-control-sm @error('tanggal_surat') is-invalid @enderror"
                                        id="tanggal_surat" name="tanggal_surat"
                                        value="{{ old('tanggal_surat', date('Y-m-d')) }}">
                                    @error('tanggal_surat')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label for="perihal" class="form-label small fw-semibold">Perihal / Subjek <span
                                            class="text-danger">*</span></label>
                                    <input type="text"
                                        class="form-control form-control-sm @error('perihal') is-invalid @enderror"
                                        id="perihal" name="perihal" value="{{ old('perihal') }}"
                                        placeholder="Contoh: Undangan Rapat Koordinasi Program Studi" required>
                                    @error('perihal')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="jenis_surat" class="form-label small fw-semibold">Klasifikasi Surat <span
                                            class="text-danger">*</span></label>
                                    <select
                                        class="form-select form-select-sm select2 @error('jenis_surat') is-invalid @enderror"
                                        id="jenis_surat" name="jenis_surat" required>
                                        <option value="">— Pilih Klasifikasi —</option>
                                        <option value="internal" {{ old('jenis_surat') == 'internal' ? 'selected' : '' }}>
                                            Internal</option>
                                        <option value="eksternal"
                                            {{ old('jenis_surat') == 'eksternal' ? 'selected' : '' }}>Eksternal</option>
                                        <option value="broadcast"
                                            {{ old('jenis_surat') == 'broadcast' ? 'selected' : '' }}>Broadcast</option>
                                    </select>
                                    @error('jenis_surat')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Status Awal</label>
                                    <input type="text" class="form-control form-control-sm" value="Draft" disabled>
                                    <small class="text-muted">Surat baru otomatis disimpan sebagai draft</small>
                                </div>
                            </div>

                            <hr class="my-3">

                            <h6 class="text-uppercase text-muted small mb-3 fw-bold">
                                <i class="fi fi-rr-users me-1"></i> Penerima / Distribusi
                            </h6>

                            <div>
                                <label for="tujuan" class="form-label small fw-semibold">Ditujukan Kepada <span
                                        class="text-danger">*</span></label>
                                <input name="tujuan" id="tujuan" type="text"
                                    class="form-control form-control-sm @error('tujuan') is-invalid @enderror"
                                    value="{{ is_array(old('tujuan')) ? json_encode(old('tujuan')) : old('tujuan') }}" placeholder="Pilih Penerima Surat">
                                <small class="text-muted">Ketik untuk mencari. Anda dapat memilih lebih dari satu
                                    penerima.</small>
                                @error('tujuan')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <hr class="my-3">

                            <h6 class="text-uppercase text-muted small mb-3 fw-bold">
                                <i class="fi fi-rr-document me-1"></i> Dokumen &amp; Lampiran
                            </h6>

                            @if ($metode === 'gdocs')
                                <div class="alert alert-info mb-0 py-2 small">
                                    <i class="fi fi-rr-info me-1"></i>
                                    Surat akan dibuat melalui <strong>Google Docs</strong>. Dokumen otomatis dibuat setelah
                                    disimpan.
                                </div>
                            @else
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="file_pdf" class="form-label small fw-semibold">Dokumen Surat (PDF) <span
                                                class="text-danger">*</span></label>
                                        <input type="file"
                                            class="form-control form-control-sm @error('file_pdf') is-invalid @enderror"
                                            id="file_pdf" name="file_pdf" accept="application/pdf">
                                        <small class="text-muted">Format PDF, maks. 10 MB</small>
                                        @error('file_pdf')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="lampiran" class="form-label small fw-semibold">Lampiran Pendukung
                                            <small class="text-muted fw-normal">(opsional)</small></label>
                                        <input type="file"
                                            class="form-control form-control-sm @error('lampiran') is-invalid @enderror"
                                            id="lampiran" name="lampiran" accept="application/pdf">
                                        <small class="text-muted">Format PDF, maks. 10 MB</small>
                                        @error('lampiran')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            @endif

                            <div class="d-flex gap-2 pt-4 mt-2 border-top">
                                @if ($metode === 'gdocs')
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i class="fi fi-rr-file-edit me-1"></i> Buat Surat
                                    </button>
                                @else
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i class="fi fi-rr-check me-1"></i> Simpan Surat
                                    </button>
                                @endif
                                <a href="{{ route('surat-keluar.index') }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="fi fi-rr-arrow-left me-1"></i> Batal
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- PANEL PETUNJUK --}}
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm sticky-top" style="top: 80px;">
                    <div class="card-header border-0 bg-transparent pb-0">
                        <h6 class="card-title mb-0 small fw-bold text-uppercase text-muted">
                            <i class="fi fi-rr-info me-1"></i> Petunjuk Pengisian
                        </h6>
                    </div>
                    <div class="card-body pt-2">
                        <div class="d-flex mb-3">
                            <div class="text-muted me-2" style="font-size: 11px; width: 20px; flex-shrink: 0;">1.</div>
                            <div>
                                <strong class="small">No. Surat</strong>
                                <p class="text-muted small mb-0">Isi dengan format penomoran resmi institusi, misal:
                                    <code>001/UN-XX/SK/2026</code>. Kosongkan untuk nomor otomatis.</p>
                            </div>
                        </div>
                        <div class="d-flex mb-3">
                            <div class="text-muted me-2" style="font-size: 11px; width: 20px; flex-shrink: 0;">2.</div>
                            <div>
                                <strong class="small">Tanggal Surat</strong>
                                <p class="text-muted small mb-0">Tanggal resmi surat diterbitkan. Default diisi tanggal
                                    hari ini.</p>
                            </div>
                        </div>
                        <div class="d-flex mb-3">
                            <div class="text-muted me-2" style="font-size: 11px; width: 20px; flex-shrink: 0;">3.</div>
                            <div>
                                <strong class="small">Perihal / Subjek</strong>
                                <p class="text-muted small mb-0">Judul atau pokok bahasan surat. Gunakan kalimat singkat
                                    dan jelas, hindari singkatan yang tidak baku.</p>
                            </div>
                        </div>
                        <div class="d-flex mb-3">
                            <div class="text-muted me-2" style="font-size: 11px; width: 20px; flex-shrink: 0;">4.</div>
                            <div>
                                <strong class="small">Klasifikasi Surat</strong>
                                <p class="text-muted small mb-0">
                                    <strong>Internal</strong> — lingkup universitas sendiri.<br>
                                    <strong>Eksternal</strong> — pihak luar kampus.<br>
                                    <strong>Broadcast</strong> — pengumuman semua unit.
                                </p>
                            </div>
                        </div>
                        <div class="d-flex mb-3">
                            <div class="text-muted me-2" style="font-size: 11px; width: 20px; flex-shrink: 0;">5.</div>
                            <div>
                                <strong class="small">Ditujukan Kepada</strong>
                                <p class="text-muted small mb-0">Ketik nama atau email penerima, lalu pilih dari daftar.
                                    Bisa lebih dari satu. Penerima akan otomatis menerima notifikasi.</p>
                            </div>
                        </div>
                        <div class="d-flex mb-0">
                            <div class="text-muted me-2" style="font-size: 11px; width: 20px; flex-shrink: 0;">6.</div>
                            <div>
                                <strong class="small">Dokumen &amp; Lampiran</strong>
                                <p class="text-muted small mb-0">Upload dokumen surat utama (PDF) dan lampiran pendukung
                                    bila ada. Format PDF, maks. 10 MB per file.</p>
                            </div>
                        </div>
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
        function escapeHTML(s) {
            return typeof s === 'string' ? s
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/`|'/g, "&#039;") :
            s;
    }
    var inputTujuan = document.querySelector('input[name="tujuan"]');

    var tagify = new Tagify(inputTujuan, {
        enforceWhitelist: true,

        whitelist: @json($users),

        tagTextProp: 'name',
        maxTags: 1,

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
        			</div>
        		`;
                }
            }
        });

        // Disable Tagify input sampai klasifikasi dipilih
        tagify.setDisabled(true);

        // Setup klasifikasi watcher (pakai Select2 event karena select di-wrap oleh Select2)
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
                tagify.removeTags(tagify.value.slice(1).map(function(t) {
                    return t.value;
                }));
            }
        }

        $jenisSurat.on('select2:select select2:clear change', function() {
            updateTujuanState($jenisSurat.val());
        });
    </script>
@endpush
