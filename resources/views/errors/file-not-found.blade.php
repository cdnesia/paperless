@extends('layouts.app')

@section('title', 'File Tidak Tersedia')

@section('content')
<div class="app-content">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="text-center py-5">
                    <div class="mb-4">
                        <i class="fi fi-rr-file-pdf text-danger" style="font-size: 5rem;"></i>
                    </div>
                    <h4 class="fw-bold mb-2">File Tidak Tersedia</h4>
                    <p class="text-muted mb-1">
                        File <strong>{{ $filename }}</strong> tidak ditemukan di server.
                    </p>
                    <p class="text-muted small">
                        File mungkin sudah dihapus, dipindahkan, atau belum di-generate.
                        Silakan hubungi administrator jika Anda yakin file ini seharusnya ada.
                    </p>
                    <div class="mt-4">
                        <a href="javascript:history.back()" class="btn btn-outline-secondary me-2">
                            <i class="fi fi-rr-arrow-left me-1"></i> Kembali
                        </a>
                        <a href="{{ route('dashboard') }}" class="btn btn-primary">
                            <i class="fi fi-ts-dashboard-monitor me-1"></i> Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
