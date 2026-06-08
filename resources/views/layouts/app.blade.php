<!DOCTYPE html>
<html lang="en">

<head>

    <base href="{{ asset('/') }}">

    <title>
        @hasSection('title')
            @yield('title') | {{ config('app.name') }}
        @else
            {{ config('app.name') }}
        @endif
    </title>

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="icon" type="image/png" href="{{ asset('') }}assets/images/favicon.png">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('') }}assets/images/apple-touch-icon.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap"
        rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('') }}assets/libs/flaticon/css/all/all.css">
    <link rel="stylesheet" href="{{ asset('') }}assets/libs/lucide/lucide.css">
    <link rel="stylesheet" href="{{ asset('') }}assets/libs/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('') }}assets/libs/simplebar/simplebar.css">
    <link rel="stylesheet" href="{{ asset('') }}assets/libs/node-waves/waves.css">
    <link rel="stylesheet" href="{{ asset('') }}assets/libs/bootstrap-select/css/bootstrap-select.min.css">

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <style>
        .select2-container--default .select2-selection--single {
            height: auto;
            min-height: calc(1.6em + 0.7rem + calc(var(--bs-border-width, 1px) * 2));
            padding: 0.35rem 2rem 0.35rem 0.7rem;
            font-size: 0.8125rem;
            font-weight: 500;
            line-height: 1.6;
            color: var(--bs-body-color, #212529);
            background-color: var(--bs-body-bg, #fff);
            border: var(--bs-border-width, 1px) solid var(--bs-border-color, #dee2e6);
            border-radius: 8px;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 1.6;
            padding: 0;
            color: var(--bs-body-color, #212529);
        }
        .select2-container--default .select2-selection--single .select2-selection__placeholder {
            color: var(--bs-body-color, #212529);
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 100%;
            top: 0;
            right: 0.7rem;
        }
        .select2-container--default .select2-selection--single:focus {
            outline: 0;
        }
        .select2-container--default.select2-container--focus .select2-selection--single,
        .select2-container--default.select2-container--open .select2-selection--single {
            border-color: var(--bs-primary, #316aff);
            box-shadow: 0 0 0 0.1rem rgba(49,106,255,.25);
        }
        .select2-dropdown {
            border-color: var(--bs-border-color, #dee2e6);
            border-radius: 8px;
            font-size: 0.8125rem;
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,.08);
        }
        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: var(--bs-primary, #316aff);
        }
        .select2-container--default .select2-results__option[aria-selected=true] {
            background-color: var(--bs-primary-bg-subtle, #cfe2ff);
            color: var(--bs-primary-text-emphasis, #052c65);
            font-weight: 500;
        }
        .select2-container--default .select2-results__option--highlighted[aria-selected=true] {
            background-color: var(--bs-primary, #316aff);
            color: #fff;
        }
        .select2-container--default .select2-search--dropdown .select2-search__field {
            border: var(--bs-border-width, 1px) solid var(--bs-border-color, #dee2e6);
            border-radius: 0.375rem;
            padding: 0.375rem 0.5rem;
            font-size: 0.8125rem;
        }
    </style>

    @stack('css')
    <link rel="stylesheet" href="{{ asset('') }}assets/css/styles.css">
</head>

<body>
    <div class="page-layout">

        @include('layouts.partials.navbar')

        @include('layouts.partials.sidebar')

        <main class="app-wrapper">
            @yield('content')
        </main>

        @include('layouts.partials.footer')

    </div>

    <script src="{{ asset('') }}assets/libs/global/global.min.js"></script>
    <script src="{{ asset('') }}assets/js/appSettings.js"></script>
    <script src="{{ asset('') }}assets/js/main.js"></script>

    <script>
        $(document).on('click', '.btn-hapus', function() {
            const modalId = $(this).data('modal') || 'deleteModal';
            const id = $(this).data('id');
            const name = $(this).data('name');
            const url = $(this).data('url');

            const modalEl = document.getElementById(modalId);

            if (!modalEl) {
                console.error('Modal tidak ditemukan: #' + modalId);
                return;
            }

            modalEl.querySelector('.modal-item-name').textContent = name;
            modalEl.querySelector('.modal-item-id').value = id;
            modalEl.querySelector('.btn-confirm-hapus').dataset.url = url;
            document.activeElement.blur();

            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        });

        $(document).on('click', '.btn-confirm-hapus', function() {
            const btn = $(this);
            const modalEl = btn.closest('.modal')[0];
            const id = modalEl.querySelector('.modal-item-id').value;
            const url = btn.data('url');

            if (!url || !id) {
                showToast('error', 'URL atau ID tidak ditemukan.');
                return;
            }

            btn.prop('disabled', true).html(
                '<span class="spinner-border spinner-border-sm me-1"></span> Menghapus...'
            );

            $.ajax({
                url: url,
                method: 'POST',
                data: {
                    _method: 'DELETE'
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Accept': 'application/json',
                },
                success: function(res) {
                    document.activeElement.blur();

                    bootstrap.Modal.getOrCreateInstance(modalEl).hide();

                    const $row = $('.btn-hapus[data-id="' + id + '"]').closest('tr');
                    if ($row.length && $.fn.DataTable.isDataTable($row.closest('table'))) {
                        $row.closest('table').DataTable().row($row).remove().draw();
                    } else {
                        $row.fadeOut(300, () => $row.remove());
                    }

                    showToast('success', res.message ?? 'Data berhasil dihapus.');

                    modalEl.addEventListener('hidden.bs.modal', function() {
                        btn.prop('disabled', false).html(
                            '<i class="fi fi-rr-trash me-1"></i> Hapus'
                        );
                    }, { once: true });
                },
                error: function(xhr) {
                    const res = xhr.responseJSON;
                    let msg = 'Gagal menghapus data.';

                    if (res?.message) {
                        msg = res.message;
                    } else if (res?.errors) {
                        const firstKey = Object.keys(res.errors)[0];
                        if (firstKey && res.errors[firstKey]?.length) {
                            msg = res.errors[firstKey][0];
                        }
                    } else if (xhr.status === 500) {
                        msg = 'Terjadi kesalahan server (500). Silakan coba lagi.';
                    } else if (xhr.status === 404) {
                        msg = 'Data tidak ditemukan (404).';
                    } else if (xhr.status === 403) {
                        msg = 'Anda tidak memiliki izin untuk menghapus data ini.';
                    }

                    // Tampilkan notifikasi terpisah, bukan di dalam modal
                    showToast('error', msg);

                    btn.prop('disabled', false).html('<i class="fi fi-rr-trash me-1"></i> Hapus');
                }
            });
        });

        function showToast(type, message) {
            const s = {
                success: {
                    color: '#166534',
                    bg: '#f0fdf4',
                    border: '#bbf7d0'
                },
                error: {
                    color: '#dc2626',
                    bg: '#fef2f2',
                    border: '#fecaca'
                },
            } [type] || {
                color: '#dc2626',
                bg: '#fef2f2',
                border: '#fecaca'
            };

            const toast = $(
                '<div style="position:fixed;top:20px;right:20px;z-index:9999;' +
                'background:' + s.bg + ';border:1px solid ' + s.border + ';' +
                'color:' + s.color + ';padding:12px 18px;border-radius:10px;' +
                'font-size:13px;font-weight:500;box-shadow:0 2px 8px rgba(0,0,0,.08)">' +
                message + '</div>'
            );

            $('body').append(toast);
            setTimeout(() => toast.fadeOut(300, () => toast.remove()), 3000);
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(function() {
            $('.select2').each(function() {
                var $el = $(this);
                var placeholder = $el.data('placeholder') || $el.find('option:first').text() || '— Pilih —';
                var opts = {
                    placeholder: placeholder,
                    width: '100%',
                };
                // Modal Bootstrap: dropdown muncul di dalam modal agar scroll tetap jalan
                var $modal = $el.closest('.modal');
                if ($modal.length) {
                    opts.dropdownParent = $modal;
                }
                $el.select2(opts);
            });
        });
    </script>
    @stack('js')
</body>

</html>
