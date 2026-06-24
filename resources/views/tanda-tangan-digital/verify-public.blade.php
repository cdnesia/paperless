<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Berkas | {{ config('app.name') }}</title>
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
        .upload-section {
            margin-top: 20px;
            padding: 20px;
            border: 1px dashed #d1d5db;
            border-radius: 12px;
            background: #fafafa;
        }
        .upload-section h6 {
            font-weight: 700;
            margin-bottom: 4px;
        }
        .upload-section p {
            margin-bottom: 12px;
        }
        .btn-verify {
            display: block;
            width: 100%;
            padding: 10px 16px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: background 0.2s;
            background: #0d6efd;
            color: #fff;
        }
        .btn-verify:hover {
            background: #0b5ed7;
            color: #fff;
        }
    </style>
</head>
<body>
    <div class="verify-card">
        <div class="verify-header">
            <h5><i class="fi fi-rr-file-check me-1"></i> Verifikasi Berkas</h5>
        </div>
        <div class="verify-body">
            {{-- Detail Surat --}}
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

            {{-- Upload Berkas untuk Verifikasi --}}
            <div class="upload-section">
                <p class="text-muted small">Upload berkas PDF untuk memverifikasi keaslian dokumen.</p>
                <form action="{{ route('tanda-tangan-digital.verify-upload', $tandaTanganDigital) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <input type="file" name="file" accept=".pdf" class="form-control" required>
                    </div>
                    <button type="submit" class="btn-verify">
                        <i class="fi fi-rr-file-check me-1"></i> Verifikasi
                    </button>
                </form>

                {{-- Hasil Verifikasi Upload --}}
                @if (isset($uploadValid))
                    <div class="mt-3">
                        @if ($uploadValid)
                            <div class="status-badge valid d-block text-center">
                                <i class="fi fi-rr-check-circle"></i> Berkas ASLI — Tanda Tangan Digital Valid
                            </div>
                        @else
                            <div class="status-badge invalid d-block text-center">
                                <i class="fi fi-rr-cross-circle"></i> Berkas Tidak Sah — Dokumen Telah Dimodifikasi
                            </div>
                        @endif
                        <div class="hash-text mt-2">
                            <strong>SHA256 Berkas:</strong><br>{{ $uploadHash }}
                        </div>
                    </div>
                @endif
            </div>

            <div class="footer-text">
                Diselenggarakan oleh ICT Center UM Jambi &mdash;
                Verifikasi keaslian menggunakan SHA256
            </div>
        </div>
    </div>
</body>
</html>
