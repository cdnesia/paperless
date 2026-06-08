@extends('layouts.app')
@section('title', 'Detail Permission')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center border-0 pb-2">
                        <h6 class="card-title mb-0">Detail Permission</h6>
                        <div class="d-flex gap-2">
                            <a href="{{ route('permissions.edit', $permission) }}" class="btn btn-primary btn-sm">
                                <i class="fi fi-rr-pencil me-1"></i> Edit
                            </a>
                            <a href="{{ route('permissions.index') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="fi fi-rr-arrow-left me-1"></i> Kembali
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Nama Permission</label>
                            <p class="fw-bold"><span class="badge bg-info">{{ $permission->name }}</span></p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tanggal Dibuat</label>
                            <p class="fw-bold">{{ $permission->created_at->translatedFormat('d F Y H:i') }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Terakhir Diupdate</label>
                            <p class="fw-bold">{{ $permission->updated_at->translatedFormat('d F Y H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
