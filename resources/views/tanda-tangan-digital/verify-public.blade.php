<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Tanda Tangan Digital | {{ config('app.name') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('') }}assets/images/favicon.png">
    <link rel="stylesheet" href="{{ asset('') }}assets/libs/flaticon/css/all/all.css">
    <link rel="stylesheet" href="{{ asset('') }}assets/css/styles.css">
    <style>
        body {
            background: #f5f7fb;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        .verify-card {
            max-width: 600px;
            width: 100%;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        .verify-header {
            padding: 24px;
            text-align: center;
            border-bottom: 1px solid #e9ecef;
        }
        .verify-header h5 {
            margin: 0;
            font-weight: 700;
        }
        .verify-body {
            padding: 24px;
        }
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            border-radius: 100px;
            font-weight: 600;
            font-size: 14px;
        }
        .status-badge.valid {
            background: #d1fae5;
            color: #065f46;
        }
        .status-badge.invalid {
            background: #fee2e2;
            color: #991b1b;
        }
        .status-badge.unknown {
            background: #f3f4f6;
            color: #6b7280;
        }
        .detail-table {
            width: 100%;
            font-size: 13px;
        }
        .detail-table td {
            padding: 8px 0;
            border-bottom: 1px solid #f3f4f6;
        }
        .detail-table td:first-child {
            color: #6b7280;
            width: 140px;
        }
        .detail-table td:last-child {
            font-weight: 500;
            word-break: break-all;
        }
        .hash-text {
            font-family: 'Courier New', monospace;
            font-size: 11px;
            background: #f9fafb;
            padding: 8px 12px;
            border-radius: 6px;
            display: inline-block;
            word-break: break-all;
        }
        .btn-download {
            display: block;
            width: 100%;
            padding: 10px;
            border-radius: 8px;
            font-weight: 600;
            text-align: center;
            text-decoration: none;
            margin-bottom: 8px;
        }
        .btn-download-primary {
            background: #dc3545;
            color: #fff;
        }
        .btn-download-primary:hover {
            background: #bb2d3b;
            color: #fff;
        }
        .btn-download-secondary {
            background: #f3f4f6;
            color: #374151;
        }
        .btn-download-secondary:hover {
            background: #e5e7eb;
            color: #374151;
        }
        .footer-text {
            text-align: center;
            font-size: 11px;
            color: #9ca3af;
            margin-top: 16px;
        }
    </style>
</head>
<body>
    <div class="verify-card">
        <div class="verify-header">
            <h5><i class="fi fi-rr-search me-1"></i> Verifikasi Tanda Tangan Digital</h5>
        </div>
        <div class="verify-body">
            {{-- Status Verifikasi --}}
            <div class="text-center mb-4">
                @if ($mode === 'dual')
                    @if ($status === 'valid')
                        <div class="status-badge valid">
                            <i class="fi fi-rr-check-circle"></i> Dokumen ASLI & Tanda Tangan VALID
                        </div>
                    @else
                        <div class="status-badge invalid">
                            <i class="fi fi-rr-cross-circle"></i> Dokumen TIDAK VALID — telah dimodifikasi!
                        </div>
                    @endif
                @else
                    @if ($status === 'valid')
                        <div class="status-badge valid">
                            <i class="fi fi-rr-check-circle"></i> BERKAS VALID
                        </div>
                    @elseif ($status === 'invalid')
                        <div class="status-badge invalid">
                            <i class="fi fi-rr-cross-circle"></i> BERKAS TIDAK VALID
                        </div>
                    @else
                        <div class="status-badge unknown">
                            <i class="fi fi-rr-question-circle"></i> Tidak dapat diverifikasi (file tidak ditemukan)
                        </div>
                    @endif
                @endif
            </div>

            {{-- Detail --}}
            <table class="detail-table">
                <tr>
                    <td>Nomor Surat</td>
                    <td>{{ $tandaTanganDigital->suratKeluar->nomor_surat ?? '-' }}</td>
                </tr>
                <tr>
                    <td>Perihal</td>
                    <td>{{ $tandaTanganDigital->suratKeluar->perihal }}</td>
                </tr>
                <tr>
                    <td>Penandatangan</td>
                    <td>{{ $tandaTanganDigital->penandatangan }}</td>
                </tr>
                <tr>
                    <td>Lokasi</td>
                    <td>{{ $tandaTanganDigital->lokasi ?? '-' }}</td>
                </tr>
                <tr>
                    <td>Waktu Tanda Tangan</td>
                    <td>{{ $tandaTanganDigital->signed_at ? $tandaTanganDigital->signed_at->translatedFormat('d F Y H:i:s') : '-' }}</td>
                </tr>
            </table>

            {{-- QR Code --}}
            <div class="d-flex gap-3 mt-4 justify-content-center">
                @if ($tandaTanganDigital->qr_code)
                    <div class="text-center">
                        <small class="text-muted d-block mb-1">QR Code</small>
                        <img src="{{ Storage::url($tandaTanganDigital->qr_code) }}"
                             alt="QR Code" style="max-width:120px;">
                    </div>
                @endif
            </div>

            <div class="footer-text">
                Sistem {{ config('app.name') }} — Verifikasi otomatis menggunakan SHA256
            </div>
        </div>
    </div>
</body>
</html>
