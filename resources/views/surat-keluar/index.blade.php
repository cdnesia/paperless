@extends('layouts.app')
@section('title', 'Daftar Surat Keluar')
@section('content')
    <div class="container-fluid">
        <div class="app-page-head d-flex mb-2 flex-wrap align-items-center justify-content-between">
            <div class="clearfix">
                <h6 class="app-page-title">
                    <i class="fi fi-rr-paper-plane me-1"></i>
                    Daftar Surat Keluar
                </h6>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="card overflow-hidden">
                    <div class="card-header d-flex gap-3 flex-wrap align-items-center justify-content-between border-0 pb-2">
                        <h6 class="card-title mb-0">Daftar Surat Keluar</h6>
                        <div class="d-flex gap-2 flex-wrap align-items-center">
                            @if(auth()->user()->hasRole('super-admin'))
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="{{ route('surat-keluar.index', ['filter' => 'semua']) }}"
                                    class="btn {{ $filter === 'semua' ? 'btn-primary' : 'btn-outline-primary' }}">
                                    Semua Surat
                                </a>
                                <a href="{{ route('surat-keluar.index', ['filter' => 'sendiri']) }}"
                                    class="btn {{ $filter === 'sendiri' ? 'btn-primary' : 'btn-outline-primary' }}">
                                    Surat Saya
                                </a>
                            </div>
                            @endif
                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                data-bs-target="#modalPilihMetode">
                                <i class="fi fi-rr-plus me-1"></i> Tambah Surat Baru
                            </button>

                            <div id="dt_SuratKeluar_Search"></div>
                        </div>
                    </div>
                    <div class="card-body p-0 pb-2">
                        <table id="dt_SuratKeluar" class="table display data-row-checkbox">
                            <thead class="table-light" style="vertical-align: middle">
                                <tr>
                                    <th style="width: 40px" class="text-center">#</th>
                                    <th>No. Surat</th>
                                    <th>Perihal</th>
                                    <th>Klasifikasi</th>
                                    <th>Tujuan</th>
                                    <th>Status</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($suratKeluars as $surat)
                                    <tr>
                                        <td class="text-center small text-muted">{{ $loop->iteration }}</td>
                                        <td>
                                            <a href="{{ route('surat-keluar.show', $surat) }}" class="fw-bold text-decoration-none">
                                                {{ $surat->nomor_surat ?? '-' }}
                                            </a>
                                        </td>
                                        <td>{{ $surat->perihal }}</td>
                                        <td>
                                            @php
                                                $jenisBadge = [
                                                    'internal' => 'bg-info',
                                                    'eksternal' => 'bg-warning text-dark',
                                                    'broadcast' => 'bg-success',
                                                ];
                                                $jenisLabels = [
                                                    'internal' => 'Internal',
                                                    'eksternal' => 'Eksternal',
                                                    'broadcast' => 'Broadcast',
                                                ];
                                            @endphp
                                            <span
                                                class="badge badge-sm px-2 {{ $jenisBadge[$surat->jenis_surat] ?? 'bg-secondary' }}">
                                                {{ $jenisLabels[$surat->jenis_surat] ?? $surat->jenis_surat }}
                                            </span>
                                        </td>
                                        <td>
                                            @forelse ($surat->penerima as $penerima)
                                                <span class="badge badge-sm px-2 bg-info bg-opacity-25 text-info-emphasis me-1 mb-1">{{ $penerima->name }}</span>
                                            @empty
                                                <span class="text-muted">-</span>
                                            @endforelse
                                        </td>
                                        <td>
                                            @php
                                                $statusBadge = [
                                                    'd' => 'bg-secondary',
                                                    'r' => 'bg-info',
                                                    'a' => 'bg-primary',
                                                    's' => 'bg-success',
                                                    'e' => 'bg-dark',
                                                ];
                                                $statusLabel = [
                                                    'd' => 'Draft',
                                                    'r' => 'Telaah',
                                                    'a' => 'Siap Dikirim',
                                                    's' => 'Terkirim',
                                                    'e' => 'Diarsipkan',
                                                ];
                                            @endphp
                                            <span
                                                class="badge badge-sm px-2 {{ $statusBadge[$surat->status] ?? 'bg-secondary' }}">
                                                {{ $statusLabel[$surat->status] ?? ucfirst($surat->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2 justify-content-center">
                                                @if (!$surat->dibaca)
                                                    <form action="{{ route('surat-keluar.mark-as-read', $surat) }}"
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
                                                    <form action="{{ route('surat-keluar.mark-as-unread', $surat) }}"
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
                                                <a href="{{ route('surat-keluar.show', $surat) }}"
                                                    class="btn btn-white btn-sm btn-shadow btn-icon waves-effect"
                                                    title="Lihat Detail">
                                                    <i class="fi fi-rr-eye"></i>
                                                </a>
                                                @if ($surat->status === 'a')
                                                    <button type="button"
                                                        class="btn btn-success btn-sm btn-shadow btn-icon waves-effect btn-kirim-surat"
                                                        title="Kirim Surat"
                                                        data-url="{{ route('surat-keluar.send', $surat) }}">
                                                        <i class="fi fi-rr-paper-plane"></i>
                                                    </button>
                                                @elseif (!in_array($surat->status, ['s', 'e']))
                                                    <a href="{{ route('surat-keluar.edit', $surat) }}"
                                                        class="btn btn-white btn-sm btn-shadow btn-icon waves-effect"
                                                        title="Edit">
                                                        <i class="fi fi-rr-pencil"></i>
                                                    </a>
                                                @endif
                                                @if (!in_array($surat->status, ['s', 'e']))
                                                    <button type="button"
                                                        class="btn btn-white btn-sm btn-shadow btn-icon btn-hapus"
                                                        title="Hapus" data-id="{{ $surat->id }}"
                                                        data-name="{{ $surat->perihal }}"
                                                        data-url="{{ route('surat-keluar.destroy', $surat) }}">
                                                        <i class="fi fi-rr-trash"></i>
                                                    </button>
                                                @endif
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

{{-- Modal Konfirmasi Kirim Surat --}}
<div class="modal fade" id="modalKirimSurat" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h6 class="modal-title">
                    <i class="fi fi-rr-paper-plane text-success me-1"></i>
                    Konfirmasi Kirim Surat
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">Kirim surat ini ke tujuan?</p>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                    <i class="fi fi-rr-cross me-1"></i> Batal
                </button>
                <button type="button" class="btn btn-success btn-sm" id="btnKonfirmasiKirimIndex">
                    <i class="fi fi-rr-paper-plane me-1"></i> Ya, Kirim
                </button>
            </div>
        </div>
    </div>
</div>

    <div class="modal fade" id="modalPilihMetode" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">
                        <i class="fi fi-rr-file me-2"></i> Buat Surat Baru
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-2 pb-4 px-4">
                    <p class="text-muted small mb-3">Pilih metode pembuatan surat:</p>
                    <div class="row g-3">
                        <div class="col-6">
                            <a href="{{ route('surat-keluar.create', ['metode' => 'upload']) }}"
                                class="card border text-decoration-none h-100" style="transition: all 0.2s;"
                                onmouseover="this.style.borderColor='var(--bs-primary)'; this.style.boxShadow='0 0 0 0.15rem rgba(49,106,255,.2)'"
                                onmouseout="this.style.borderColor=''; this.style.boxShadow=''">
                                <div class="card-body text-center py-4">
                                    <div class="rounded-circle bg-secondary bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-3" style="width:56px; height:56px;">
                                        <i class="fi fi-rr-upload text-secondary fs-4"></i>
                                    </div>
                                    <h6 class="card-title mb-1 small fw-bold">Upload PDF</h6>
                                    <p class="text-muted small mb-0">Upload dokumen PDF<br>yang sudah jadi</p>
                                </div>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="{{ route('surat-keluar.create', ['metode' => 'gdocs']) }}"
                                class="card border text-decoration-none h-100" style="transition: all 0.2s;"
                                onmouseover="this.style.borderColor='var(--bs-primary)'; this.style.boxShadow='0 0 0 0.15rem rgba(49,106,255,.2)'"
                                onmouseout="this.style.borderColor=''; this.style.boxShadow=''">
                                <div class="card-body text-center py-4">
                                    <div class="rounded-circle bg-info bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-3" style="width:56px; height:56px;">
                                        <i class="fi fi-rr-file-edit text-info fs-4"></i>
                                    </div>
                                    <h6 class="card-title mb-1 small fw-bold">Google Docs</h6>
                                    <p class="text-muted small mb-0">Buat & edit online<br>via Google Docs</p>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('css')
    <link rel="stylesheet" href="{{ asset('') }}assets/libs/datatables/datatables.min.css">
    <style>
        #dt_SuratKeluar thead th:nth-child(8),
        #dt_SuratKeluar tbody td:nth-child(8) {
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

        window.dt_SuratKeluar = null;
        var filterStorageKey = 'surat_keluar_filter_' + window.location.pathname;

        function getSavedFilters() {
            try {
                return JSON.parse(sessionStorage.getItem(filterStorageKey)) || {};
            } catch (e) {
                return {};
            }
        }

        function saveFilters() {
            if (!window.dt_SuratKeluar) {
                return;
            }

            sessionStorage.setItem(filterStorageKey, JSON.stringify({
                search: $('#dt_SuratKeluar_Search input[type="search"]').val() || window.dt_SuratKeluar.search()
            }));
        }

        function applySavedFilters() {
            var savedFilters = getSavedFilters();

            if (savedFilters.search) {
                window.dt_SuratKeluar.search(savedFilters.search);
                $('#dt_SuratKeluar_Search input[type="search"]').val(savedFilters.search);
            }
        }

        function clearFilters() {
            $('#dt_SuratKeluar_Search input[type="search"]').val('');
            if (window.dt_SuratKeluar) {
                window.dt_SuratKeluar.search('').draw();
            }
            sessionStorage.removeItem(filterStorageKey);
        }

        if ($('#dt_SuratKeluar').length) {
            window.dt_SuratKeluar = $('#dt_SuratKeluar').DataTable({
                searching: true,
                pageLength: 25,
                select: false,
                lengthChange: false,
                info: true,
                paging: true,
                language: {
                    search: "",
                    searchPlaceholder: 'Pencarian ...',
                    paginate: {
                        previous: "<i class='fi fi-rr-angle-left'></i>",
                        next: "<i class='fi fi-rr-angle-right'></i>",
                        first: "<i class='fi fi-rr-angle-double-left'></i>",
                        last: "<i class='fi fi-rr-angle-double-right'></i>"
                    },
                },
                initComplete: function() {
                    var dtSearch = $('#dt_SuratKeluar_wrapper .dt-search').detach();
                    $('#dt_SuratKeluar_Search').append(dtSearch);
                    $('#dt_SuratKeluar_Search .dt-search').prepend('<i class="fi fi-rr-search"></i>');
                    $('#dt_SuratKeluar_Search .dt-search label').remove();
                    $('#dt_SuratKeluar_wrapper > .row.mt-2.justify-content-between').first().remove();
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
            window.dt_SuratKeluar.draw();
        }

        $(document).on('input', '#dt_SuratKeluar_Search input[type="search"]', function() {
            if (window.dt_SuratKeluar) {
                saveFilters();
            }
        });

        // Konfirmasi Kirim Surat dengan modal
        var $formKirim = null;

        $(document).on('click', '.btn-kirim-surat', function(e) {
            e.preventDefault();
            $formKirim = $('<form>', {
                action: $(this).data('url'),
                method: 'POST',
                class: 'd-none'
            }).append(
                '<input type="hidden" name="_token" value="{{ csrf_token() }}">',
                '<input type="hidden" name="_method" value="PATCH">'
            );
            $('body').append($formKirim);
            $('#modalKirimSurat').modal('show');
        });

        $('#btnKonfirmasiKirimIndex').on('click', function() {
            $('#modalKirimSurat').modal('hide');
            if ($formKirim) {
                $formKirim.submit();
            }
        });
    </script>
@endpush
