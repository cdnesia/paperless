@extends('layouts.app')
@section('title', 'Profil Saya')
@section('content')
    <div class="container-fluid">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row">

            {{-- Profile Header Card --}}
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex flex-wrap gap-4 align-items-center">
                            <div class="d-flex align-items-center">
                                <div class="position-relative">
                                    <div class="avatar avatar-xxl rounded-circle">
                                        <img src="{{ asset('') }}assets/images/avatar/avatar3.webp" alt="">
                                    </div>
                                </div>
                                <div class="ms-3">
                                    <h4 class="fw-bold mb-0">{{ $user->name }}</h4>
                                    <small class="mb-2 text-muted">{{ $user->email }}</small>
                                    <div class="d-flex flex-wrap gap-1 mt-2">
                                        @forelse ($user->roles as $role)
                                            <span class="badge badge-sm px-2 rounded-pill text-bg-primary">{{ $role->name }}</span>
                                        @empty
                                            <span class="badge badge-sm px-2 rounded-pill text-bg-secondary">Tidak ada role</span>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Left Column: Info Cards --}}
            <div class="col-lg-4 col-sm-12">
                <div class="row">
                    {{-- Basic Information --}}
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title mb-0">Informasi Akun</h4>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <span class="text-muted small">Nama Lengkap</span>
                                    <p class="text-dark fw-semibold mb-0">{{ $user->name }}</p>
                                </div>
                                <div class="mb-3">
                                    <span class="text-muted small">Email</span>
                                    <p class="text-dark fw-semibold mb-0">{{ $user->email }}</p>
                                </div>
                                <div class="mb-3">
                                    <span class="text-muted small">Role</span>
                                    <p class="text-dark fw-semibold mb-0">
                                        @forelse ($user->roles as $role)
                                            <span class="badge badge-sm px-2 rounded-pill text-bg-primary me-1">{{ $role->name }}</span>
                                        @empty
                                            <span class="text-muted">-</span>
                                        @endforelse
                                    </p>
                                </div>
                                <div class="mb-2">
                                    <span class="text-muted small">Bergabung Sejak</span>
                                    <p class="text-dark fw-semibold mb-0">{{ $user->created_at->format('d F Y') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column: Account Settings --}}
            <div class="col-lg-8 col-sm-12">
                <div class="row">
                    {{-- Edit Profile Form --}}
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Pengaturan Akun</h4>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('profile.update') }}" method="POST">
                                    @csrf
                                    @method('PUT')

                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label" for="name">Nama Lengkap</label>
                                            <input type="text" name="name" id="name"
                                                class="form-control @error('name') is-invalid @enderror"
                                                value="{{ old('name', $user->name) }}" required>
                                            @error('name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label" for="email">Email</label>
                                            <input type="email" name="email" id="email"
                                                class="form-control @error('email') is-invalid @enderror"
                                                value="{{ old('email', $user->email) }}" required>
                                            @error('email')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <hr class="my-4">
                                    <h5 class="mb-3">Ubah Password</h5>
                                    <small class="text-muted d-block mb-3">Kosongkan jika tidak ingin mengubah password.</small>

                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label" for="current_password">Password Saat Ini</label>
                                            <input type="password" name="current_password" id="current_password"
                                                class="form-control @error('current_password') is-invalid @enderror"
                                                placeholder="Masukkan password saat ini">
                                            @error('current_password')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label" for="new_password">Password Baru</label>
                                            <input type="password" name="new_password" id="new_password"
                                                class="form-control @error('new_password') is-invalid @enderror"
                                                placeholder="Minimal 8 karakter">
                                            @error('new_password')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label" for="new_password_confirmation">Konfirmasi Password Baru</label>
                                            <input type="password" name="new_password_confirmation" id="new_password_confirmation"
                                                class="form-control"
                                                placeholder="Ulangi password baru">
                                        </div>
                                    </div>

                                    <div class="text-end mt-4">
                                        <button type="submit" class="btn btn-success waves-effect waves-light btn-sm">
                                            <i class="fi fi-rr-check me-1"></i> Simpan Perubahan
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    {{-- Danger Zone --}}
                    <div class="col-12">
                        <div class="card border border-danger bg-danger-subtle border-2">
                            <div class="card-header border-0 pb-0">
                                <h5 class="text-danger fw-bold mb-0">Zona Berbahaya</h5>
                                <small class="text-muted">Tindakan penting yang mempengaruhi akun Anda.</small>
                            </div>
                            <div class="card-body">
                                <div class="d-flex gap-3 justify-content-between align-items-start mb-4 flex-wrap">
                                    <div class="pe-3">
                                        <h6 class="text-danger mb-1">Hapus Akun</h6>
                                        <p class="mb-0 small">Tindakan ini <strong>permanen</strong> dan tidak bisa dibatalkan. Pastikan Anda benar-benar ingin menghapus akun.</p>
                                    </div>
                                    <a href="{{ route('profile.delete') }}"
                                        class="btn btn-danger waves-effect waves-light btn-sm"
                                        onclick="event.preventDefault(); if(confirm('Apakah Anda yakin ingin menghapus akun ini? Tindakan ini tidak dapat dibatalkan.')) { document.getElementById('delete-account-form').submit(); }">
                                        <i class="fi fi-rr-trash me-1"></i> Hapus Akun
                                    </a>
                                    <form id="delete-account-form" action="{{ route('profile.delete') }}" method="POST" class="d-none">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
