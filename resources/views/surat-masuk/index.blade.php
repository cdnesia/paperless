@extends('layouts.app')
@section('title', 'Surat Masuk')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card overflow-hidden">
                    <div class="card-header d-flex gap-3 flex-wrap align-items-center justify-content-between border-0 pb-2">
                        <h6 class="card-title mb-0">Surat Masuk</h6>
                        <div class="d-flex gap-2 flex-wrap align-items-center">
                            <div class="form-check form-switch me-2">
                                <input class="form-check-input" type="checkbox" id="toggleSemua"
                                    {{ $semua ? 'checked' : '' }}>
                                <label class="form-check-label small" for="toggleSemua">
                                    Tampilkan yang sudah dibaca
                                </label>
                            </div>

                            <div id="dt_SuratMasuk_Search"></div>
                        </div>
                    </div>
                    <div class="card-body p-0 pb-2">
                        <table id="dt_SuratMasuk" class="table display data-row-checkbox">
                            <thead class="table-light" style="vertical-align: middle">
                                <tr>
                                    <th style="width: 30px !important" class="pe-0">
                                        <div class="form-check">
                                            <input class="form-check-input" data-row-checkbox type="checkbox">
                                        </div>
                                    </th>
                                    <th>Nomor Surat</th>
                                    <th>Perihal</th>
                                    <th>Asal / Pengirim</th>
                                    <th>Status Baca Saya</th>
                                    <th>Tanggal</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($suratMasuk as $surat)
                                    <tr class="{{ !$surat->dibaca ? 'table-warning' : '' }}">
                                        <td>
                                            <div class="form-check">
                                                <input class="form-check-input row-checkbox" data-checkbox type="checkbox"
                                                    value="{{ $surat->surat_id }}">
                                            </div>
                                        </td>
                                        <td>
                                            <a href="{{ $surat->show_route }}" class="fw-bold text-decoration-none">
                                                {{ $surat->nomor_surat ?? '-' }}
                                            </a>
                                        </td>
                                        <td>{{ $surat->perihal }}</td>
                                        <td>
                                            <span class="badge badge-lg bg-secondary bg-opacity-10 text-dark">
                                                <i class="fi fi-tr-member-list me-1"></i>
                                                {{ $surat->dari_user }}
                                            </span>
                                        </td>
                                        <td>
                                            @if (!$surat->dibaca)
                                                <span class="badge badge-sm bg-warning text-dark">Belum Dibaca</span>
                                            @else
                                                <span class="badge badge-sm bg-success">Sudah Dibaca</span>
                                            @endif
                                        </td>
                                        <td>{{ $surat->tanggal ? \Carbon\Carbon::parse($surat->tanggal)->translatedFormat('d F Y') : ($surat->created_at ? \Carbon\Carbon::parse($surat->created_at)->translatedFormat('d F Y') : '-') }}
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2 justify-content-center">
                                                @if (!$surat->dibaca)
                                                    <form
                                                        action="{{ route('surat-masuk.mark-as-read', $surat->surat_id) }}"
                                                        method="POST" class="d-inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit"
                                                            class="btn btn-white btn-sm btn-shadow btn-icon waves-effect"
                                                            title="Tandai sudah dibaca">
                                                            <i class="fi fi-rr-envelope"></i>
                                                            <span
                                                                class="position-absolute top-0 end-0 p-1 mt-1 me-1 bg-danger border border-3 border-light rounded-circle">
                                                                <span class="visually-hidden">New alerts</span>
                                                            </span>
                                                        </button>
                                                    </form>
                                                @else
                                                    <form
                                                        action="{{ route('surat-masuk.mark-as-unread', $surat->surat_id) }}"
                                                        method="POST" class="d-inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit"
                                                            class="btn btn-white btn-sm btn-shadow btn-icon waves-effect"
                                                            title="Tandai belum dibaca">
                                                            <i class="fi fi-rr-envelope-open"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                                <a href="{{ $surat->show_route }}"
                                                    class="btn btn-white btn-sm btn-shadow btn-icon waves-effect"
                                                    title="Lihat Detail">
                                                    <i class="fi fi-rr-eye"></i>
                                                </a>
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
@endsection

@push('css')
    <link rel="stylesheet" href="{{ asset('') }}assets/libs/datatables/datatables.min.css">
    <style>
        #dt_SuratMasuk thead th:nth-child(8),
        #dt_SuratMasuk tbody td:nth-child(8) {
            text-align: center !important;
        }
    </style>
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

        window.dt_SuratMasuk = null;
        var filterStorageKey = 'surat_masuk_filter_' + window.location.pathname;

        function getSavedFilters() {
            try {
                return JSON.parse(sessionStorage.getItem(filterStorageKey)) || {};
            } catch (e) {
                return {};
            }
        }

        function saveFilters() {
            if (!window.dt_SuratMasuk) return;
            sessionStorage.setItem(filterStorageKey, JSON.stringify({
                search: $('#dt_SuratMasuk_Search input[type="search"]').val() || window.dt_SuratMasuk.search()
            }));
        }

        function applySavedFilters() {
            var savedFilters = getSavedFilters();
            if (savedFilters.search) {
                window.dt_SuratMasuk.search(savedFilters.search);
                $('#dt_SuratMasuk_Search input[type="search"]').val(savedFilters.search);
            }
        }

        function clearFilters() {
            $('#dt_SuratMasuk_Search input[type="search"]').val('');
            if (window.dt_SuratMasuk) {
                window.dt_SuratMasuk.search('').draw();
            }
            sessionStorage.removeItem(filterStorageKey);
        }

        if ($('#dt_SuratMasuk').length) {
            window.dt_SuratMasuk = $('#dt_SuratMasuk').DataTable({
                searching: true,
                pageLength: 25,
                select: false,
                lengthChange: false,
                info: true,
                paging: true,
                language: {
                    search: "",
                    searchPlaceholder: 'Pencarian ...',
                    emptyTable: 'Tidak ada surat masuk',
                    zeroRecords: 'Tidak ada data ditemukan',
                    paginate: {
                        previous: "<i class='fi fi-rr-angle-left'></i>",
                        next: "<i class='fi fi-rr-angle-right'></i>",
                        first: "<i class='fi fi-rr-angle-double-left'></i>",
                        last: "<i class='fi fi-rr-angle-double-right'></i>"
                    },
                },
                initComplete: function() {
                    var dtSearch = $('#dt_SuratMasuk_wrapper .dt-search').detach();
                    $('#dt_SuratMasuk_Search').append(dtSearch);
                    $('#dt_SuratMasuk_Search .dt-search').prepend('<i class="fi fi-rr-search"></i>');
                    $('#dt_SuratMasuk_Search .dt-search label').remove();
                    $('#dt_SuratMasuk_wrapper > .row.mt-2.justify-content-between').first().remove();
                },
                columnDefs: [{
                    targets: 0,
                    orderable: false,
                }, {
                    targets: 6,
                    orderable: false,
                }]
            });

            applySavedFilters();
            window.dt_SuratMasuk.draw();
        }

        $(document).on('input', '#dt_SuratMasuk_Search input[type="search"]', function() {
            if (window.dt_SuratMasuk) saveFilters();
        });

        // Toggle tampilkan semua / hanya belum dibaca
        document.getElementById('toggleSemua')?.addEventListener('change', function() {
            const url = new URL(window.location.href);
            if (this.checked) {
                url.searchParams.set('semua', '1');
            } else {
                url.searchParams.delete('semua');
            }
            window.location.href = url.toString();
        });
    </script>
@endpush
