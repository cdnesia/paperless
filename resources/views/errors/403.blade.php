@extends('layouts.app')
@section('title', 'Akses Ditolak')
@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <div class="mb-4">
                            <i class="fi fi-rr-lock" style="font-size:64px;color:#dc3545;"></i>
                        </div>
                        <h4 class="fw-bold text-danger mb-2">Akses Ditolak</h4>
                        <p class="text-muted mb-1">Anda tidak memiliki izin untuk mengakses halaman ini.</p>
                        <p class="text-muted mb-2">Silahkan hubungi administrator anda.</p>
                        <a href="{{ url()->previous() ?? route('dashboard') }}" class="btn btn-primary">
                            <i class="fi fi-rr-arrow-left me-1"></i> Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
