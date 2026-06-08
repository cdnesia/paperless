@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-12">
                <div class="card bg-primary text-white">
                    <div class="card-body p-4">
                        <h3 class="text-white mb-2">Selamat Datang, {{ Auth::user()->name }}! 👋</h3>
                        <p class="mb-0 opacity-75">Selamat datang di dashboard {{ config('app.name') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            {{-- Surat Masuk --}}
            <div class="col-xxl-3 col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-muted small mb-1">Surat Masuk</p>
                                <h2 class="mb-0 fw-bold">{{ $suratMasuk }}</h2>
                            </div>
                            <div class="rounded-circle bg-info bg-opacity-10 d-flex align-items-center justify-content-center"
                                style="width:48px; height:48px;">
                                <i class="fi fi-rr-envelope-download text-info fs-5"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Surat Keluar --}}
            <div class="col-xxl-3 col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-muted small mb-1">Surat Keluar</p>
                                <h2 class="mb-0 fw-bold">{{ $suratKeluar }}</h2>
                            </div>
                            <div class="rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center"
                                style="width:48px; height:48px;">
                                <i class="fi fi-rr-paper-plane text-success fs-5"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Disposisi Masuk --}}
            <div class="col-xxl-3 col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-muted small mb-1">Disposisi Masuk</p>
                                <h2 class="mb-0 fw-bold">{{ $disposisiMasuk }}</h2>
                            </div>
                            <div class="rounded-circle bg-warning bg-opacity-10 d-flex align-items-center justify-content-center"
                                style="width:48px; height:48px;">
                                <i class="fi fi-rr-user-add text-warning fs-5"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Disposisi Keluar --}}
            <div class="col-xxl-3 col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-muted small mb-1">Disposisi Keluar</p>
                                <h2 class="mb-0 fw-bold">{{ $disposisiKeluar }}</h2>
                            </div>
                            <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center"
                                style="width:48px; height:48px;">
                                <i class="fi fi-rr-users text-primary fs-5"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
