@extends('layouts.app')
@section('title', 'Edit Google Docs - ' . $suratKeluar->perihal)
@section('content')
    @php
        $isLocked = in_array($suratKeluar->status, ['s', 'e']);
        $isApproved = $suratKeluar->status === 'a';
        $gdocId = $suratKeluar->google_doc_id;
        $editUrl = "https://docs.google.com/document/d/{$gdocId}/edit";
        $viewUrl = "https://docs.google.com/document/d/{$gdocId}/view";
        $previewUrl = "https://docs.google.com/document/d/{$gdocId}/preview";
    @endphp
    <div class="container-fluid">
        <div class="app-page-head d-flex mb-2 flex-wrap align-items-center justify-content-between">
            <div class="clearfix mb-2">
                <h6 class="app-page-title">
                    <i class="fi fi-rr-file-edit me-1"></i>
                    Edit Surat Keluar
                </h6>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ $viewUrl }}" target="_blank" class="btn btn-outline-secondary btn-sm" rel="noopener">
                    <i class="fi fi-rr-eye me-1"></i> Lihat
                </a>
                <a href="{{ $editUrl }}" target="_blank" class="btn btn-primary btn-sm" rel="noopener">
                    <i class="fi fi-rr-edit me-1"></i> Edit
                </a>
                <button type="button" class="btn btn-outline-info btn-sm" onclick="copyGdocsLink()">
                    <i class="fi fi-rr-copy me-1"></i> Salin Link
                </button>
                <a href="{{ route('surat-keluar.show', $suratKeluar) }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fi fi-rr-arrow-left me-1"></i> Kembali
                </a>
            </div>
        </div>

        @if ($isLocked)
            <div class="alert alert-warning py-2 mb-3">
                <i class="fi fi-rr-lock me-1"></i>
                Surat sudah <strong>{{ $suratKeluar->status }}</strong>. Data surat tidak bisa diedit.
                Hanya lampiran yang masih bisa ditambahkan.
            </div>
        @elseif ($isApproved)
            <div class="alert alert-info py-2 mb-3">
                <i class="fi fi-rr-info me-1"></i>
                Surat sudah <strong>siap dikirim</strong>. Anda masih bisa mengedit informasi surat,
                tetapi tidak bisa mengganti file PDF (Simpan Final tidak tersedia).
            </div>
        @endif

        <div class="row">
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header border-0 pb-2 d-flex justify-content-between align-items-center">
                        <h6 class="card-title mb-0">
                            <i class=\"fi fi-rr-document-signed me-1\"></i>Data Surat
                        </h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('surat-keluar.update', $suratKeluar) }}" method="POST" id="formGdocs"
                            enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="metode_surat" value="gdocs">
                            <input type="hidden" name="google_doc_id" value="{{ $suratKeluar->google_doc_id }}">

                            {{-- HEADER SURAT --}}
                            <div class="border rounded p-3 mb-3 bg-light">
                                <h6 class="text-uppercase text-muted small mb-3 fw-bold">
                                    <i class=\"fi fi-rr-memo-circle-check me-1\"></i> Informasi Surat
                                </h6>

                                <div class="row g-2">
                                    <div class="col-6">
                                        <label for="nomor_surat" class="form-label small fw-semibold">No. Surat</label>
                                        <input type="text"
                                            class="form-control form-control-sm @error('nomor_surat') is-invalid @enderror"
                                            id="nomor_surat" name="nomor_surat"
                                            value="{{ old('nomor_surat', $suratKeluar->nomor_surat) }}"
                                            placeholder="Contoh: 001/UN-XX/SK/2026"
                                            {{ $isLocked ? 'readonly' : '' }}>
                                        @error('nomor_surat')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-6">
                                        <label for="tanggal_surat" class="form-label small fw-semibold">Tanggal Surat <span class="text-danger">*</span></label>
                                        <input type="date"
                                            class="form-control form-control-sm @error('tanggal_surat') is-invalid @enderror"
                                            id="tanggal_surat" name="tanggal_surat"
                                            value="{{ old('tanggal_surat', $suratKeluar->tanggal_surat ? $suratKeluar->tanggal_surat->format('Y-m-d') : '') }}"
                                            {{ $isLocked ? 'readonly' : '' }}>
                                        @error('tanggal_surat')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-12">
                                        <label for="perihal" class="form-label small fw-semibold">Perihal / Subjek <span class="text-danger">*</span></label>
                                        <input type="text"
                                            class="form-control form-control-sm @error('perihal') is-invalid @enderror"
                                            id="perihal" name="perihal" value="{{ old('perihal', $suratKeluar->perihal) }}"
                                            placeholder="Contoh: Undangan Rapat Koordinasi Program Studi"
                                            {{ $isLocked ? 'readonly' : '' }}>
                                        @error('perihal')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-6">
                                        <label for="jenis_surat" class="form-label small fw-semibold">Klasifikasi Surat <span class="text-danger">*</span></label>
                                        <select class="form-select form-select-sm select2 @error('jenis_surat') is-invalid @enderror"
                                            id="jenis_surat" name="jenis_surat" {{ $isLocked ? 'disabled' : '' }}>
                                            <option value="">— Pilih —</option>
                                            <option value="internal"
                                                {{ old('jenis_surat', $suratKeluar->jenis_surat) == 'internal' ? 'selected' : '' }}>📂 Internal</option>
                                            <option value="eksternal"
                                                {{ old('jenis_surat', $suratKeluar->jenis_surat) == 'eksternal' ? 'selected' : '' }}>📤 Eksternal</option>
                                            <option value="broadcast"
                                                {{ old('jenis_surat', $suratKeluar->jenis_surat) == 'broadcast' ? 'selected' : '' }}>📢 Broadcast</option>
                                        </select>
                                        @error('jenis_surat')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-6">
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
                                    <i class=\"fi fi-rr-users me-1\"></i> Penerima / Distribusi
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

                            {{-- LAMPIRAN --}}
                            <div class="border rounded p-3 mb-3">
                                <h6 class="text-uppercase text-muted small mb-3 fw-bold">
                                    <i class=\"fi fi-rr-document me-1\"></i> Lampiran
                                </h6>
                                <label for="lampiran" class="form-label small fw-semibold">Lampiran Pendukung</label>
                                @if ($suratKeluar->lampiran)
                                    <div class="mb-1">
                                        <a href="{{ $suratKeluar->lampiranUrl() }}" target="_blank"
                                            class="small text-info" rel="noopener">
                                            <i class="fi fi-rr-paperclip me-1"></i> Lihat Lampiran Saat Ini
                                        </a>
                                    </div>
                                @endif
                                <input type="file"
                                    class="form-control form-control-sm @error('lampiran') is-invalid @enderror"
                                    id="lampiran" name="lampiran" accept="application/pdf">
                                <small class="text-muted">Format PDF, maks. 10 MB</small>
                                @error('lampiran')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <hr>

                            @if ($isLocked)
                                {{-- Mode locked (sent/archived): hanya simpan lampiran --}}
                                <div class="d-flex gap-2 pt-2">
                                    <a href="https://docs.google.com/document/d/{{ $suratKeluar->google_doc_id }}/edit"
                                        target="_blank" class="btn btn-outline-primary btn-sm" rel="noopener">
                                        <i class="fi fi-rr-external-link me-1"></i> Buka di Google Docs
                                    </a>
                                    <button type="submit" name="action" value="draft" class="btn btn-primary btn-sm">
                                        <i class="fi fi-rr-save me-1"></i> Simpan Lampiran
                                    </button>
                                </div>
                            @elseif ($isApproved)
                                {{-- Mode approved: bisa edit info surat, tapi tidak bisa Simpan Final --}}
                                <div class="d-flex gap-2 pt-2">
                                    <button type="submit" name="action" value="draft" class="btn btn-primary btn-sm">
                                        <i class="fi fi-rr-save me-1"></i> Simpan Draft
                                    </button>
                                    <a href="https://docs.google.com/document/d/{{ $suratKeluar->google_doc_id }}/edit"
                                        target="_blank" class="btn btn-outline-primary btn-sm" rel="noopener">
                                        <i class="fi fi-rr-external-link me-1"></i> Buka di Google Docs
                                    </a>
                                </div>
                            @else
                                {{-- Mode normal: bisa edit info surat + Simpan Final --}}
                                <div class="alert alert-warning py-2 small mb-3">
                                    <i class="fi fi-rr-info me-1"></i>
                                    <strong>Simpan Final</strong> akan meng-export Google Docs ke PDF dan tidak dapat diubah lagi.
                                </div>
                                <div class="d-flex gap-2 pt-2">
                                    <button type="submit" name="action" value="draft" class="btn btn-primary btn-sm">
                                        <i class="fi fi-rr-disk me-1"></i> Simpan Draft
                                    </button>
                                    <button type="submit" name="action" value="final" class="btn btn-success btn-sm">
                                        <i class="fi fi-rr-check me-1"></i> Simpan Final
                                    </button>
                                </div>
                            @endif
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-8 d-flex flex-column">
                <div class="flex-grow-1 bg-white rounded shadow-sm" style="min-height: 75vh;">
                    <iframe src="{{ $previewUrl }}" width="100%" height="100%" style="border:none;" id="gdocsIframe"
                        allowfullscreen>
                    </iframe>
                </div>
            </div>
        </div>
    </div>
