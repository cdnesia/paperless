<!DOCTYPE html>
<html lang="en">

<head>
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
                                <img class="visible-light" src="{{ asset('assets/images/logo-full.svg') }}"
                                    alt="{{ config('app.name') }} logo">
                                <img class="visible-dark" src="{{ asset('assets/images/logo-full-white.svg') }}"
                                    alt="{{ config('app.name') }} logo">
                            </a>
                        </div>
                        <div class="text-center mb-4">
                            <h5 class="mb-1">Selamat Datang</h5>
                            <p>Sign in to access your secure admin dashboard.</p>
                        </div>
                        @if ($errors->any() && !session('retryAfter'))
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

                        @if (session('retryAfter'))
                            <div class="alert text-bg-warning alert-dismissible fade show d-flex align-items-start"
                                role="alert" id="countdownAlert">
                                <ul class="mb-0 ps-3">
                                    <li id="countdownMessage">Terlalu banyak percobaan login. Coba lagi dalam <strong id="countdownSeconds">{{ session('retryAfter') }}</strong> detik.</li>
                                </ul>
                                <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        @endif
                        <form action="{{ route('login') }}" method="POST">
                            @csrf
                            <div class="mb-4">
                                <label class="form-label" for="loginEmail">Email Address</label>
                                <input type="email" name="email" class="form-control" id="loginEmail"
                                    placeholder="info@example.com" value="{{ old('email') }}" required>
                            </div>
                            <div class="mb-4">
                                <label class="form-label" for="loginPassword">Password</label>
                                <div class="input-group">
                                    <input type="password" name="password" class="form-control" id="loginPassword"
                                        placeholder="********" required>
                                    <button class="btn btn-light border" type="button" id="togglePassword"
                                        tabindex="-1" title="Lihat password">
                                        <i class="fi fi-rr-eye" id="toggleIcon"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="mb-4">
                                <div class="d-flex justify-content-between">
                                    <div class="form-check mb-0">
                                        <input class="form-check-input" name="remember" type="checkbox" id="rememberMe">
                                        <label class="form-check-label" for="rememberMe"> Remember Me </label>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <button type="submit" value="Submit"
                                    class="btn btn-primary waves-effect waves-light w-100">Login</button>
                            </div>

                        </form>
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
            // Toggle password visibility
            var toggleBtn = document.getElementById('togglePassword');
            var passwordInput = document.getElementById('loginPassword');
            var toggleIcon = document.getElementById('toggleIcon');
            if (toggleBtn && passwordInput) {
                toggleBtn.addEventListener('click', function() {
                    var isPassword = passwordInput.type === 'password';
                    passwordInput.type = isPassword ? 'text' : 'password';
                    toggleIcon.className = isPassword ? 'fi fi-rr-eye-crossed' : 'fi fi-rr-eye';
                });
            }

            var countdownEl = document.getElementById('countdownSeconds');
            if (countdownEl) {
                var seconds = parseInt(countdownEl.textContent);
                var interval = setInterval(function() {
                    seconds--;
                    if (seconds <= 0) {
                        clearInterval(interval);
                        document.getElementById('countdownAlert').remove();
                        location.reload();
                    } else {
                        countdownEl.textContent = seconds;
                    }
                }, 1000);
            }
        });
    </script>
</body>

</html>
