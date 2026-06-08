@extends('layouts.app')
@section('title', 'Tambah Unit Kerja')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header border-0 py-3 d-flex justify-content-between align-items-center">
                        <h6 class="card-title mb-0">
                            <i class="fi fi-rr-building me-1"></i> Tambah Unit Kerja
                        </h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('unit-kerja.store') }}" method="POST">
                            @csrf

                            <div class="mb-3">
                                <label for="kode" class="form-label small fw-semibold">Kode Unit <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm @error('kode') is-invalid @enderror"
                                    id="kode" name="kode" value="{{ old('kode') }}"
                                    placeholder="Contoh: FTI, FH, LPPM">
                                <small class="text-muted">Kode singkat, unik, maks. 20 karakter</small>
                                @error('kode')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="nama" class="form-label small fw-semibold">Nama Unit <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm @error('nama') is-invalid @enderror"
                                    id="nama" name="nama" value="{{ old('nama') }}"
                                    placeholder="Contoh: Fakultas Teknologi Informasi">
                                @error('nama')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="deskripsi" class="form-label small fw-semibold">Deskripsi</label>
                                <textarea class="form-control form-control-sm @error('deskripsi') is-invalid @enderror"
                                    id="deskripsi" name="deskripsi" rows="3" placeholder="Keterangan unit kerja (opsional)">{{ old('deskripsi') }}</textarea>
                                @error('deskripsi')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-flex gap-2 pt-3 border-top">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="fi fi-rr-check me-1"></i> Simpan
                                </button>
                                <a href="{{ route('unit-kerja.index') }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="fi fi-rr-arrow-left me-1"></i> Batal
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
