@extends('layouts.app')
@section('title', 'Data Mahasiswa Baru')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card overflow-hidden">
                    <div class="card-header d-flex gap-3 flex-wrap align-items-center justify-content-between border-0 pb-2">
                        <h6 class="card-title mb-0">Data Mahasiswa Baru</h6>
                        <div class="d-flex gap-2 flex-wrap align-items-center">
                            <select class="form-select form-select-sm select2" id="filterTahun" style="width: 200px;">
                                <option value="">Semua Tahun</option>
                                @foreach ($tahun_gelombang as $tahun)
                                    <option value="{{ $tahun }}">{{ $tahun }}</option>
                                @endforeach
                            </select>

                            <select class="form-select form-select-sm select2" id="filterProdi" style="width: 200px;">
                                <option value="">Semua Prodi</option>
                                @foreach ($all_prodi as $prodi)
                                    <option value="{{ $prodi }}">{{ $prodi }}</option>
                                @endforeach
                            </select>

                            <form action="{{ route('camaba.terbitkanNpmMassal') }}" method="post" id="formTerbitkanNPM"
                                style="display: none;">
                                @csrf
                                <input type="hidden" name="tahun_akademik" id="selectedTahunAkademik">
                                <div id="selectedNpmInputs"></div>
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="fi fi-rr-check me-1"></i> Terbitkan NPM (<span id="countSelected">0</span>)
                                </button>
                            </form>

                            <div id="dt_PayrollList_Search"></div>

                            <button type="button" class="btn btn-outline-secondary btn-sm" id="resetFilter"
                                title="Hapus semua filter">
                                <i class="fi fi-rr-refresh"></i> Reset
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0 pb-2">
                        <table id="dt_PayrollList" class="table display data-row-checkbox">
                            <thead class="table-light" style="vertical-align: middle">
                                <tr>
                                    <th style="width: 30px !important" class="pe-0">
                                        <div class="form-check">
                                            <input class="form-check-input" data-row-checkbox type="checkbox">
                                        </div>
                                    </th>
                                    <th>NOMOR PENDAFTARAN</th>
                                    <th>NAMA CALON MAHASISWA</th>
                                    <th>GELOMBANG</th>
                                    <th>PROGRAM STUDI</th>
                                    <th>JALUR MASUK</th>
                                    <th>KELAS PERKULIAHAN</th>
                                    <th>STATUS AKUN</th>
                                    <th class="text-middle">AKSI</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($mahasiswa_baru as $item)
                                    <tr
                                        data-tahun="{{ preg_match('/UMJA(\d{4})/', strtoupper($item['pmb_gelombang'] ?? ''), $matches) ? $matches[1] : '' }}">
                                        <td>
                                            <div class="form-check">
                                                <input class="form-check-input row-checkbox" data-checkbox type="checkbox"
                                                    value="{{ $item['id'] }}"
                                                    data-tahun="{{ preg_match('/UMJA(\d{4})/', strtoupper($item['pmb_gelombang'] ?? ''), $matches) ? $matches[1] : '' }}">
                                            </div>
                                        </td>
                                        <td>
                                            {!! $item['id'] . '<br>' !!}
                                            <small class="text-muted font-monospace">
                                                {{ $item['email'] ?? 'Belum ada email' }}
                                            </small>
                                        </td>
                                        <td>
                                            {{ Str::upper($item['nama']) }}
                                            <br>
                                            <small class="text-muted font-monospace">
                                                NPM : {{ $item['nim'] ?? 'Belum ada' }}
                                            </small>
                                        </td>
                                        <td>{{ $item['gelombang'] }}</td>
                                        <td>{{ $item['nama_id'] }}</td>
                                        <td>{{ $item['nama_jalur'] }}</td>
                                        <td>{{ $item['nama_kelas'] }}</td>
                                        <td>
                                            @if ($item['na'] == 'N')
                                                <span class="badge badge-sm px-2 bg-success">Aktif</span>
                                            @else
                                                <span class="badge badge-sm px-2 bg-danger">Tidak Aktif</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2 justify-content-end">
                                                <button
                                                    class="btn btn-white btn-sm btn-shadow btn-icon waves-effect dropdown-toggle"
                                                    type="button">
                                                    <i class="fi fi-rr-eye"></i>
                                                </button>
                                                <form
                                                    action="{{ route('camaba.terbitkanNpm', Crypt::encrypt($item['id'])) }}"
                                                    method="post">
                                                    @csrf
                                                    <input type="hidden" name="tahun_akademik"
                                                        value="{{ preg_match('/UMJA(\d{4})/', strtoupper($item['pmb_gelombang'] ?? ''), $matches) ? $matches[1] : '' }}">
                                                    <button type="submit"
                                                        class="btn btn-white btn-sm btn-shadow btn-icon waves-effect dropdown-toggle"
                                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                                        data-bs-title="Terbitkan NPM">
                                                        <i class="fi fi-rr-refresh"></i>
                                                </form>
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

    @if (session('result'))
        <div class="modal fade" id="newCredentialResult" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header py-3">
                        <h5 class="modal-title">Hasil Generate NPM</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>{{ session('result') }}</p>
                    </div>
                </div>
            </div>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var myModal = new bootstrap.Modal(document.getElementById('newCredentialResult'));
                myModal.show();
            });
        </script>
    @endif

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

        $.fn.dataTable.ext.search.push(
            function(settings, data, dataIndex) {
                if (settings.nTable.id !== 'dt_PayrollList') {
                    return true;
                }

                var tahunFilter = $('#filterTahun').val() || '';
                var prodiFilter = $('#filterProdi').val() || '';

                if (!tahunFilter && !prodiFilter) {
                    return true;
                }

                var row = settings.aoData[dataIndex].nTr;
                var tahunData = $(row).attr('data-tahun') || '';

                var prodiData = $.trim(data[4] || '');

                if (tahunFilter && tahunData !== tahunFilter) {
                    return false;
                }

                if (prodiFilter && prodiData !== prodiFilter) {
                    return false;
                }

                return true;
            }
        );

        window.dt_PayrollList = null;
        var filterStorageKey = 'maba_filter_' + window.location.pathname;

        function getSavedFilters() {
            try {
                return JSON.parse(sessionStorage.getItem(filterStorageKey)) || {};
            } catch (e) {
                return {};
            }
        }

        function saveFilters() {
            if (!window.dt_PayrollList) {
                return;
            }

            sessionStorage.setItem(filterStorageKey, JSON.stringify({
                tahun: $('#filterTahun').val(),
                prodi: $('#filterProdi').val(),
                search: $('#dt_PayrollList_Search input[type="search"]').val() || window.dt_PayrollList.search()
            }));
        }

        function applySavedFilters() {
            var savedFilters = getSavedFilters();

            if (savedFilters.tahun) {
                $('#filterTahun').val(savedFilters.tahun).trigger('change');
            }

            if (savedFilters.prodi) {
                $('#filterProdi').val(savedFilters.prodi).trigger('change');
            }

            if (savedFilters.search) {
                window.dt_PayrollList.search(savedFilters.search);
                $('#dt_PayrollList_Search input[type="search"]').val(savedFilters.search);
            }
        }

        function clearFilters() {
            $('#filterTahun').val('').trigger('change');
            $('#filterProdi').val('').trigger('change');
            $('#dt_PayrollList_Search input[type="search"]').val('');
            if (window.dt_PayrollList) {
                window.dt_PayrollList.search('').draw();
            }
            sessionStorage.removeItem(filterStorageKey);
        }

        if ($('#dt_PayrollList').length) {
            window.dt_PayrollList = $('#dt_PayrollList').DataTable({
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
                    var dtSearch = $('#dt_PayrollList_wrapper .dt-search').detach();
                    $('#dt_PayrollList_Search').append(dtSearch);
                    $('#dt_PayrollList_Search .dt-search').prepend('<i class="fi fi-rr-search"></i>');
                    $('#dt_PayrollList_Search .dt-search label').remove();
                    $('#dt_PayrollList_wrapper > .row.mt-2.justify-content-between').first().remove();
                },
                columnDefs: [{
                    targets: [0, 8],
                    orderable: false,
                }]
            });

            applySavedFilters();
            window.dt_PayrollList.draw();
        }

        $('#filterTahun').on('change', function() {
            if (window.dt_PayrollList) {
                saveFilters();
                window.dt_PayrollList.draw();
            }
        });

        $('#filterProdi').on('change', function() {
            if (window.dt_PayrollList) {
                saveFilters();
                window.dt_PayrollList.draw();
            }
        });

        $(document).on('input', '#dt_PayrollList_Search input[type="search"]', function() {
            if (window.dt_PayrollList) {
                window.dt_PayrollList.search($(this).val()).draw();
            }

            saveFilters();
        });

        $('#resetFilter').on('click', function() {
            clearFilters();
        });

        var selectedIds = [];

        window.dt_PayrollList.on('draw', function() {
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
            $('#countSelected').text(selectedIds.length);

            if (selectedIds.length > 0) {
                $('#formTerbitkanNPM').show();
            } else {
                $('#formTerbitkanNPM').hide();
            }
        }

        function syncSelectedNpmInputs() {
            $('#selectedNpmInputs').empty();
            $('#selectedTahunAkademik').val($('#filterTahun').val());

            selectedIds.forEach(function(id) {
                $('#selectedNpmInputs').append('<input type="hidden" name="npm[]" value="' + id + '">');
            });
        }

        $('#formTerbitkanNPM').on('submit', function(e) {
            if (!$('#filterTahun').val()) {
                e.preventDefault();
                alert('Pilih tahun terlebih dahulu');
                return;
            }

            if (selectedIds.length === 0) {
                e.preventDefault();
                alert('Pilih minimal 1 data');
                return;
            }

            if (!confirm('Terbitkan NPM untuk ' + selectedIds.length + ' data yang dipilih?')) {
                e.preventDefault();
                return;
            }

            syncSelectedNpmInputs();
        });
    </script>
@endpush
