@extends('layouts.app')
@section('title', 'Disposisi Masuk')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card overflow-hidden">
                    <div class="card-header d-flex gap-3 flex-wrap align-items-center justify-content-between border-0 pb-2">
                        <h6 class="card-title mb-0">Disposisi Masuk</h6>
                        <div class="d-flex gap-2 flex-wrap align-items-center">
                            <div class="form-check form-switch me-2">
                                <input class="form-check-input" type="checkbox" id="toggleSemua"
                                    {{ $semua ? 'checked' : '' }}>
                                <label class="form-check-label small" for="toggleSemua">
                                    Tampilkan yang sudah dibaca
                                </label>
                            </div>

                            <div id="dt_DisposisiMasuk_Search"></div>

                            <button type="button" class="btn btn-outline-secondary btn-sm" id="resetFilter"
                                title="Hapus semua filter">
                                <i class="fi fi-rr-refresh"></i> Reset
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0 pb-2">
                        <table id="dt_DisposisiMasuk" class="table display data-row-checkbox">
                            <thead class="table-light" style="vertical-align: middle">
                                <tr>
                                    <th style="width: 30px !important" class="pe-0">
                                        <div class="form-check">
                                            <input class="form-check-input" data-row-checkbox type="checkbox">
                                        </div>
                                    </th>
                                    <th>Nomor Surat</th>
                                    <th>Perihal</th>
                                    <th>Pengirim</th>
                                    <th>Keterangan</th>
                                    <th>Status</th>
                                    <th>Status Baca Saya</th>
                                    <th>Tanggal</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($disposisi as $item)
                                    @php $surat = $item->suratKeluar; @endphp
                                    <tr class="{{ !$item->dibaca ? 'table-warning' : '' }}">
                                        <td>
                                            <div class="form-check">
                                                <input class="form-check-input row-checkbox" data-checkbox type="checkbox"
                                                    value="{{ $item->id }}">
                                            </div>
                                        </td>
                                        <td>
                                            <a href="{{ route('disposisi-masuk.show', $item) }}" class="fw-bold text-decoration-none">
                                                {{ $surat->nomor_surat ?? '-' }}
                                            </a>
                                        </td>
                                        <td>{{ $surat->perihal ?? '-' }}</td>
                                        <td>
                                            <span class="badge bg-secondary bg-opacity-10 text-dark">
                                                <i class="fi fi-rr-arrow-down me-1"></i>
                                                {{ $item->pengirim->name ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td>{{ Str::limit($item->keterangan, 40) ?? '-' }}</td>
                                        <td>
                                            <span class="badge badge-sm bg-{{ $item->status === 'diteruskan' ? 'info' : ($item->status === 'diterima' || $item->status === 'disposisi' ? 'success' : 'danger') }}">
                                                {{ ucfirst($item->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if (!$item->dibaca)
                                                <span class="badge badge-sm bg-warning text-dark">Belum Dibaca</span>
                                            @else
                                                <span class="badge badge-sm bg-success">Sudah Dibaca</span>
                                            @endif
                                        </td>
                                        <td>{{ $item->created_at->translatedFormat('d F Y') }}</td>
                                        <td class="text-center">
                                            <div class="d-flex gap-2 justify-content-center">
                                                @if (!$item->dibaca)
                                                    <form action="{{ route('disposisi-masuk.mark-as-read', $item) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="btn btn-white btn-sm btn-shadow btn-icon waves-effect" title="Tandai sudah dibaca">
                                                            <i class="fi fi-rr-envelope"></i>
                                                            <span class="position-absolute top-0 end-0 p-1 mt-1 me-1 bg-danger border border-3 border-light rounded-circle">
                                                                <span class="visually-hidden">New</span>
                                                            </span>
                                                        </button>
                                                    </form>
                                                @else
                                                    <form action="{{ route('disposisi-masuk.mark-as-unread', $item) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="btn btn-white btn-sm btn-shadow btn-icon waves-effect" title="Tandai belum dibaca">
                                                            <i class="fi fi-rr-envelope-open"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                                <a href="{{ route('disposisi-masuk.show', $item) }}"
                                                    class="btn btn-white btn-sm btn-shadow btn-icon waves-effect" title="Lihat Detail">
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
        #dt_DisposisiMasuk thead th:nth-child(9),
        #dt_DisposisiMasuk tbody td:nth-child(9) {
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

        window.dt_DisposisiMasuk = null;
        var filterStorageKey = 'disposisi_masuk_filter_' + window.location.pathname;

        function getSavedFilters() {
            try { return JSON.parse(sessionStorage.getItem(filterStorageKey)) || {}; } catch (e) { return {}; }
        }

        function saveFilters() {
            if (!window.dt_DisposisiMasuk) return;
            sessionStorage.setItem(filterStorageKey, JSON.stringify({
                search: $('#dt_DisposisiMasuk_Search input[type="search"]').val() || window.dt_DisposisiMasuk.search()
            }));
        }

        function applySavedFilters() {
            var saved = getSavedFilters();
            if (saved.search) {
                window.dt_DisposisiMasuk.search(saved.search);
                $('#dt_DisposisiMasuk_Search input[type="search"]').val(saved.search);
            }
        }

        function clearFilters() {
            $('#dt_DisposisiMasuk_Search input[type="search"]').val('');
            if (window.dt_DisposisiMasuk) window.dt_DisposisiMasuk.search('').draw();
            sessionStorage.removeItem(filterStorageKey);
        }

        if ($('#dt_DisposisiMasuk').length) {
            window.dt_DisposisiMasuk = $('#dt_DisposisiMasuk').DataTable({
                searching: true,
                pageLength: 25,
                lengthChange: false,
                info: true,
                paging: true,
                language: {
                    search: "",
                    searchPlaceholder: 'Pencarian ...',
                    emptyTable: 'Tidak ada disposisi masuk',
                    zeroRecords: 'Tidak ada data ditemukan',
                    paginate: {
                        previous: "<i class='fi fi-rr-angle-left'></i>",
                        next: "<i class='fi fi-rr-angle-right'></i>",
                        first: "<i class='fi fi-rr-angle-double-left'></i>",
                        last: "<i class='fi fi-rr-angle-double-right'></i>"
                    },
                },
                initComplete: function() {
                    var dtSearch = $('#dt_DisposisiMasuk_wrapper .dt-search').detach();
                    $('#dt_DisposisiMasuk_Search').append(dtSearch);
                    $('#dt_DisposisiMasuk_Search .dt-search').prepend('<i class="fi fi-rr-search"></i>');
                    $('#dt_DisposisiMasuk_Search .dt-search label').remove();
                    $('#dt_DisposisiMasuk_wrapper > .row.mt-2.justify-content-between').first().remove();
                },
                columnDefs: [{
                    targets: 0,
                    orderable: false,
                }, {
                    targets: -1,
                    orderable: false,
                }]
            });

            applySavedFilters();
            window.dt_DisposisiMasuk.draw();
        }

        $(document).on('input', '#dt_DisposisiMasuk_Search input[type="search"]', function() {
            if (window.dt_DisposisiMasuk) saveFilters();
        });

        $('#resetFilter').click(function() { clearFilters(); });

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
