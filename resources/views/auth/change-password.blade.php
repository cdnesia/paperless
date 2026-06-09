<!DOCTYPE html>
<html lang="en">

<head>
    <title>Ganti Password | {{ config('app.name') }}</title>
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

    <link rel="stylesheet" href="{{ asset('') }}assets/css/styles.css">

</head>

<body>
    <div class="page-layout">

        <div class="auth-wrapper min-vh-100 px-2"
            style="background-image: url({{ asset('assets/images/auth/auth.webp') }}); background-size: cover; background-position: center; background-repeat: no-repeat;">
            <div class="row g-0 min-vh-100">
                <div class="col-xl-5 col-lg-6 ms-auto px-sm-4 align-self-center py-4">
                    <div class="card card-body p-4 p-sm-5 maxw-450px m-auto rounded-4 auth-card" data-simplebar>
                        <div class="mb-4 text-center">
                            <a href="/" aria-label="{{ config('app.name') }} logo">
                                <img class="visible-light" src="{{ asset('assets/images/logo-full.png') }}"
                                    alt="{{ config('app.name') }} logo" style="max-width: 200px">
                            </a>
                        </div>
                        <div class="text-center mb-4">
                            <h5 class="mb-1">Ganti Password</h5>
                            <p>Demi keamanan, Anda wajib mengganti password default sebelum melanjutkan.</p>
                        </div>

                        @if (session('warning'))
                            <div class="alert text-bg-warning alert-dismissible fade show d-flex align-items-start"
                                role="alert">
                                <ul class="mb-0 ps-3">
                                    <li>{{ session('warning') }}</li>
                                </ul>
                                <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="alert text-bg-danger alert-dismissible fade show d-flex align-items-start"
                                role="alert">
                                <ul class="mb-0 ps-3">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        @endif

                        <form action="{{ route('password.update') }}" method="POST" autocomplete="off">
                            @csrf
                            <div class="mb-4">
                                <label class="form-label" for="passwordOld">Password Saat Ini</label>
                                <div class="input-group">
                                    <input type="password" name="password_old" class="form-control" id="passwordOld"
                                        placeholder="********" required>
                                    <button class="btn btn-light border" type="button" id="togglePasswordOld"
                                        tabindex="-1" title="Lihat password">
                                        <i class="fi fi-rr-eye" id="toggleIconOld"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label" for="passwordNew">Password Baru</label>
                                <div class="input-group">
                                    <input type="password" name="password" class="form-control" id="passwordNew"
                                        placeholder="********" required>
                                    <button class="btn btn-light border" type="button" id="togglePasswordNew"
                                        tabindex="-1" title="Lihat password">
                                        <i class="fi fi-rr-eye" id="toggleIconNew"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label" for="passwordConfirmation">Konfirmasi Password Baru</label>
                                <div class="input-group">
                                    <input type="password" name="password_confirmation" class="form-control" id="passwordConfirmation"
                                        placeholder="********" required>
                                    <button class="btn btn-light border" type="button" id="togglePasswordConfirmation"
                                        tabindex="-1" title="Lihat password">
                                        <i class="fi fi-rr-eye" id="toggleIconConfirmation"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="mb-3">
                                <button type="submit" value="Submit"
                                    class="btn btn-primary waves-effect waves-light w-100">Simpan Password Baru</button>
                            </div>

                        </form>

                        <div class="text-center mt-3">
                            <form action="{{ route('logout') }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-link text-muted small">
                                    <i class="fi fi-rr-sign-out-alt me-1"></i> Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <script src="{{ asset('') }}assets/libs/global/global.min.js"></script>
    <script src="{{ asset('') }}assets/js/appSettings.js"></script>
    <script src="{{ asset('') }}assets/js/main.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            function setupToggle(buttonId, inputId, iconId) {
                var btn = document.getElementById(buttonId);
                var input = document.getElementById(inputId);
                var icon = document.getElementById(iconId);
                if (btn && input) {
                    btn.addEventListener('click', function() {
                        var isPassword = input.type === 'password';
                        input.type = isPassword ? 'text' : 'password';
                        icon.className = isPassword ? 'fi fi-rr-eye-crossed' : 'fi fi-rr-eye';
                    });
                }
            }

            setupToggle('togglePasswordOld', 'passwordOld', 'toggleIconOld');
            setupToggle('togglePasswordNew', 'passwordNew', 'toggleIconNew');
            setupToggle('togglePasswordConfirmation', 'passwordConfirmation', 'toggleIconConfirmation');
        });
    </script>
</body>

</html>