@endsection

{{-- Modal Konfirmasi Simpan Final --}}
<div class="modal fade" id="modalKonfirmasiFinal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h6 class="modal-title">
                    <i class="fi fi-rr-warning text-warning me-1"></i>
                    Konfirmasi Simpan Final
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2">Apakah Anda yakin ingin menyimpan sebagai <strong>Final</strong>?</p>
                <ul class="list-unstyled mb-0 small">
                    <li class="mb-1">• Status akan berubah menjadi <strong>Approved</strong></li>
                    <li class="mb-1">• Google Docs akan di-export ke PDF</li>
                    <li class="mb-1">• Dokumen Google Docs akan dihapus dari Drive</li>
                    <li class="mb-1">• Data tidak bisa diedit lebih lanjut</li>
                </ul>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                    <i class="fi fi-rr-cross me-1"></i> Batal
                </button>
                <button type="button" class="btn btn-success btn-sm" id="btnKonfirmasiFinal">
                    <i class="fi fi-rr-check me-1"></i> Ya, Simpan Final
                </button>
            </div>
        </div>
    </div>
</div>

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
                    </div>
                    `;
                    }
                }
            });

            // Set nilai awal dari penerima yang sudah dipilih
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

        $(document).ready(function() {
            // Konfirmasi sebelum simpan final — tampilkan modal
            var $btnFinal = null;

            $('button[value="final"]').on('click', function(e) {
                e.preventDefault();
                $btnFinal = $(this);
                $('#modalKonfirmasiFinal').modal('show');
            });

            $('#btnKonfirmasiFinal').on('click', function() {
                $('#modalKonfirmasiFinal').modal('hide');
                if ($btnFinal) {
                    // Tambah hidden input action karena submit() tidak kirim nilai button
                    var form = $btnFinal.closest('form');
                    $('<input>').attr({type: 'hidden', name: 'action', value: 'final'}).appendTo(form);
                    form.submit();
                }
            });
        });

        // Auto-reload preview saat user kembali dari tab lain
        var iframe = document.getElementById('gdocsIframe');
        var previewUrl = '{{ $previewUrl }}';

        // Reload ketika halaman menjadi visible lagi (user balik dari tab sebelah)
        document.addEventListener('visibilitychange', function() {
            if (!document.hidden && iframe) {
                iframe.src = previewUrl + '?reload=' + Date.now();
            }
        });

        // Copy Google Docs link ke clipboard
        function copyGdocsLink() {
            var url = '{{ $editUrl }}';
            navigator.clipboard.writeText(url).then(function() {
                var btn = event.target.closest('button');
                var originalHtml = btn.innerHTML;
                btn.innerHTML = '<i class="fi fi-rr-check me-1"></i> Tersalin!';
                btn.classList.remove('btn-outline-info');
                btn.classList.add('btn-success');
                setTimeout(function() {
                    btn.innerHTML = originalHtml;
                    btn.classList.remove('btn-success');
                    btn.classList.add('btn-outline-info');
                }, 2000);
            }).catch(function() {
                alert('Gagal menyalin link. Silakan copy manual: ' + url);
            });
        }
    </script>
@endpush
