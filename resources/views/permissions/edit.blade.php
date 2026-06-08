@extends('layouts.app')
@section('title', 'Edit Permission')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header border-0 pb-2">
                        <h6 class="card-title mb-0">Form Edit Permission</h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('permissions.update', $permission) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label for="name" class="form-label">Nama Permission <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $permission->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Gunakan format: noun-action (contoh: user-create, post-delete)</small>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="fi fi-rr-check me-1"></i> Update
                                </button>
                                <a href="{{ route('permissions.show', $permission) }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="fi fi-rr-arrow-left me-1"></i> Kembali
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
