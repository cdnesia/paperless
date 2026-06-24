<!DOCTYPE html>
<html lang="id">
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
            max-width: 520px;
            width: 100%;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        .verify-header {
            padding: 28px 24px 20px;
            text-align: center;
            border-bottom: 1px solid #e9ecef;
        }
        .verify-header .icon-circle {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: #e8f0fe;
            margin-bottom: 12px;
        }
        .verify-header .icon-circle i {
            font-size: 24px;
            color: #0d6efd;
        }
        .verify-header h5 {
            margin: 0;
            font-weight: 700;
            font-size: 16px;
        }
        .verify-header p {
            margin: 6px 0 0;
            font-size: 13px;
            color: #6b7280;
        }
        .verify-body {
            padding: 24px;
        }
        .upload-zone {
            border: 2px dashed #d1d5db;
            border-radius: 12px;
            padding: 32px 20px;
            text-align: center;
            cursor: pointer;
            transition: border-color 0.2s, background 0.2s;
            background: #fafafa;
        }
        .upload-zone:hover {
            border-color: #0d6efd;
            background: #e8f0fe;
        }
        .upload-zone i {
            font-size: 36px;
            color: #9ca3af;
            margin-bottom: 8px;
            display: block;
        }
        .upload-zone .upload-text {
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 4px;
        }
        .upload-zone .upload-hint {
            font-size: 12px;
            color: #9ca3af;
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
            margin-top: 12px;
        }
        .btn-verify:hover {
            background: #0b5ed7;
            color: #fff;
        }
        .error-box {
            margin-top: 16px;
            padding: 12px 16px;
            border-radius: 8px;
            background: #fee2e2;
            color: #991b1b;
            font-size: 13px;
            font-weight: 500;
        }
        .error-box i {
            margin-right: 6px;
        }
        .hash-text-small {
            font-family: 'Courier New', monospace;
            font-size: 11px;
            background: #f9fafb;
            padding: 8px 12px;
            border-radius: 6px;
            word-break: break-all;
            margin-top: 8px;
            color: #6b7280;
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
            <div class="icon-circle">
                <i class="fi fi-rr-file-check"></i>
            </div>
            <h5>Verifikasi Berkas</h5>
            <p>Upload berkas PDF untuk memverifikasi keaslian tanda tangan digital</p>
        </div>
        <div class="verify-body">
            <form action="{{ route('verifikasi.post') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="upload-zone" onclick="document.getElementById('fileInput').click()">
                    <i class="fi fi-rr-file-pdf"></i>
                    <div class="upload-text">Pilih atau seret berkas PDF</div>
                    <div class="upload-hint">Format PDF, maksimal 10 MB</div>
                </div>
                <input type="file" id="fileInput" name="file" accept=".pdf" class="d-none" required
                       onchange="document.querySelector('.upload-zone .upload-text').textContent = this.files[0]?.name || 'Pilih atau seret berkas PDF'">
                <button type="submit" class="btn-verify">
                    <i class="fi fi-rr-search me-1"></i> Verifikasi
                </button>
            </form>

            @if (session('error'))
                <div class="error-box">
                    <i class="fi fi-rr-exclamation-triangle"></i> {{ session('error') }}
                </div>
                @if (session('hashUpload'))
                    <div class="hash-text-small">
                        <strong>SHA256:</strong> {{ session('hashUpload') }}
                    </div>
                @endif
            @endif

            <div class="footer-text">
                Diselenggarakan oleh {{ config('app.name') }}
            </div>
        </div>
    </div>
</body>
</html>
