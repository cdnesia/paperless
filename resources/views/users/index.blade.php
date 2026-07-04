@extends('layouts.app')
@section('title', 'Daftar User')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card overflow-hidden">
                    <div class="card-header d-flex gap-3 flex-wrap align-items-center justify-content-between border-0 pb-2">
                        <h6 class="card-title mb-0">Daftar User</h6>
                        <div class="d-flex gap-2 flex-wrap align-items-center">
                            <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm">
                                <i class="fi fi-rr-plus me-1"></i> Tambah User
                            </a>

                            <div id="dt_UserList_Search"></div>
                        </div>
                    </div>
                    <div class="card-body p-0 pb-2">
                        <table id="dt_UserList" class="table display data-row-checkbox">
                            <thead class="table-light" style="vertical-align: middle">
                                <tr>
                                    <th style="width: 40px" class="text-center">#</th>
                                    <th>Nama</th>
                                    <th>Email</th>
                                    <th>Unit Kerja</th>
                                    <th>Role</th>
                                    <th>Telegram</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($users as $user)
                                    <tr>
                                        <td class="text-center small text-muted">{{ $loop->iteration }}</td>
                                        <td>
                                            <strong>{{ $user->name }}</strong>
                                        </td>
                                        <td>
                                            <small class="text-muted font-monospace">{{ $user->email }}</small>
                                        </td>
                                        <td>
                                            {{ $user->unitKerja->nama ?? '-' }}
                                        </td>
                                        <td>
                                            @forelse ($user->roles as $role)
                                                <span class="badge badge-sm px-2 text-bg-success me-1">
                                                    {{ $role->name }}
                                                </span>
                                            @empty
                                                <span class="text-muted">-</span>
                                            @endforelse
                                        </td>
                                        <td>
                                            @if ($user->telegram_chat_id)
                                                <div class="d-flex align-items-center gap-2">
                                                    <code class="small">{{ $user->telegram_chat_id }}</code>
                                                    <button type="button"
                                                        class="btn btn-sm btn-outline-telegram btn-test-telegram"
                                                        title="Test Kirim Telegram"
                                                        data-user-id="{{ $user->id }}"
                                                        data-user-name="{{ $user->name }}">
                                                        <i class="fi fi-brands-telegram"></i>
                                                    </button>
                                                </div>
                                            @else
                                                <span class="text-muted small">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2 justify-content-center">
                                                <a href="{{ route('users.show', $user) }}"
                                                    class="btn btn-white btn-sm btn-shadow btn-icon waves-effect"
                                                    title="Lihat Detail">
                                                    <i class="fi fi-rr-eye"></i>
                                                </a>
                                                <a href="{{ route('users.edit', $user) }}"
                                                    class="btn btn-white btn-sm btn-shadow btn-icon waves-effect"
                                                    title="Edit">
                                                    <i class="fi fi-rr-pencil"></i>
                                                </a>
                                                @if ($user->id !== auth()->id())
                                                    <button type="button"
                                                        class="btn btn-white btn-sm btn-shadow btn-icon btn-hapus"
                                                        title="Hapus" data-id="{{ $user->id }}"
                                                        data-name="{{ $user->name }}"
                                                        data-url="{{ route('users.destroy', $user) }}">
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

@endsection

@push('css')
    <link rel="stylesheet" href="{{ asset('') }}assets/libs/datatables/datatables.min.css">
    <style>
        #dt_UserList thead th:nth-child(7),
        #dt_UserList tbody td:nth-child(7) {
            text-align: center !important;
        }

        .btn-outline-telegram {
            color: #24A1DE;
            border-color: #24A1DE;
        }

        .btn-outline-telegram:hover {
            color: #fff;
            background-color: #24A1DE;
            border-color: #24A1DE;
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

        window.dt_UserList = null;
        var filterStorageKey = 'users_filter_' + window.location.pathname;

        function getSavedFilters() {
            try {
                return JSON.parse(sessionStorage.getItem(filterStorageKey)) || {};
            } catch (e) {
                return {};
            }
        }

        function saveFilters() {
            if (!window.dt_UserList) {
                return;
            }

            sessionStorage.setItem(filterStorageKey, JSON.stringify({
                search: $('#dt_UserList_Search input[type="search"]').val() || window.dt_UserList.search()
            }));
        }

        function applySavedFilters() {
            var savedFilters = getSavedFilters();

            if (savedFilters.search) {
                window.dt_UserList.search(savedFilters.search);
                $('#dt_UserList_Search input[type="search"]').val(savedFilters.search);
            }
        }

        function clearFilters() {
            $('#dt_UserList_Search input[type="search"]').val('');
            if (window.dt_UserList) {
                window.dt_UserList.search('').draw();
            }
            sessionStorage.removeItem(filterStorageKey);
        }

        if ($('#dt_UserList').length) {
            window.dt_UserList = $('#dt_UserList').DataTable({
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
                    var dtSearch = $('#dt_UserList_wrapper .dt-search').detach();
                    $('#dt_UserList_Search').append(dtSearch);
                    $('#dt_UserList_Search .dt-search').prepend('<i class="fi fi-rr-search"></i>');
                    $('#dt_UserList_Search .dt-search label').remove();
                    $('#dt_UserList_wrapper > .row.mt-2.justify-content-between').first().remove();
                    $('#dt_UserList thead th:last-child, #dt_UserList tbody td:last-child').addClass('text-center');
                },
                columnDefs: [{
                    targets: 0,
                    orderable: false,
                }, {
                    targets: 4,
                    orderable: false,
                }, {
                    targets: 5,
                    orderable: false,
                }]
            });

            applySavedFilters();
            window.dt_UserList.draw();
        }

        $(document).on('input', '#dt_UserList_Search input[type="search"]', function() {
            if (window.dt_UserList) {
                window.dt_UserList.search($(this).val()).draw();
            }

            saveFilters();
        });

        $('#resetFilter').on('click', function() {
            clearFilters();
        });

        var selectedIds = [];

        window.dt_UserList.on('draw', function() {
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

        // --- Test Telegram ---
        $(document).on('click', '.btn-test-telegram', function() {
            var btn = $(this);
            var userId = btn.data('user-id');
            var userName = btn.data('user-name');
            var icon = btn.find('i');

            btn.prop('disabled', true);
            icon.removeClass('fi-brands-telegram').addClass('fi fi-rr-spinner fa-spin');

            $.ajax({
                url: '/users/' + userId + '/test-telegram',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(res) {
                    showToast('success', res.message);
                },
                error: function(xhr) {
                    var msg = xhr.responseJSON?.message || 'Gagal mengirim test Telegram.';
                    showToast('error', msg);
                },
                complete: function() {
                    btn.prop('disabled', false);
                    icon.removeClass('fi-rr-spinner fa-spin').addClass('fi-brands-telegram');
                }
            });
        });
    </script>
@endpush
