@extends('layouts.app')
@section('title', 'Disposisi Keluar - Detail')
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
                            <a href="{{ route('disposisi.index') }}" class="btn btn-outline-secondary btn-sm">
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
                                            <i class="fi fi-rr-file-pdf me-1"></i> Lihat Surat
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
                                        <a href="{{ $surat->lampiranUrl() }}" target="_blank" rel="noopener">
                                            <i class="fi fi-rr-file-pdf me-1"></i> Lihat Lampiran
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

            {{-- KANAN: Riwayat Disposisi --}}
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center border-0 pb-2">
                        <h6 class="card-title mb-0">
                            <i class="fi fi-rr-share me-1"></i> Riwayat Disposisi
                        </h6>
                        <span class="badge badge-sm bg-{{ $disposisi->status === 'selesai' || $disposisi->status === 'diterima' ? 'success' : ($disposisi->status === 'ditolak' ? 'danger' : 'info') }} bg-opacity-10 text-dark">
                            {{ ucfirst($disposisi->status) }}
                        </span>
                    </div>
                    <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                        @php
                            $surat = $disposisi->suratKeluar;
                            $allDisposisions = $surat->disposisis()->with('pengguna', 'pengirim')->oldest()->get();
                        @endphp
                        @if ($allDisposisions->count() > 0)
                            @foreach ($allDisposisions as $item)
                                @php $isInvolved = auth()->user()->hasRole('super-admin') || $item->pengirim_id === auth()->id() || $item->pengguna_id === auth()->id(); @endphp
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
                                        @if ($isInvolved && $item->keterangan)
                                            <p class="mb-0 small text-muted">{{ $item->keterangan }}</p>
                                        @endif
                                        @if ($isInvolved && $item->alasan)
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
        <script>showToast('success', '{{ session('success') }}');</script>
    @endif
    @if (session('error'))
        <script>showToast('error', '{{ session('error') }}');</script>
    @endif
@endsection
