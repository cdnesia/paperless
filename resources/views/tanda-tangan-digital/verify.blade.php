@extends('layouts.app')
@section('title', 'Verifikasi Tanda Tangan Digital')
@section('content')
    <div class="container-fluid">
        <div class="app-page-head d-flex mb-2 flex-wrap align-items-center justify-content-between">
            <div class="clearfix">
                <h6 class="app-page-title">
                    <i class="fi fi-rr-search me-1"></i>
                    Verifikasi Tanda Tangan Digital
                </h6>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('tanda-tangan-digital.index', $tandaTanganDigital->suratKeluar) }}"
                    class="btn btn-outline-secondary btn-sm">
                    <i class="fi fi-rr-arrow-left me-1"></i> Kembali
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                {{-- Status Verifikasi --}}
                <div class="card mb-3">
                    <div class="card-header border-0 pb-2">
                        <h6 class="card-title mb-0"><i class="fi fi-rr-shield me-1"></i> Status Keaslian</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            {{-- Status Hash Original --}}
                            <div class="col-md-6">
                                <div class="border rounded p-3 text-center h-100
                                    @if ($statusOriginal === 'valid') border-success bg-success bg-opacity-10
                                    @elseif($statusOriginal === 'invalid') border-danger bg-danger bg-opacity-10
                                    @else border-secondary bg-light @endif">
                                    <div class="mb-2">
                                        @if ($statusOriginal === 'valid')
                                            <i class="fi fi-rr-check-circle text-success" style="font-size:2rem;"></i>
                                            <h6 class="text-success mt-2">Hash Original VALID</h6>
                                        @elseif($statusOriginal === 'invalid')
                                            <i class="fi fi-rr-cross-circle text-danger" style="font-size:2rem;"></i>
                                            <h6 class="text-danger mt-2">Hash Original TIDAK VALID</h6>
                                        @else
                                            <i class="fi fi-rr-question-circle text-secondary" style="font-size:2rem;"></i>
                                            <h6 class="text-secondary mt-2">Tidak Dapat Diverifikasi</h6>
                                        @endif
                                    </div>
                                    <small class="text-muted">File PDF asli tidak dimodifikasi</small>
                                </div>
                            </div>

                            {{-- Status Hash Final --}}
                            <div class="col-md-6">
                                <div class="border rounded p-3 text-center h-100
                                    @if ($statusFinal === 'valid') border-success bg-success bg-opacity-10
                                    @elseif($statusFinal === 'invalid') border-danger bg-danger bg-opacity-10
                                    @else border-secondary bg-light @endif">
                                    <div class="mb-2">
                                        @if ($statusFinal === 'valid')
                                            <i class="fi fi-rr-check-circle text-success" style="font-size:2rem;"></i>
                                            <h6 class="text-success mt-2">Hash Final VALID</h6>
                                        @elseif($statusFinal === 'invalid')
                                            <i class="fi fi-rr-cross-circle text-danger" style="font-size:2rem;"></i>
                                            <h6 class="text-danger mt-2">Hash Final TIDAK VALID</h6>
                                        @else
                                            <i class="fi fi-rr-question-circle text-secondary" style="font-size:2rem;"></i>
                                            <h6 class="text-secondary mt-2">Tidak Dapat Diverifikasi</h6>
                                        @endif
                                    </div>
                                    <small class="text-muted">File PDF final tidak dimodifikasi</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Detail Verifikasi --}}
                <div class="card mb-3">
                    <div class="card-header border-0 pb-2">
                        <h6 class="card-title mb-0"><i class="fi fi-rr-detail me-1"></i> Detail Verifikasi</h6>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-bordered table-striped mb-0">
                            <tr>
                                <th style="width:180px;">Nomor Surat</th>
                                <td class="fw-bold">{{ $tandaTanganDigital->suratKeluar->nomor_surat ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Perihal</th>
                                <td>{{ $tandaTanganDigital->suratKeluar->perihal }}</td>
                            </tr>
                            <tr>
                                <th>Penandatangan</th>
                                <td>{{ $tandaTanganDigital->penandatangan }}</td>
                            </tr>
                            <tr>
                                <th>Lokasi</th>
                                <td>{{ $tandaTanganDigital->lokasi ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Waktu Tanda Tangan</th>
                                <td>{{ $tandaTanganDigital->signed_at ? $tandaTanganDigital->signed_at->translatedFormat('d F Y H:i:s') : '-' }}
                                </td>
                            </tr>
                            <tr>
                                <th>IP Address</th>
                                <td><code>{{ $tandaTanganDigital->ip_address ?? '-' }}</code></td>
                            </tr>
                            <tr>
                                <th>Hash Original (tersimpan)</th>
                                <td>
                                    <code style="font-size:11px; word-break:break-all;">
                                        {{ $tandaTanganDigital->hash_sha256_original }}
                                    </code>
                                </td>
                            </tr>
                            <tr>
                                <th>Hash Original (sekarang)</th>
                                <td>
                                    @if ($hashOriginalSekarang)
                                        <code style="font-size:11px; word-break:break-all;">
                                            {{ $hashOriginalSekarang }}
                                        </code>
                                        @if ($hashOriginalSekarang === $tandaTanganDigital->hash_sha256_original)
                                            <span class="badge bg-success ms-1">Sama</span>
                                        @else
                                            <span class="badge bg-danger ms-1">Berbeda!</span>
                                        @endif
                                    @else
                                        <span class="text-muted">File tidak ditemukan</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Hash Final (tersimpan)</th>
                                <td>
                                    <code style="font-size:11px; word-break:break-all;">
                                        {{ $tandaTanganDigital->hash_sha256_final ?? '-' }}
                                    </code>
                                </td>
                            </tr>
                            <tr>
                                <th>Hash Final (sekarang)</th>
                                <td>
                                    @if ($hashFinalSekarang)
                                        <code style="font-size:11px; word-break:break-all;">
                                            {{ $hashFinalSekarang }}
                                        </code>
                                        @if ($hashFinalSekarang === $tandaTanganDigital->hash_sha256_final)
                                            <span class="badge bg-success ms-1">Sama</span>
                                        @else
                                            <span class="badge bg-danger ms-1">Berbeda!</span>
                                        @endif
                                    @else
                                        <span class="text-muted">File tidak ditemukan</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Kolom Kanan: File --}}
            <div class="col-lg-4">
                {{-- QR Code --}}
                @if ($tandaTanganDigital->qr_code)
                    <div class="card mb-3">
                        <div class="card-header border-0 pb-2">
                            <h6 class="card-title mb-0"><i class="fi fi-rr-qr-scan me-1"></i> QR Code</h6>
                        </div>
                        <div class="card-body text-center">
                            <img src="{{ Storage::url($tandaTanganDigital->qr_code) }}"
                                alt="QR Code" class="img-fluid" style="max-width:200px;">
                            <p class="small text-muted mt-2 mb-0">Scan untuk verifikasi keaslian</p>
                        </div>
                    </div>
                @endif

                {{-- Tombol Download --}}
                <div class="card">
                    <div class="card-body">
                        @if ($tandaTanganDigital->file_pdf_final)
                            <a href="{{ $tandaTanganDigital->pdfFinalUrl() }}" target="_blank"
                                class="btn btn-danger w-100 mb-2">
                                <i class="fi fi-rr-file-pdf me-1"></i> Download PDF Final
                            </a>
                        @endif
                        @if ($tandaTanganDigital->suratKeluar->file_pdf)
                            <a href="{{ $tandaTanganDigital->suratKeluar->pdfUrl() }}" target="_blank"
                                class="btn btn-outline-danger w-100">
                                <i class="fi fi-rr-file-pdf me-1"></i> Download PDF Asli
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
