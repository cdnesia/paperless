@extends('layouts.app')
@section('title', 'Tanda Tangan Digital')
@section('content')
    <div class="container-fluid">
        <div class="app-page-head d-flex mb-2 flex-wrap align-items-center justify-content-between">
            <div class="clearfix">
                <h6 class="app-page-title">
                    <i class="fi fi-tr-attribution-pen me-1"></i>
                    Tanda Tangan Digital — {{ $suratKeluar->nomor_surat ?? $suratKeluar->perihal }}
                </h6>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('surat-keluar.show', $suratKeluar) }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fi fi-rr-arrow-left me-1"></i> Kembali
                </a>
            </div>
        </div>

        {{-- Alert jika belum ada hash --}}
        @if (!$hashOriginal)
            <div class="alert alert-warning">
                <i class="fi fi-rr-exclamation me-1"></i>
                File PDF surat belum tersedia. Hash SHA256 tidak dapat digenerate.
            </div>
        @endif

        <div class="row">
            {{-- Kolom Kiri: Info Surat & Form Tanda Tangan --}}
            <div class="col-lg-6">
                {{-- Card Info Surat --}}
                <div class="card mb-3">
                    <div class="card-header border-0 pb-2">
                        <h6 class="card-title mb-0"><i class="fi fi-rr-info me-1"></i> Informasi Surat</h6>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-bordered table-striped mb-0">
                            <tr>
                                <th style="width:140px;">Nomor Surat</th>
                                <td class="fw-bold">{{ $suratKeluar->nomor_surat ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Perihal</th>
                                <td>{{ $suratKeluar->perihal }}</td>
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
                                <th>Hash SHA256</th>
                                <td>
                                    @if ($hashOriginal)
                                        <code style="font-size:11px; word-break:break-all;">{{ $hashOriginal }}</code>
                                        <button class="btn btn-sm btn-outline-secondary py-0 px-1 ms-1"
                                            onclick="copyToClipboard('{{ $hashOriginal }}')" title="Salin hash">
                                            <i class="fi fi-rr-copy"></i>
                                        </button>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>File PDF</th>
                                <td>
                                    @if ($suratKeluar->file_pdf)
                                        <a href="{{ $suratKeluar->pdfUrl() }}" target="_blank" rel="noopener">
                                            <i class="fi fi-rr-file-pdf me-1"></i> Lihat PDF
                                        </a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>File Lampiran</th>
                                <td>
                                    @if ($suratKeluar->file_lampiran)
                                        <a href="{{ Storage::url($suratKeluar->file_lampiran) }}" target="_blank"
                                            rel="noopener">
                                            <i class="fi fi-rr-file-pdf me-1"></i> Lihat Lampiran
                                        </a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card mb-3">
                    <div class="card-header border-0 pb-2">
                        <h6 class="card-title mb-0"><i class="fi fi-rr-signature me-1"></i> Tanda Tangan Digital</h6>
                    </div>
                    <div class="card-body">
                            <p class="small text-muted mb-3">
                                <i class="fi fi-rr-info me-1"></i>
                                Proses: Hash PDF asli → Tempel QR Code → Generate PDF final → Hash baru
                            </p>

                            @if ($suratKeluar->status !== 's' && $suratKeluar->status !== 'e')
                                <form action="{{ route('tanda-tangan-digital.sign', $suratKeluar) }}" method="POST"
                                    id="formSign">
                                    @csrf

                                    <div class="mb-3">
                                        <label class="form-label">Penandatangan</label>
                                        <input type="text" class="form-control" value="{{ Auth::user()->name }}" readonly>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Lokasi</label>
                                        <input type="text" class="form-control" name="lokasi" value="Jambi" required>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Tanggal Tanda Tangan</label>
                                        <input type="datetime-local" class="form-control" name="signed_at"
                                            value="{{ now()->format('Y-m-d\TH:i') }}" required readonly>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">IP Address</label>
                                        <input type="text" class="form-control" value="{{ request()->ip() }}" readonly>
                                    </div>

                                    <div class="alert alert-info mb-3">
                                        <i class="fi fi-rr-info me-1"></i>
                                        Dengan menekan tombol di bawah, sistem akan:
                                        <ol class="mb-0 mt-1 ps-3">
                                            <li>Hash SHA256 dokumen asli</li>
                                            <li>Tempel QR Code verifikasi di halaman terakhir PDF</li>
                                            <li>Generate PDF final & hash SHA256 baru</li>
                                            <li>Simpan metadata tanda tangan digital (tanpa gambar tanda tangan)</li>
                                        </ol>
                                    </div>

                                    <button type="submit" class="btn btn-success btn-sm w-100" id="btnSign">
                                        <i class="fi fi-rr-file-pen me-1"></i> Tandatangani Sekarang
                                    </button>
                                </form>
                            @else
                                <div class="alert alert-info mb-0">
                                    <i class="fi fi-rr-info me-1"></i>
                                    Surat sudah {{ $suratKeluar->status === 's' ? 'terkirim' : 'diarsipkan' }},
                                    tidak dapat ditandatangani lagi.
                                </div>
                            @endif
                    </div>
                </div>
            </div>

            {{-- Kolom Kanan: Riwayat Tanda Tangan --}}
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header border-0 pb-2">
                        <h6 class="card-title mb-0">
                            <i class="fi fi-rr-stamp me-1"></i>
                            Riwayat Tanda Tangan Digital
                            <span class="badge bg-primary ms-1">{{ $signatures->count() }}</span>
                        </h6>
                    </div>
                    <div class="card-body">
                        @if ($signatures->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-sm">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Penandatangan</th>
                                            <th>Lokasi</th>
                                            <th>Hash Original</th>
                                            <th>Hash Final</th>
                                            <th>Waktu</th>
                                            <th>IP</th>
                                            <th class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($signatures as $index => $sig)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $sig->penandatangan }}</td>
                                                <td>{{ $sig->lokasi ?? '-' }}</td>
                                                <td>
                                                    <code
                                                        style="font-size:10px;">{{ substr($sig->hash_sha256_original, 0, 16) }}...</code>
                                                </td>
                                                <td>
                                                    @if ($sig->hash_sha256_final)
                                                        <code
                                                            style="font-size:10px;">{{ substr($sig->hash_sha256_final, 0, 16) }}...</code>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td style="white-space:nowrap;">
                                                    {{ $sig->signed_at ? $sig->signed_at->translatedFormat('d F Y H:i') : '-' }}
                                                </td>
                                                <td><code>{{ $sig->ip_address ?? '-' }}</code></td>
                                                <td class="text-center">
                                                    <div class="d-flex gap-1 justify-content-center">
                                                        <a href="{{ route('tanda-tangan-digital.verify', $sig) }}"
                                                            class="btn btn-sm btn-outline-info" title="Verifikasi">
                                                            <i class="fi fi-rr-search"></i>
                                                        </a>
                                                        @if ($sig->file_pdf_final)
                                                            <a href="{{ $sig->pdfFinalUrl() }}"
                                                                target="_blank" class="btn btn-sm btn-outline-danger"
                                                                title="Download PDF Final">
                                                                <i class="fi fi-rr-file-pdf"></i>
                                                            </a>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center text-muted py-4">
                                <i class="fi fi-rr-stamp" style="font-size: 2rem;"></i>
                                <p class="mt-2 mb-0">Belum ada tanda tangan digital</p>
                                <small>Silakan klik tombol di samping untuk menandatangani surat.</small>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        // ─── Copy to clipboard ──────────────────────────────────
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                showToast('success', 'Hash berhasil disalin');
            }).catch(() => {
                // Fallback
                const textarea = document.createElement('textarea');
                textarea.value = text;
                document.body.appendChild(textarea);
                textarea.select();
                document.execCommand('copy');
                document.body.removeChild(textarea);
                showToast('success', 'Hash berhasil disalin');
            });
        }
    </script>
@endpush
