@extends('layouts.app')
@section('title', 'Unit Kerja')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card overflow-hidden">
                    <div class="card-header d-flex gap-3 flex-wrap align-items-center justify-content-between border-0 pb-2">
                        <h6 class="card-title mb-0">Daftar Unit Kerja</h6>
                        <div class="d-flex gap-2 flex-wrap align-items-center">
                            <a href="{{ route('unit-kerja.create') }}" class="btn btn-primary btn-sm">
                                <i class="fi fi-rr-plus me-1"></i> Tambah Unit Kerja
                            </a>
                            <div id="dt_UnitKerja_Search"></div>
                        </div>
                    </div>
                    <div class="card-body p-0 pb-2">
                        <table id="dt_UnitKerja" class="table display data-row-checkbox">
                            <thead class="table-light" style="vertical-align: middle">
                                <tr>
                                    <th style="width: 40px" class="text-center">#</th>
                                    <th>Kode</th>
                                    <th>Nama Unit</th>
                                    <th>Deskripsi</th>
                                    <th class="text-center" style="width: 100px">Pengguna</th>
                                    <th class="text-center" style="width: 120px">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($units as $unit)
                                    <tr>
                                        <td class="text-center small text-muted">{{ $loop->iteration }}</td>
                                        <td><code>{{ $unit->kode }}</code></td>
                                        <td class="fw-semibold">{{ $unit->nama }}</td>
                                        <td class="small text-muted">{{ \Illuminate\Support\Str::limit($unit->deskripsi, 60) ?? '-' }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-secondary">{{ $unit->users_count }}</span>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex gap-2 justify-content-center">
                                                <a href="{{ route('unit-kerja.edit', $unit) }}"
                                                    class="btn btn-white btn-sm btn-shadow btn-icon waves-effect"
                                                    title="Edit">
                                                    <i class="fi fi-rr-pencil"></i>
                                                </a>
                                                <button type="button"
                                                    class="btn btn-white btn-sm btn-shadow btn-icon btn-hapus"
                                                    title="Hapus" data-id="{{ $unit->id }}"
                                                    data-name="{{ $unit->nama }}"
                                                    data-url="{{ route('unit-kerja.destroy', $unit) }}">
                                                    <i class="fi fi-rr-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('partials.delete-modal')
@endsection

@push('css')
    <link rel="stylesheet" href="{{ asset('') }}assets/libs/datatables/datatables.min.css">
@endpush

@push('js')
    <script src="{{ asset('') }}assets/libs/datatables/datatables.min.js"></script>
    <script>
        @if (session('success'))
            showToast('success', '{{ session('success') }}');
        @endif
        @if (session('error'))
            showToast('error', '{{ session('error') }}');
        @endif

        if ($('#dt_UnitKerja').length) {
            window.dt = $('#dt_UnitKerja').DataTable({
                searching: true,
                pageLength: 25,
                lengthChange: false,
                info: true,
                paging: true,
                language: {
                    search: "", searchPlaceholder: 'Pencarian ...',
                    emptyTable: 'Belum ada unit kerja',
                    zeroRecords: 'Tidak ada data ditemukan',
                    paginate: {
                        previous: "<i class='fi fi-rr-angle-left'></i>",
                        next: "<i class='fi fi-rr-angle-right'></i>",
                        first: "<i class='fi fi-rr-angle-double-left'></i>",
                        last: "<i class='fi fi-rr-angle-double-right'></i>"
                    },
                },
                initComplete: function() {
                    var dtSearch = $('#dt_UnitKerja_wrapper .dt-search').detach();
                    $('#dt_UnitKerja_Search').append(dtSearch);
                    $('#dt_UnitKerja_Search .dt-search').prepend('<i class="fi fi-rr-search"></i>');
                    $('#dt_UnitKerja_Search .dt-search label').remove();
                    $('#dt_UnitKerja_wrapper > .row.mt-2.justify-content-between').first().remove();
                },
                columnDefs: [{ targets: 0, orderable: false }, { targets: -1, orderable: false }]
            });
        }
    </script>
@endpush
