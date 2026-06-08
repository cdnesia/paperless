@extends('layouts.app')
@section('title', 'Detail Surat Keluar')
@section('content')
    <div class="container-fluid">
        <div class="app-page-head d-flex mb-2 flex-wrap align-items-center justify-content-between">
            <div class="clearfix">
                <h6 class="app-page-title">
                    <i class="fi fi-rr-file me-1"></i>
                    Preview Surat Keluar
                </h6>
            </div>
            <div class="d-flex gap-2">
                @if ($suratKeluar->status === 'a')
                    <a href="{{ route('surat-keluar.edit', $suratKeluar) }}" class="btn btn-primary btn-sm">
                        <i class="fi fi-rr-pencil me-1"></i> Edit
                    </a>
                    <button type="button" class="btn btn-success btn-sm btn-shadow waves-effect btn-kirim-surat-show"
                        title="Kirim Surat" data-url="{{ route('surat-keluar.send', $suratKeluar) }}">
                        <i class="fi fi-rr-paper-plane me-1"></i> Kirim Surat
                    </button>
                    <a href="{{ route('tanda-tangan-digital.index', $suratKeluar) }}" class="btn btn-outline-info btn-sm">
                        <i class="fi fi-rr-file-pen me-1"></i> Tanda Tangan Digital
                    </a>
                @elseif (!in_array($suratKeluar->status, ['s', 'e']))
                    <a href="{{ route('surat-keluar.edit', $suratKeluar) }}" class="btn btn-primary btn-sm">
                        <i class="fi fi-rr-pencil me-1"></i> Edit
                    </a>
                @endif
                <a href="{{ route('surat-keluar.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fi fi-rr-arrow-left me-1"></i> Kembali
                </a>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header border-0 pb-2">
                        <h6 class="card-title mb-2">
                            <i class="fi fi-rr-arrow-right me-1"></i>Data Surat
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-bordered table-striped">
                            <tr>
                                <th style="width: 180px;">Nomor Surat</th>
                                <td class="fw-bold">{{ $suratKeluar->nomor_surat ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Perihal</th>
                                <td class="fw-bold">{{ $suratKeluar->perihal }}</td>
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
                                <th>Status</th>
                                <td>
                                    @php
                                        $statusLabels = [
                                            'd' => 'Draft',
                                            'r' => 'Telaah',
                                            'a' => 'Siap Dikirim',
                                            's' => 'Terkirim',
                                            'e' => 'Diarsipkan',
                                        ];
                                        $statusBadges = [
                                            'd' => 'bg-secondary',
                                            'r' => 'bg-info',
                                            'a' => 'bg-primary',
                                            's' => 'bg-success',
                                            'e' => 'bg-dark',
                                        ];
                                    @endphp
                                    <span
                                        class="badge badge-sm {{ $statusBadges[$suratKeluar->status] ?? 'bg-secondary' }}">
                                        {{ $statusLabels[$suratKeluar->status] ?? ucfirst($suratKeluar->status) }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Penerima</th>
                                <td>
                                    @if ($suratKeluar->penerima->isNotEmpty())
                                        @foreach ($suratKeluar->penerima as $penerima)
                                            <span class="badge badge-sm bg-secondary bg-opacity-10 text-dark me-1">{{ $penerima->name }}</span>
                                        @endforeach
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Tanggal Surat</th>
                                <td>{{ $suratKeluar->tanggal_surat ? $suratKeluar->tanggal_surat->translatedFormat('d F Y') : '-' }}
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
                                <th>Google Docs</th>
                                <td>
                                    @if ($suratKeluar->google_doc_id)
                                        <a href="{{ route('surat-keluar.edit', $suratKeluar) }}" rel="noopener">
                                            <i class="fi fi-rr-file-edit me-1"></i> Buka Google Docs
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>File Surat (PDF)</th>
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
                                <th>Lampiran (PDF)</th>
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
                                <th>Tanggal Dikirim</th>
                                <td>{{ $suratKeluar->sent_at ? $suratKeluar->sent_at->translatedFormat('d F Y H:i:s') : '-' }}
                                </td>
                            </tr>
                            <tr>
                                <th>Tanggal Dibuat</th>
                                <td>{{ $suratKeluar->created_at->translatedFormat('d F Y H:i:s') }}</td>
                            </tr>
                            <tr>
                                <th>Terakhir Diupdate</th>
                                <td>{{ $suratKeluar->updated_at->translatedFormat('d F Y H:i:s') }}</td>
                            </tr>
                        </table>

                        @if ($suratKeluar->penerima->isNotEmpty())
                        <h6 class="text-uppercase text-muted small fw-bold mt-4 mb-2 px-3">
                            <i class="fi fi-rr-users-alt me-1"></i> Status Penerima
                        </h6>
                        <table class="table table-sm table-bordered mb-0">
                                <thead class="table-light small text-muted">
                                    <tr>
                                        <th>Nama</th>
                                        <th class="text-center" style="width:80px">Baca</th>
                                        <th class="text-center" style="width:100px">Status</th>
                                        <th style="width:130px">Waktu</th>
                                        <th>Alasan</th>
                                    </tr>
                                </thead>
                                <tbody class="small align-middle">
                                    @foreach ($suratKeluar->penerima as $p)
                                        <tr>
                                            <td class="fw-semibold">{{ $p->name }}</td>
                                            <td class="text-center">
                                                @if ($p->pivot->dibaca)
                                                    <span class="badge bg-success">✓</span>
                                                @else
                                                    <span class="badge bg-warning text-dark">⏳</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @php $s = $p->pivot->status; @endphp
                                                @if ($s)
                                                    <span class="badge badge-sm {{ match($s) { 'diterima' => 'bg-success', 'ditolak' => 'bg-danger', 'diteruskan' => 'bg-info', default => 'bg-secondary' } }}">
                                                        {{ match($s) { 'diterima' => 'Diterima', 'ditolak' => 'Ditolak', 'diteruskan' => 'Diteruskan', default => $s } }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td class="text-muted">{{ $p->pivot->dibaca_at ? \Carbon\Carbon::parse($p->pivot->dibaca_at)->translatedFormat('d M H:i') : '-' }}</td>
                                            <td class="text-muted">{{ $p->pivot->alasan ? \Illuminate\Support\Str::limit($p->pivot->alasan, 50) : '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header border-0 pb-2">
                                <h6 class="card-title mb-0">
                                    <i class="fi fi-rr-share me-1"></i> Riwayat Disposisi
                                </h6>
                            </div>

                            <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                                @php
                                    $disposisis = $suratKeluar
                                        ->disposisis()
                                        ->with('pengguna', 'pengirim')
                                        ->oldest()
                                        ->get();
                                @endphp
                                @if ($disposisis->count() > 0)
                                    @foreach ($disposisis as $disposisi)
                                        <div class="d-flex align-items-start mb-3 pb-2 border-bottom">
                                            <div class="flex-shrink-0">
                                                <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center"
                                                    style="width:36px; height:36px;">
                                                    <i class="fi fi-rr-user text-primary"></i>
                                                </div>
                                            </div>
                                            <div class="ms-2 flex-grow-1">
                                                <div class="small text-muted mb-1">
                                                    <i class="fi fi-rr-arrow-right me-1"></i>
                                                    <strong>Dari:</strong>
                                                    {{ $disposisi->pengirim_id === auth()->id() ? 'Anda' : $disposisi->pengirim->name ?? 'System' }}
                                                    <span class="mx-1">|</span>
                                                    <strong>Kepada:</strong>
                                                    {{ $disposisi->pengguna_id === auth()->id() ? 'Anda' : $disposisi->pengguna->name ?? 'User #' . $disposisi->pengguna_id }}
                                                </div>
                                                <div class="small text-muted mb-1">
                                                    <i class="fi fi-rr-calendar me-1"></i>
                                                    {{ $disposisi->created_at->translatedFormat('d F Y H:i') }}
                                                </div>
                                                @if ($disposisi->keterangan)
                                                    <p class="mb-0 small text-muted">{{ $disposisi->keterangan }}</p>
                                                @endif
                                                @if ($disposisi->alasan)
                                                    <p class="mb-0 small fst-italic text-muted">
                                                        <i class="fi fi-rr-quote-right me-1"></i>{{ $disposisi->alasan }}
                                                    </p>
                                                @endif
                                                <div class="d-flex align-items-center gap-2 mt-1">
                                                    <span
                                                        class="badge badge-sm bg-{{ $disposisi->status === 'selesai' || $disposisi->status === 'diterima' ? 'success' : ($disposisi->status === 'ditolak' ? 'danger' : 'info') }} mb-1">
                                                        {{ ucfirst($disposisi->status) }}
                                                    </span>
                                                </div>
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
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header border-0 pb-2">
                                <h6 class="card-title mb-2">
                                    <i class="fi fi-rr-time-past"></i> Riwayat Surat
                                </h6>
                            </div>

                            <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                                @if ($suratKeluar->histories->count() > 0)
                                    <div class="timeline-wrapper position-relative ps-4">
                                        <div class="timeline-line"
                                            style="position:absolute; left:7px; top:0; bottom:0; width:2px; background:#e9ecef;">
                                        </div>

                                        @foreach ($suratKeluar->histories->sortByDesc('created_at') as $history)
                                            <div class="timeline-item mb-3 position-relative">
                                                <div class="timeline-dot position-absolute"
                                                    style="left:-16px; top:4px; width:14px; height:14px; border-radius:50%;
                                                    @php
$colors = [
                                                            'created' => 'background:#0d6efd',
                                                            'updated' => 'background:#6c757d',
                                                            'read' => 'background:#198754',
                                                            'unread' => 'background:#ffc107',
                                                            'terkirim' => 'background:#0dcaf0',
                                                            'disposisi' => 'background:#6610f2',
                                                            'approved' => 'background:#198754',
                                                            'rejected' => 'background:#dc3545',
                                                            'review' => 'background:#0dcaf0',
                                                            'archived' => 'background:#212529',
                                                            'status_changed' => 'background:#fd7e14',
                                                            'deleted' => 'background:#dc3545',
                                                        ]; @endphp
                                                    {{ $colors[$history->action] ?? 'background:#6c757d' }};
                                             border:2px solid #fff; box-shadow:0 1px 3px rgba(0,0,0,.1);">
                                                </div>

                                                <div class="timeline-content">
                                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                                        <span
                                                            class="badge badge-sm bg-secondary bg-opacity-10 text-dark fw-normal">
                                                            @php
                                                                $actionLabels = [
                                                                    'created' => 'Dibuat',
                                                                    'updated' => 'Diperbarui',
                                                                    'read' => 'Dibaca',
                                                                    'unread' => 'Tandai Belum Dibaca',
                                                                    'sent' => 'Dikirim',
                                                                    'disposisi' => 'Disposisi',
                                                                    'approved' => 'Siap Dikirim',
                                                                    'rejected' => 'Ditolak',
                                                                    'review' => 'Review',
                                                                    'archived' => 'Diarsipkan',
                                                                    'status_changed' => 'Ubah Status',
                                                                    'deleted' => 'Dihapus',
                                                                ];
                                                            @endphp
                                                            <i
                                                                class="fi fi-rr-{{ $history->action == 'read' ? 'envelope-open' : ($history->action == 'disposisi' ? 'share' : 'circle') }} me-1"></i>
                                                            {{ $actionLabels[$history->action] ?? ucfirst($history->action) }}
                                                        </span>
                                                        <small class="text-muted"
                                                            style="font-size:10px; white-space:nowrap;">
                                                            {{ $history->created_at->diffForHumans() }}
                                                        </small>
                                                    </div>

                                                    @if ($history->keterangan)
                                                        <p class="mb-0 small">{{ $history->keterangan }}</p>
                                                    @endif

                                                    @if ($history->user)
                                                        <small class="text-muted">
                                                            <i class="fi fi-rr-user me-1"></i> {{ $history->user->name }}
                                                        </small>
                                                    @endif

                                                    <small class="text-muted d-block" style="font-size:10px;">
                                                        {{ $history->created_at->translatedFormat('d F Y H:i') }}
                                                    </small>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-center text-muted py-4">
                                        <i class="fi fi-rr-empty-set" style="font-size: 2rem;"></i>
                                        <p class="mt-2 mb-0">Belum ada riwayat</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Konfirmasi Kirim Surat --}}
    <div class="modal fade" id="modalKirimSuratShow" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h6 class="modal-title">
                        <i class="fi fi-rr-paper-plane text-success me-1"></i>
                        Konfirmasi Kirim Surat
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">Kirim surat ini ke tujuan?</p>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                        <i class="fi fi-rr-cross me-1"></i> Batal
                    </button>
                    <button type="button" class="btn btn-success btn-sm" id="btnKonfirmasiKirimShow">
                        <i class="fi fi-rr-paper-plane me-1"></i> Ya, Kirim
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        // Konfirmasi Kirim Surat dengan modal
        var $formKirimShow = null;

        $(document).on('click', '.btn-kirim-surat-show', function(e) {
            e.preventDefault();
            $formKirimShow = $('<form>', {
                action: $(this).data('url'),
                method: 'POST',
                class: 'd-none'
            }).append(
                '<input type="hidden" name="_token" value="{{ csrf_token() }}">',
                '<input type="hidden" name="_method" value="PATCH">'
            );
            $('body').append($formKirimShow);
            $('#modalKirimSuratShow').modal('show');
        });

        $('#btnKonfirmasiKirimShow').on('click', function() {
            $('#modalKirimSuratShow').modal('hide');
            if ($formKirimShow) {
                $formKirimShow.submit();
            }
        });
    </script>
@endpush
