@extends('layouts.app')
@section('title', 'Detail User')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center border-0 pb-2">
                        <h6 class="card-title mb-0">Detail User</h6>
                        <div class="d-flex gap-2">
                            <a href="{{ route('users.edit', $user) }}" class="btn btn-primary btn-sm">
                                <i class="fi fi-rr-pencil me-1"></i> Edit
                            </a>
                            <a href="{{ route('users.index') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="fi fi-rr-arrow-left me-1"></i> Kembali
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-6 mb-3">
                                <label class="form-label small text-muted fw-semibold">Nama</label>
                                <p class="fw-bold mb-0">{{ $user->name }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label small text-muted fw-semibold">Email</label>
                                <p class="fw-bold mb-0 font-monospace">{{ $user->email }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label small text-muted fw-semibold">Unit Kerja</label>
                                <p class="fw-bold mb-0">{{ $user->unitKerja->nama ?? '-' }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label small text-muted fw-semibold">Telegram Chat ID</label>
                                <p class="fw-bold mb-0">{{ $user->telegram_chat_id ?? '-' }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label small text-muted fw-semibold">Tanggal Dibuat</label>
                                <p class="fw-bold mb-0">{{ $user->created_at->translatedFormat('d F Y H:i') }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label small text-muted fw-semibold">Terakhir Diupdate</label>
                                <p class="fw-bold mb-0">{{ $user->updated_at->translatedFormat('d F Y H:i') }}</p>
                            </div>
                        </div>

                        <hr>

                        <div>
                            <h6 class="text-muted text-uppercase mb-3">Role yang Diberikan</h6>
                            @if ($user->roles->count() > 0)
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach ($user->roles as $role)
                                        <span class="badge bg-success">{{ $role->name }}</span>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-muted mb-0"><i class="fi fi-rr-inbox"></i> Tidak ada role</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
