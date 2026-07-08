@extends('layouts.app')
@section('title', 'Detail Surat Masuk - Surat Keluar')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center border-0">
                        <h6 class="card-title mb-0">
                            <i class="fi fi-rr-arrow-right me-1"></i> Detail Surat Masuk
                        </h6>
                        <div class="d-flex gap-2">
                            @if (!$dibaca)
                                <form action="{{ route('surat-masuk.mark-as-read', $suratKeluar) }}" method="POST"
                                    class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-white btn-sm btn-shadow waves-effect"
                                        title="Tandai sudah dibaca">
                                        <i class="fi fi-rr-envelope me-1"></i> Tandai Dibaca
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('surat-masuk.mark-as-unread', $suratKeluar) }}" method="POST"
                                    class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-white btn-sm btn-shadow waves-effect"
                                        title="Tandai belum dibaca">
                                        <i class="fi fi-rr-envelope-open me-1"></i> Tandai Belum Dibaca
                                    </button>
                                </form>
                            @endif
                            <a href="{{ route('surat-masuk.index') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="fi fi-rr-arrow-left me-1"></i> Kembali
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-striped mb-0">
                            <tr>
                                <th style="width: 180px;">Nomor Surat</th>
                                <td class="fw-bold">{{ $suratKeluar->nomor_surat ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Perihal</th>
                                <td class="fw-bold">{{ $suratKeluar->perihal }}</td>
                            </tr>
                            <tr>
                                <th>Pengirim</th>
                                <td>
                                    <span class="badge badge-lg bg-secondary bg-opacity-10 text-dark">
                                        <i class="fi fi-tr-member-list me-1"></i> {{ $suratKeluar->user->name }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Tanggal Surat</th>
                                <td>{{ $suratKeluar->tanggal_surat ? $suratKeluar->tanggal_surat->translatedFormat('d F Y') : '-' }}
                                </td>
                            </tr>
                            <tr>
                                <th>Jenis Surat</th>
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
                                    <span
                                        class="badge badge-sm {{ $jenisBadge[$suratKeluar->jenis_surat] ?? 'bg-secondary' }}">
                                        {{ $jenisLabels[$suratKeluar->jenis_surat] ?? $suratKeluar->jenis_surat }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Metode Surat</th>
                                <td>
                                    @if ($suratKeluar->metode_surat === 'gdocs')
                                        <span class="badge badge-sm bg-info"><i class="fi fi-rr-file-edit me-1"></i> Google
                                            Docs</span>
                                    @else
                                        <span class="badge badge-sm bg-secondary"><i class="fi fi-rr-upload me-1"></i>
                                            Upload PDF</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Tanggal Dikirim</th>
                                <td>{{ $suratKeluar->sent_at ? $suratKeluar->sent_at->translatedFormat('d F Y H:i:s') : '-' }}</td>
                            </tr>
                            <tr>
                                <th>File Surat</th>
                                <td>
                                    @if ($suratKeluar->file_pdf)
                                        <a href="{{ $suratKeluar->pdfUrl() }}" target="_blank"
                                            rel="noopener">
                                            <i class="fi fi-rr-file-pdf me-1"></i> Lihat Surat
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Lampiran Surat</th>
                                <td>
                                    @if ($suratKeluar->lampiran)
                                        <a href="{{ $suratKeluar->lampiranUrl() }}" target="_blank"
                                            rel="noopener">
                                            <i class="fi fi-rr-file-pdf me-1"></i> Lihat Lampiran
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Status Baca Saya</th>
                                <td>
                                    @if ($dibaca)
                                        <span class="badge badge-sm bg-success">Sudah Dibaca</span>
                                    @else
                                        <span class="badge badge-sm bg-warning text-dark">Belum Dibaca</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="card-title mb-0">
                            <i class="fi fi-rr-share me-1"></i> Riwayat Disposisi
                        </h6>
                        @php
                            $aksiLocked = in_array($pivotStatus, ['diterima', 'ditolak']);
                        @endphp
                        @if (!$aksiLocked)
                            <button type="button" class="btn btn-white btn-sm btn-shadow waves-effect"
                                data-bs-toggle="collapse" data-bs-target="#formDisposisi">
                                <i class="fi fi-rr-add"></i> Aksi
                            </button>
                        @else
                            <span
                                class="badge badge-sm bg-{{ $pivotStatus === 'diterima' || $pivotStatus === 'disposisi' ? 'success' : ($pivotStatus === 'ditolak' ? 'danger' : 'info') }} bg-opacity-10 text-dark">
                                {{ ucfirst($pivotStatus) }}
                            </span>
                        @endif
                    </div>

                    <div class="collapse" id="formDisposisi">
                        <div class="card-body border-bottom bg-light">
                            @if (!$aksiLocked)
                                <form action="{{ route('surat-masuk.update-status', $suratKeluar) }}" method="POST"
                                    id="formAksiSurat">
                                    @csrf
                                    @method('PATCH')
                                    <div class="mb-2">
                                        <label class="form-label small">Aksi</label>
                                        <select name="aksi" class="form-select form-select-sm select2" id="pilihAksi" required>
                                            <option value="">-- Pilih Aksi --</option>
                                            <option value="diteruskan">↻ Diteruskan</option>
                                            <option value="disposisi">📋 Disposisi</option>
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
                                    <span class="badge badge-sm bg-{{ in_array($pivotStatus, ['diterima', 'disposisi']) ? 'success' : ($pivotStatus === 'ditolak' ? 'danger' : 'info') }} fs-6">
                                        @if ($pivotStatus === 'diterima')
                                            ✓ Surat Diterima
                                        @elseif ($pivotStatus === 'disposisi')
                                            ✓ Surat Didiposisikan
                                        @elseif ($pivotStatus === 'ditolak')
                                            ✗ Surat Ditolak
                                        @else
                                            ↻ Surat Sudah Diteruskan
                                        @endif
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="card-body">
                        @php
                            $disposisis = $suratKeluar->disposisis()->with('pengguna', 'pengirim')->oldest()->get();
                        @endphp
                        @if ($disposisis->count() > 0)
@foreach ($disposisis as $disposisi)
@php $isInvolved = auth()->user()->hasRole('super-admin') || $disposisi->pengirim_id === auth()->id() || $disposisi->pengguna_id === auth()->id(); @endphp
<div class="d-flex align-items-start mb-3 pb-2 border-bottom">
                                    <div class="shrink-0">
                                        <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center"
                                             style="width:36px; height:36px;">
                                            <i class="fi fi-rr-user text-primary"></i>
                                        </div>
                                    </div>
                                    <div class="ms-2 flex-grow-1">
                                        <div class="small text-muted mb-1">
                                            <i class="fi fi-rr-arrow-right me-1"></i>
                                            <strong>Dari:</strong> {{ $disposisi->pengirim_id === auth()->id() ? 'Anda' : $disposisi->pengirim->name ?? 'System' }}
                                            <span class="mx-1">|</span>
                                            <strong>Kepada:</strong> {{ $disposisi->pengguna_id === auth()->id() ? 'Anda' : $disposisi->pengguna->name ?? 'User #' . $disposisi->pengguna_id }}
                                        </div>
                                        <div class="small text-muted mb-1">
                                            <i class="fi fi-rr-calendar me-1"></i> {{ $disposisi->created_at->translatedFormat('d F Y H:i') }}
                                        </div>

                                        <span class="badge badge-sm bg-{{ $disposisi->status === 'disposisi' || $disposisi->status === 'diterima' ? 'success' : ($disposisi->status === 'ditolak' ? 'danger' : 'info') }} mb-1">
                                            {{ ucfirst($disposisi->status) }}
                                        </span>
                                        @if ($isInvolved && $disposisi->keterangan)
<p class="mb-0 small text-muted">{{ $disposisi->keterangan }}</p>
@endif
                                        @if ($isInvolved && $disposisi->alasan)
<p class="mb-0 small fst-italic text-muted">
                                                <i class="fi fi-rr-quote-right me-1"></i>{{ $disposisi->alasan }}
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
                if (this.value === 'diteruskan' || this.value === 'disposisi') {
                    if (userWrapper) userWrapper.style.display = 'block';
                    inputPenerima.required = true;
                    if (this.value === 'disposisi') {
                        userWrapper.querySelector('label').textContent = 'Disposisi ke';
                    } else {
                        userWrapper.querySelector('label').textContent = 'Teruskan ke';
                    }
                    setTimeout(function() { inputPenerima.focus(); }, 150);
                } else {
                    if (userWrapper) userWrapper.style.display = 'none';
                    inputPenerima.required = false;
                    if (tagify) tagify.removeAllTags();
                }
            });

            document.getElementById('formAksiSurat')?.addEventListener('submit', function(e) {
                const aksi = document.getElementById('pilihAksi')?.value;
                if ((aksi === 'diteruskan' || aksi === 'disposisi') && tagify) {
                    const tags = JSON.parse(inputPenerima.value || '[]');
                    if (tags.length === 0) {
                        e.preventDefault();
                        showToast('warning', 'Pilih minimal satu penerima');
                    }
                }
            });
        });
    </script>
@endpush
