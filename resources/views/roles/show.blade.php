@extends('layouts.app')
@section('title', 'Detail Role')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center border-0 pb-2">
                        <h6 class="card-title mb-0">Detail Role: <span class="badge bg-primary">{{ $role->name }}</span></h6>
                        <div class="d-flex gap-2">
                            <a href="{{ route('roles.edit', $role) }}" class="btn btn-primary btn-sm">
                                <i class="fi fi-rr-pencil me-1"></i> Edit
                            </a>
                            <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="fi fi-rr-arrow-left me-1"></i> Kembali
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <h6 class="text-muted text-uppercase mb-2">Informasi Role</h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Nama Role</label>
                                    <p class="fw-bold">{{ $role->name }}</p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Tanggal Dibuat</label>
                                    <p class="fw-bold">{{ $role->created_at->translatedFormat('d F Y H:i') }}</p>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div>
                            <h6 class="text-muted text-uppercase mb-3">Permission yang Diberikan</h6>
                            <div class="row">
                                @forelse ($role->permissions as $permission)
                                    <div class="col-md-6 mb-2">
                                        <span class="badge bg-success">{{ $permission->name }}</span>
                                    </div>
                                @empty
                                    <div class="col-12">
                                        <p class="text-muted mb-0"><i class="fi fi-rr-inbox"></i> Tidak ada permission</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
