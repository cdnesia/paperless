@php
    $user = auth()->user();
    $isSuperAdmin = $user->hasRole('super-admin');

    $menus = [];

    // Dashboard — semua role bisa akses
    if ($user->can('dashboard')) {
        $menus[] = [
            'title' => 'Dashboard',
            'icon' => 'fi fi-ts-dashboard-monitor',
            'route' => 'dashboard',
        ];
    }

    // Surat Keluar
    if ($user->can('surat-keluar.index')) {
        $menus[] = [
            'title' => 'Surat Keluar',
            'icon' => 'fi fi-ts-envelope-plus',
            'route' => 'surat-keluar.index',
        ];
    }

    // Surat Masuk
    if ($user->can('surat-masuk.index')) {
        $menus[] = [
            'title' => 'Surat Masuk',
            'icon' => 'fi fi-ts-envelope-download',
            'route' => 'surat-masuk.index',
        ];
    }

    // Disposisi Surat (tampilkan jika punya salah satu akses disposisi)
    if ($user->can('disposisi.index') || $user->can('disposisi-masuk.index')) {
        $disposisiChildren = [];

        if ($user->can('disposisi-masuk.index')) {
            $disposisiChildren[] = [
                'title' => 'Disposisi Masuk',
                'icon' => 'fi fi-rr-user-add',
                'route' => 'disposisi-masuk.index',
            ];
        }

        if ($user->can('disposisi.index')) {
            $disposisiChildren[] = [
                'title' => 'Disposisi Keluar',
                'route' => 'disposisi.index',
                'icon' => 'fi fi-rr-users',
            ];
        }

        if (!empty($disposisiChildren)) {
            $menus[] = [
                'title' => 'Disposisi Surat',
                'icon' => 'fi fi-tr-share-square',
                'children' => $disposisiChildren,
            ];
        }
    }

    // Pengaturan — hanya super-admin
    if ($isSuperAdmin) {
        $menus[] = [
            'title' => 'Pengaturan',
            'icon' => 'fi fi-rr-settings-sliders',
            'children' => [
                [
                    'title' => 'Data Pengguna',
                    'icon' => 'fi fi-rr-user-add',
                    'route' => 'users.index',
                ],
                [
                    'title' => 'Unit Kerja',
                    'icon' => 'fi fi-rr-building',
                    'route' => 'unit-kerja.index',
                ],
                [
                    'title' => 'Role',
                    'route' => 'roles.index',
                    'icon' => 'fi fi-rr-users',
                ],
                [
                    'title' => 'Permission',
                    'route' => 'permissions.index',
                    'icon' => 'fi fi-rr-users',
                ],
                [
                    'title' => 'Sistem',
                    'icon' => 'fi fi-rr-settings',
                    'route' => 'pengaturan.index',
                ],
            ],
        ];
    }
@endphp

<aside class="app-menubar" id="appMenubar">
    <div class="app-navbar-brand">
        <a class="navbar-brand-logo" href="{{ route('dashboard') }}">
            <img src="{{ asset('') }}assets/images/logo.svg" alt="GXON Admin Dashboard Logo">
        </a>
        <a class="navbar-brand-mini visible-light" href="{{ route('dashboard') }}">
            {{ config('app.name') }}
        </a>
        <a class="navbar-brand-mini visible-dark" href="{{ route('dashboard') }}">
            {{ config('app.name') }}
        </a>
    </div>
    <nav class="app-navbar" data-simplebar>
        <ul class="menubar">

            @foreach ($menus as $menu)
                @php
                    $hasChildren = isset($menu['children']);
                    $parentActive = false;

                    if (isset($menu['route'])) {
                        $routeName = $menu['route'];
                        $parentActive = str_contains($routeName, '.')
                            ? Route::is(explode('.', $routeName)[0] . '.*')
                            : Route::is($routeName);
                    } elseif ($hasChildren) {
                        foreach ($menu['children'] as $child) {
                            $childRoute = $child['route'];
                            $childMatch = str_contains($childRoute, '.')
                                ? Route::is(explode('.', $childRoute)[0] . '.*')
                                : Route::is($childRoute);
                            if ($childMatch) {
                                $parentActive = true;
                                break;
                            }
                        }
                    }
                @endphp

                @if (!$hasChildren)
                    <li class="menu-item">
                        <a class="menu-link {{ $parentActive ? 'active' : '' }}" href="{{ route($menu['route']) }}">
                            <i class="{{ $menu['icon'] }}"></i>
                            <span class="menu-label">{{ $menu['title'] }}</span>
                        </a>
                    </li>
                @else
                    <li class="menu-item menu-arrow {{ $parentActive ? 'active' : '' }}">
                        <a class="menu-link {{ $parentActive ? 'open' : '' }}" href="javascript:void(0);">
                            <i class="{{ $menu['icon'] }}"></i>
                            <span class="menu-label">{{ $menu['title'] }}</span>
                        </a>
                        <ul class="menu-inner" style="{{ $parentActive ? 'display:block;' : 'display:none;' }}">
                            @foreach ($menu['children'] as $child)
                                @php
                                    $childRoute = $child['route'];
                                    $childActive = str_contains($childRoute, '.')
                                        ? Route::is(explode('.', $childRoute)[0] . '.*')
                                        : Route::is($childRoute);
                                @endphp
                                <li class="menu-item {{ $childActive ? 'active' : '' }}">
                                    <a class="menu-link {{ $childActive ? 'active' : '' }}"
                                        href="{{ route($child['route']) }}">
                                        <span class="menu-label">{{ $child['title'] }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </li>
                @endif
            @endforeach
        </ul>
    </nav>
    <div class="app-footer">
        <a href="pages/faq.html" class="btn btn-outline-light waves-effect btn-shadow btn-app-nav w-100">
            <i class="fi fi-rs-interrogation text-primary"></i>
            <span class="nav-text">Bantuan</span>
        </a>
    </div>
</aside>
