@extends('layouts.app')
@section('title', 'Daftar Permission')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card overflow-hidden">
                    <div class="card-header d-flex gap-3 flex-wrap align-items-center justify-content-between border-0 pb-2">
                        <h6 class="card-title mb-0">Daftar Permission</h6>
                        <div class="d-flex gap-2 flex-wrap align-items-center">
                            <a href="{{ route('permissions.create') }}" class="btn btn-primary btn-sm">
                                <i class="fi fi-rr-plus me-1"></i> Tambah Permission
                            </a>
                            <form action="{{ route('permissions.sync') }}" method="POST" class="d-inline" id="form-sync">
                                @csrf
                                <button type="submit" class="btn btn-success btn-sm" id="btn-sync">
                                    <i class="fi fi-rr-sync me-1"></i> Sinkron
                                </button>
                            </form>

                            <div id="dt_PermissionList_Search"></div>

                            <button type="button" class="btn btn-outline-secondary btn-sm" id="resetFilter"
                                title="Hapus semua filter">
                                <i class="fi fi-rr-refresh"></i> Reset
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0 pb-2">
                        <table id="dt_PermissionList" class="table display data-row-checkbox">
                            <thead class="table-light" style="vertical-align: middle">
                                <tr>
                                    <th style="width: 30px !important" class="pe-0">
                                        <div class="form-check">
                                            <input class="form-check-input" data-row-checkbox type="checkbox">
                                        </div>
                                    </th>
                                    <th>Nama Permission</th>
                                    <th>Tanggal Dibuat</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($permissions as $permission)
                                    <tr>
                                        <td>
                                            <div class="form-check">
                                                <input class="form-check-input row-checkbox" data-checkbox type="checkbox"
                                                    value="{{ $permission->id }}">
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge badge-sm px-2 text-bg-success me-1">{{ $permission->name }}</span>
                                        </td>
                                        <td>{{ $permission->created_at->translatedFormat('d F Y H:i') }}</td>
                                        <td class="text-center">
                                            <div class="d-flex gap-2 justify-content-center">
                                                <a href="{{ route('permissions.show', $permission) }}" class="btn btn-white btn-sm btn-shadow btn-icon waves-effect" title="Lihat Detail">
                                                    <i class="fi fi-rr-eye"></i>
                                                </a>
                                                <a href="{{ route('permissions.edit', $permission) }}" class="btn btn-white btn-sm btn-shadow btn-icon waves-effect" title="Edit">
                                                    <i class="fi fi-rr-pencil"></i>
                                                </a>
                                                <button type="button" class="btn btn-white btn-sm btn-shadow btn-icon btn-hapus" title="Hapus" data-id="{{ $permission->id }}" data-name="{{ $permission->name }}" data-url="{{ route('permissions.destroy', $permission) }}">
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

        window.dt_PermissionList = null;
        var filterStorageKey = 'permissions_filter_' + window.location.pathname;

        function getSavedFilters() {
            try {
                return JSON.parse(sessionStorage.getItem(filterStorageKey)) || {};
            } catch (e) {
                return {};
            }
        }

        function saveFilters() {
            if (!window.dt_PermissionList) {
                return;
            }

            sessionStorage.setItem(filterStorageKey, JSON.stringify({
                search: $('#dt_PermissionList_Search input[type="search"]').val() || window.dt_PermissionList.search()
            }));
        }

        function applySavedFilters() {
            var savedFilters = getSavedFilters();

            if (savedFilters.search) {
                window.dt_PermissionList.search(savedFilters.search);
                $('#dt_PermissionList_Search input[type="search"]').val(savedFilters.search);
            }
        }

        function clearFilters() {
            $('#dt_PermissionList_Search input[type="search"]').val('');
            if (window.dt_PermissionList) {
                window.dt_PermissionList.search('').draw();
            }
            sessionStorage.removeItem(filterStorageKey);
        }

        if ($('#dt_PermissionList').length) {
            window.dt_PermissionList = $('#dt_PermissionList').DataTable({
                searching: true,
                pageLength: 10,
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
                    var dtSearch = $('#dt_PermissionList_wrapper .dt-search').detach();
                    $('#dt_PermissionList_Search').append(dtSearch);
                    $('#dt_PermissionList_Search .dt-search').prepend('<i class="fi fi-rr-search"></i>');
                    $('#dt_PermissionList_Search .dt-search label').remove();
                    $('#dt_PermissionList_wrapper > .row.mt-2.justify-content-between').first().remove();
                    $('#dt_PermissionList thead th:last-child, #dt_PermissionList tbody td:last-child').addClass('text-center');
                },
                columnDefs: [{
                    targets: 0,
                    orderable: false,
                }, {
                    targets: 3,
                    orderable: false,
                }]
            });

            applySavedFilters();
            window.dt_PermissionList.draw();
        }

        $(document).on('input', '#dt_PermissionList_Search input[type="search"]', function() {
            if (window.dt_PermissionList) {
                window.dt_PermissionList.search($(this).val()).draw();
            }

            saveFilters();
        });

        $('#resetFilter').on('click', function() {
            clearFilters();
        });

        var selectedIds = [];

        window.dt_PermissionList.on('draw', function() {
            $('.row-checkbox').each(function() {
                var id = $(this).val();
                if (selectedIds.indexOf(id) !== -1) {
                    $(this).prop('checked', true);
                }
            });
            updateSelectAllCheckbox();
        });

        $('[data-row-checkbox]').on('change', function() {
            var isChecked = $(this).prop('checked');
            $('.row-checkbox:visible').each(function() {
                var id = $(this).val();
                $(this).prop('checked', isChecked);

                if (isChecked) {
                    if (selectedIds.indexOf(id) === -1) {
                        selectedIds.push(id);
                    }
                } else {
                    var index = selectedIds.indexOf(id);
                    if (index !== -1) {
                        selectedIds.splice(index, 1);
                    }
                }
            });
            updateSelectedCount();
        });

        $(document).on('change', '.row-checkbox', function() {
            var id = $(this).val();
            var isChecked = $(this).prop('checked');

            if (isChecked) {
                if (selectedIds.indexOf(id) === -1) {
                    selectedIds.push(id);
                }
            } else {
                var index = selectedIds.indexOf(id);
                if (index !== -1) {
                    selectedIds.splice(index, 1);
                }
            }

            updateSelectedCount();
            updateSelectAllCheckbox();
        });

        function updateSelectAllCheckbox() {
            var totalVisible = $('.row-checkbox:visible').length;
            var totalChecked = $('.row-checkbox:visible:checked').length;
            $('[data-row-checkbox]').prop('checked', totalVisible === totalChecked && totalVisible > 0);
        }

        function updateSelectedCount() {
            // Add action for bulk operations here if needed
        }

        // ─── Sync button loading ────────────────────────────
        $('#form-sync').on('submit', function() {
            $('#btn-sync').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyinkron...');
        });
    </script>
@endpush
