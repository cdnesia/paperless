@extends('layouts.app')
@section('title', 'Disposisi Masuk - Detail')
@section('content')
    <div class="container-fluid">
        <div class="row">
            {{-- KIRI: Informasi Surat Ringkas --}}
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center border-0 pb-2">
                        <h6 class="card-title mb-0">
                            <i class="fi fi-rr-document me-1"></i> Informasi Surat
                        </h6>
                        <div class="d-flex gap-2">
                            @if (!$disposisi->dibaca)
                                <form action="{{ route('disposisi-masuk.mark-as-read', $disposisi) }}" method="POST"
                                    class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-white btn-sm btn-shadow waves-effect">
                                        <i class="fi fi-rr-envelope me-1"></i> Tandai Dibaca
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('disposisi-masuk.mark-as-unread', $disposisi) }}" method="POST"
                                    class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-white btn-sm btn-shadow waves-effect">
                                        <i class="fi fi-rr-envelope-open me-1"></i> Tandai Belum Dibaca
                                    </button>
                                </form>
                            @endif
                            <a href="{{ route('disposisi-masuk.index') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="fi fi-rr-arrow-left me-1"></i> Kembali
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        @php $surat = $disposisi->suratKeluar; @endphp
                        <table class="table table-bordered table-striped">
                            <tr>
                                <th style="width: 160px;">Nomor Surat</th>
                                <td class="fw-bold">{{ $surat->nomor_surat ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Perihal</th>
                                <td class="fw-bold">{{ $surat->perihal }}</td>
                            </tr>
                            <tr>
                                <th>Pengirim</th>
                                <td>
                                    <span class="badge badge-sm bg-secondary bg-opacity-10 text-dark">
                                        <i class="fi fi-tr-member-list me-1"></i> {{ $surat->user->name ?? '-' }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Tanggal Surat</th>
                                <td>{{ $surat->tanggal_surat ? $surat->tanggal_surat->translatedFormat('d F Y') : '-' }}</td>
                            </tr>
                            <tr>
                                <th>Jenis / Metode</th>
                                <td>
                                    @php
                                        $jenisLabels = [
                                            'internal' => 'Internal',
                                            'eksternal' => 'Eksternal',
                                            'broadcast' => 'Broadcast',
                                        ];
                                        $jenisBadge = [
                                            'internal' => 'bg-info',
                                            'eksternal' => 'bg-warning text-dark',
                                            'broadcast' => 'bg-success',
                                        ];
                                    @endphp
                                    <span class="badge badge-sm {{ $jenisBadge[$surat->jenis_surat] ?? 'bg-secondary' }}">
                                        {{ $jenisLabels[$surat->jenis_surat] ?? $surat->jenis_surat }}
                                    </span>
                                    @if ($surat->metode_surat === 'gdocs')
                                        <span class="badge badge-sm bg-info ms-1"><i class="fi fi-rr-file-edit me-1"></i>
                                            Google Docs</span>
                                    @else
                                        <span class="badge badge-sm bg-secondary ms-1"><i class="fi fi-rr-upload me-1"></i>
                                            Upload PDF</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>File Surat</th>
                                <td>
                                    @if ($surat->file_pdf)
                                        <a href="{{ $surat->pdfUrl() }}" target="_blank" rel="noopener">
                                            <i class="fi fi-rr-file-pdf me-1"></i> Lihat PDF
                                        </a>
                                    @elseif ($surat->google_doc_id)
                                        <a href="https://docs.google.com/document/d/{{ $surat->google_doc_id }}/edit"
                                            target="_blank" rel="noopener">
                                            <i class="fi fi-rr-file-edit me-1"></i> Buka Google Docs
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Lampiran Surat</th>
                                <td>
                                    @if ($surat->lampiran)
                                        <span class="mx-1">|</span>
                                        <a href="{{ $surat->lampiranUrl() }}" target="_blank" rel="noopener">
                                            <i class="fi fi-rr-file-pdf me-1"></i> Lampiran PDF
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            {{-- KANAN: Aksi Disposisi --}}
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center border-0 pb-2">
                        <h6 class="card-title mb-0">
                            <i class="fi fi-rr-share me-1"></i> Riwayat Disposisi
                        </h6>
                        @if (!in_array($disposisi->status, ['diterima', 'ditolak', 'selesai']))
                            <button type="button" class="btn btn-white btn-sm btn-shadow waves-effect"
                                data-bs-toggle="collapse" data-bs-target="#formDisposisi">
                                <i class="fi fi-rr-add"></i> Aksi
                            </button>
                        @else
                            <span
                                class="badge badge-sm bg-{{ $disposisi->status === 'diterima' || $disposisi->status === 'selesai' ? 'success' : ($disposisi->status === 'ditolak' ? 'danger' : 'info') }} bg-opacity-10 text-dark">
                                {{ ucfirst($disposisi->status) }}
                            </span>
                        @endif
                    </div>

                    <div class="collapse" id="formDisposisi">
                        <div class="card-body border-bottom bg-light">
                            @if (!in_array($disposisi->status, ['diterima', 'ditolak', 'selesai']))
                                <form action="{{ route('disposisi-masuk.update-status', $disposisi) }}" method="POST" id="formAksiSurat">
                                    @csrf
                                    @method('PATCH')
                                    <div class="mb-2">
                                        <label class="form-label small">Aksi</label>
                                        <select name="aksi" class="form-select form-select-sm select2" id="pilihAksi" required>
                                            <option value="">-- Pilih Aksi --</option>
                                            <option value="diteruskan">↻ Disposisikan / Teruskan</option>
                                            <option value="diterima">✓ Terima</option>
                                            <option value="ditolak">✗ Tolak</option>
                                        </select>
                                    </div>
                                    <div class="mb-2" id="pilihUserWrapper" style="display:none;">
                                        <label class="form-label small">Teruskan ke</label>
                                        <input name="pengguna_id" id="pengguna_id" type="text"
                                            class="form-control form-control-sm"
                                            placeholder="Pilih penerima disposisi">
                                        <small class="text-muted">Gunakan Ctrl/Cmd+klik untuk pilih banyak</small>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label small">Alasan / Keterangan</label>
                                        <textarea name="alasan" class="form-control form-control-sm" rows="2"
                                                  placeholder="Alasan atau catatan (opsional)"></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-sm w-100" id="btnSubmitAksi">
                                        <i class="fi fi-rr-share me-1"></i> Proses
                                    </button>
                                </form>
                            @else
                                <div class="text-center py-2">
                                    <span class="badge badge-sm bg-{{ $disposisi->status === 'diterima' || $disposisi->status === 'selesai' ? 'success' : ($disposisi->status === 'ditolak' ? 'danger' : 'info') }} fs-6">
                                        @if ($disposisi->status === 'diterima' || $disposisi->status === 'selesai')
                                            ✓ Surat Diterima
                                        @elseif ($disposisi->status === 'ditolak')
                                            ✗ Surat Ditolak
                                        @else
                                            ↻ Surat Sudah Diteruskan
                                        @endif
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                        @php
                            $surat = $disposisi->suratKeluar;
                            $allDisposisions = $surat->disposisis()->with('pengguna', 'pengirim')->oldest()->get();
                        @endphp
                        @if ($allDisposisions->count() > 0)
                            @foreach ($allDisposisions as $item)
                                <div class="d-flex align-items-start mb-3 pb-2 border-bottom">
                                    <div class="shrink-0">
                                        <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center"
                                            style="width:36px; height:36px;">
                                            <i class="fi fi-rr-user text-primary"></i>
                                        </div>
                                    </div>
                                    <div class="ms-2 grow">
                                        <div class="small text-muted mb-1">
                                            <i class="fi fi-rr-arrow-right me-1"></i>
                                            <strong>Dari:</strong>
                                            {{ $item->pengirim_id === auth()->id() ? 'Anda' : $item->pengirim->name ?? 'System' }}
                                            <span class="mx-1">|</span>
                                            <strong>Kepada:</strong>
                                            {{ $item->pengguna_id === auth()->id() ? 'Anda' : $item->pengguna->name ?? 'User #' . $item->pengguna_id }}
                                        </div>
                                        <div class="small text-muted mb-1">
                                            <i class="fi fi-rr-calendar me-1"></i>
                                            {{ $item->created_at->translatedFormat('d F Y H:i') }}
                                        </div>
                                        <span
                                            class="badge badge-sm bg-{{ $item->status === 'selesai' || $item->status === 'diterima' ? 'success' : ($item->status === 'ditolak' ? 'danger' : 'info') }} mb-1">
                                            {{ ucfirst($item->status) }}
                                        </span>
                                        @if ($item->keterangan)
                                            <p class="mb-0 small text-muted">{{ $item->keterangan }}</p>
                                        @endif
                                        @if ($item->alasan)
                                            <p class="mb-0 small fst-italic text-muted">
                                                <i class="fi fi-rr-quote-right me-1"></i>{{ $item->alasan }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="text-center text-muted py-4">
                                <i class="fi fi-rr-share" style="font-size: 2rem;"></i>
                                <p class="mt-2 mb-0">Belum ada disposisi</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if (session('success'))
        <script>
            showToast('success', '{{ session('success') }}');
        </script>
    @endif
    @if (session('error'))
        <script>
            showToast('error', '{{ session('error') }}');
        </script>
    @endif
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

        var inputPenerima = document.querySelector('input[name="pengguna_id"]');

        // Jika input tidak ada (surat sudah di-disposisi), jangan inisialisasi Tagify
        if (!inputPenerima) {
            var tagify = null;
        } else {
            var tagify = new Tagify(inputPenerima, {
            enforceWhitelist: true,

            whitelist: @json($users),

            tagTextProp: 'name',

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
        } // end if (inputPenerima)

        // Tampilkan dropdown saat input difokus (Tagify enabled:0 = auto)
        if (inputPenerima) {
            inputPenerima.addEventListener('focus', function() {
                if (tagify) tagify.dropdown.show();
            });
        }

        // Bootstrap collapse: pastikan overflow tidak memotong dropdown
        var collapseEl = document.getElementById('formDisposisi');
        if (collapseEl) {
            collapseEl.addEventListener('shown.bs.collapse', function () {
                collapseEl.style.overflow = 'visible';
                setTimeout(function() {
                    if (inputPenerima && inputPenerima.offsetParent) {
                        inputPenerima.focus();
                    }
                }, 100);
            });
            collapseEl.addEventListener('hidden.bs.collapse', function () {
                collapseEl.style.overflow = '';
            });
        }

        $(function() {
            // Gunakan jQuery untuk Select2 compatibility
            $('#pilihAksi').on('change', function() {
                const userWrapper = document.getElementById('pilihUserWrapper');
                if (this.value === 'diteruskan') {
                    if (userWrapper) userWrapper.style.display = 'block';
                    inputPenerima.required = true;
                    setTimeout(function() { inputPenerima.focus(); }, 150);
                } else {
                    if (userWrapper) userWrapper.style.display = 'none';
                    inputPenerima.required = false;
                    if (tagify) tagify.removeAllTags();
                }
            });

            // Prevent form submit if diteruskan but no tags
            document.getElementById('formAksiSurat')?.addEventListener('submit', function(e) {
                const aksi = document.getElementById('pilihAksi')?.value;
                if (aksi === 'diteruskan' && tagify) {
                    const tags = JSON.parse(inputPenerima.value || '[]');
                    if (tags.length === 0) {
                        e.preventDefault();
                        showToast('warning', 'Pilih minimal satu penerima untuk diteruskan');
                    }
                }
            });
        });
    </script>
@endpush
