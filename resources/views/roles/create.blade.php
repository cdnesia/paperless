@extends('layouts.app')
@section('title', 'Tambah Role')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header border-0 pb-2">
                        <h6 class="card-title mb-0">Form Tambah Role</h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('roles.store') }}" method="POST">
                            @csrf

                            <div class="mb-3">
                                <label for="name" class="form-label">Nama Role <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" placeholder="Contoh: editor, moderator" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Pilih Permission <span class="text-danger">*</span></label>
                                <div class="border rounded p-3" style="max-height: 400px; overflow-y: auto;">
                                    @forelse ($permissions as $permission)
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" id="perm_{{ $permission->id }}" name="permissions[]" value="{{ $permission->name }}">
                                            <label class="form-check-label" for="perm_{{ $permission->id }}">
                                                {{ $permission->name }}
                                            </label>
                                        </div>
                                    @empty
                                        <p class="text-muted mb-0">Belum ada permission</p>
                                    @endforelse
                                </div>
                                @error('permissions')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="fi fi-rr-check me-1"></i> Simpan
                                </button>
                                <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary btn-sm">
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
