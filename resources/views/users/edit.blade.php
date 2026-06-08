@extends('layouts.app')
@section('title', 'Edit User')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header border-0 py-3 d-flex justify-content-between align-items-center">
                        <h6 class="card-title mb-0">
                            <i class="fi fi-rr-user-pen me-1"></i> Edit Pengguna
                        </h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('users.update', $user) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label for="name" class="form-label small fw-semibold">Nama <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-sm @error('name') is-invalid @enderror"
                                        id="name" name="name" value="{{ old('name', $user->name) }}" placeholder="Nama lengkap" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="email" class="form-label small fw-semibold">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control form-control-sm @error('email') is-invalid @enderror"
                                        id="email" name="email" value="{{ old('email', $user->email) }}" placeholder="email@kampus.ac.id" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="password" class="form-label small fw-semibold">Password Baru <small class="text-muted fw-normal">(opsional)</small></label>
                                    <input type="password" class="form-control form-control-sm @error('password') is-invalid @enderror"
                                        id="password" name="password">
                                    <small class="text-muted">Kosongkan jika tidak ingin mengubah. Minimal 8 karakter.</small>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="password_confirmation" class="form-label small fw-semibold">Konfirmasi Password</label>
                                    <input type="password" class="form-control form-control-sm"
                                        id="password_confirmation" name="password_confirmation">
                                </div>

                                <div class="col-md-6">
                                    <label for="unit_kerja_id" class="form-label small fw-semibold">Unit Kerja <span class="text-danger">*</span></label>
                                    <select class="form-select form-select-sm select2 @error('unit_kerja_id') is-invalid @enderror"
                                        id="unit_kerja_id" name="unit_kerja_id" required>
                                        <option value="">— Pilih Unit Kerja —</option>
                                        @foreach ($unitKerjas as $unit)
                                            <option value="{{ $unit->id }}" {{ old('unit_kerja_id', $user->unit_kerja_id) == $unit->id ? 'selected' : '' }}>
                                                {{ $unit->nama }} ({{ $unit->kode }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('unit_kerja_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="telegram_chat_id" class="form-label small fw-semibold">Telegram Chat ID</label>
                                    <input type="text" class="form-control form-control-sm @error('telegram_chat_id') is-invalid @enderror"
                                        id="telegram_chat_id" name="telegram_chat_id" value="{{ old('telegram_chat_id', $user->telegram_chat_id) }}"
                                        placeholder="Contoh: 1234567890">
                                    <small class="text-muted">ID chat Telegram untuk notifikasi otomatis</small>
                                    @error('telegram_chat_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="roles_tagify" class="form-label small fw-semibold">Role</label>
                                    <input name="roles" id="roles_tagify" type="text"
                                        class="form-control form-control-sm @error('roles') is-invalid @enderror"
                                        placeholder="Ketik nama role...">
                                    <small class="text-muted">Ketik untuk mencari. Dapat memilih lebih dari satu role.</small>
                                    @error('roles')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="d-flex gap-2 pt-3 border-top">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="fi fi-rr-check me-1"></i> Update
                                </button>
                                <a href="{{ route('users.show', $user) }}" class="btn btn-outline-secondary btn-sm">
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

@push('css')
    <link rel="stylesheet" href="{{ asset('') }}assets/libs/tagify/tagify.css">
@endpush

@push('js')
    <script src="{{ asset('') }}assets/libs/tagify/tagify.js"></script>
    <script>
        var inputRoles = document.querySelector('input[name="roles"]');

        var tagifyRoles = new Tagify(inputRoles, {
            enforceWhitelist: true,
            whitelist: @json($rolesWhitelist),
            tagTextProp: 'name',
            dropdown: {
                closeOnSelect: false,
                enabled: 0,
                classname: 'roles-list',
                searchKeys: ['name']
            },
            templates: {
                dropdownItem: function(tagData) {
                    var cls = tagData.class || '';
                    var attrs = this.getAttributes(tagData);
                    return '<div ' + attrs + ' class="tagify__dropdown__item ' + cls + '" tabindex="0" role="option">' +
                        '<strong>' + (tagData.name || '') + '</strong></div>';
                }
            }
        });

        // Pre-fill existing roles
        @php
            $existingTags = $user->roles->pluck('name')->map(fn($n) => ['value' => $n, 'name' => $n])->values()->toArray();
        @endphp
        var existingTags = @json($existingTags);
        if (existingTags.length > 0) {
            tagifyRoles.addTags(existingTags);
        }
    </script>
@endpush
