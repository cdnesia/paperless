<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SuratMasukController;
use App\Http\Controllers\SuratKeluarController;
use App\Http\Controllers\DisposisiController;
use App\Http\Controllers\KeycloakController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UnitKerjaController;
use App\Http\Controllers\PengaturanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TandaTanganDigitalController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'index'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');

Route::get('/auth/login', [KeycloakController::class, 'login'])->name('auth.login');
Route::get('/auth/callback', [KeycloakController::class, 'callback'])->name('auth.callback');

// Ganti password wajib — diluar route.permission + password.force
Route::middleware(['auth'])->group(function () {
    Route::get('/password/change', [AuthController::class, 'showChangePassword'])->name('password.change');
    Route::post('/password/update', [AuthController::class, 'updatePassword'])->name('password.update');
});

Route::middleware(['auth', 'route.permission', 'password.force'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::prefix('surat-masuk')->name('surat-masuk.')->group(function () {
        Route::get('/', [SuratMasukController::class, 'index'])->name('index');
        Route::get('/{suratKeluar}', [SuratMasukController::class, 'show'])->name('show');
        Route::patch('/{suratKeluar}/mark-as-read', [SuratMasukController::class, 'markAsRead'])->name('mark-as-read');
        Route::patch('/{suratKeluar}/mark-as-unread', [SuratMasukController::class, 'markAsUnread'])->name('mark-as-unread');
        Route::patch('/{suratKeluar}/update-status', [SuratMasukController::class, 'updateStatus'])->name('update-status');
        Route::patch('/{suratKeluar}/terima', [SuratMasukController::class, 'terima'])->name('terima');
        Route::patch('/{suratKeluar}/tolak', [SuratMasukController::class, 'tolak'])->name('tolak');
    });

    Route::prefix('surat-keluar')->name('surat-keluar.')->group(function () {
        Route::get('/', [SuratKeluarController::class, 'index'])->name('index');
        Route::get('/create', [SuratKeluarController::class, 'create'])->name('create');
        Route::post('/', [SuratKeluarController::class, 'store'])->name('store');
        Route::get('/{suratKeluar}', [SuratKeluarController::class, 'show'])->name('show');
        Route::get('/{suratKeluar}/edit', [SuratKeluarController::class, 'edit'])->name('edit');
        Route::put('/{suratKeluar}', [SuratKeluarController::class, 'update'])->name('update');
        Route::delete('/{suratKeluar}', [SuratKeluarController::class, 'destroy'])->name('destroy');
        Route::patch('/{suratKeluar}/mark-as-read', [SuratKeluarController::class, 'markAsRead'])->name('mark-as-read');
        Route::patch('/{suratKeluar}/mark-as-unread', [SuratKeluarController::class, 'markAsUnread'])->name('mark-as-unread');
        Route::patch('/{suratKeluar}/send', [SuratKeluarController::class, 'send'])->name('send');
        Route::post('/{suratKeluar}/history', [SuratKeluarController::class, 'tambahHistory'])->name('history');
        Route::post('/{suratKeluar}/disposisi', [SuratKeluarController::class, 'disposisi'])->name('disposisi');
    });

    Route::prefix('disposisi')->name('disposisi.')->group(function () {
        Route::get('/', [DisposisiController::class, 'index'])->name('index');
        Route::get('/{disposisi}', [DisposisiController::class, 'show'])->name('show');
        Route::delete('/{disposisi}', [DisposisiController::class, 'destroy'])->name('destroy');
        Route::patch('/{disposisi}/mark-as-read', [DisposisiController::class, 'markAsRead'])->name('mark-as-read');
        Route::patch('/{disposisi}/mark-as-unread', [DisposisiController::class, 'markAsUnread'])->name('mark-as-unread');
        Route::patch('/{disposisi}/update-status', [DisposisiController::class, 'updateStatus'])->name('update-status');
    });

    // Disposisi Masuk Routes (Incoming letters forwarded to you)
    Route::prefix('disposisi-masuk')->name('disposisi-masuk.')->group(function () {
        Route::get('/', [DisposisiController::class, 'masukIndex'])->name('index');
        Route::get('/{disposisi}', [DisposisiController::class, 'showMasuk'])->name('show');
        Route::post('/{disposisi}/teruskan', [DisposisiController::class, 'teruskanMasuk'])->name('teruskan');
        Route::patch('/{disposisi}/mark-as-read', [DisposisiController::class, 'markAsReadMasuk'])->name('mark-as-read');
        Route::patch('/{disposisi}/mark-as-unread', [DisposisiController::class, 'markAsUnreadMasuk'])->name('mark-as-unread');
        Route::patch('/{disposisi}/update-status', [DisposisiController::class, 'updateStatus'])->name('update-status');
        Route::patch('/{disposisi}/terima', [DisposisiController::class, 'terimaMasuk'])->name('terima');
        Route::patch('/{disposisi}/tolak', [DisposisiController::class, 'tolakMasuk'])->name('tolak');
    });

    Route::prefix('roles')->name('roles.')->group(function () {
        Route::get('/', [RoleController::class, 'index'])->name('index');
        Route::get('/create', [RoleController::class, 'create'])->name('create');
        Route::post('/', [RoleController::class, 'store'])->name('store');
        Route::get('/{role}', [RoleController::class, 'show'])->name('show');
        Route::get('/{role}/edit', [RoleController::class, 'edit'])->name('edit');
        Route::put('/{role}', [RoleController::class, 'update'])->name('update');
        Route::delete('/{role}', [RoleController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('permissions')->name('permissions.')->group(function () {
        Route::get('/', [PermissionController::class, 'index'])->name('index');
        Route::get('/create', [PermissionController::class, 'create'])->name('create');
        Route::post('/', [PermissionController::class, 'store'])->name('store');
        Route::post('/sync', [PermissionController::class, 'sync'])->name('sync');
        Route::get('/{permission}', [PermissionController::class, 'show'])->name('show');
        Route::get('/{permission}/edit', [PermissionController::class, 'edit'])->name('edit');
        Route::put('/{permission}', [PermissionController::class, 'update'])->name('update');
        Route::delete('/{permission}', [PermissionController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/create', [UserController::class, 'create'])->name('create');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('/{user}', [UserController::class, 'show'])->name('show');
        Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
        Route::put('/{user}', [UserController::class, 'update'])->name('update');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
        Route::post('/{user}/test-telegram', [UserController::class, 'testTelegram'])->name('test-telegram');
    });

    Route::prefix('unit-kerja')->name('unit-kerja.')->group(function () {
        Route::get('/', [UnitKerjaController::class, 'index'])->name('index');
        Route::get('/create', [UnitKerjaController::class, 'create'])->name('create');
        Route::post('/', [UnitKerjaController::class, 'store'])->name('store');
        Route::get('/{unitKerja}/edit', [UnitKerjaController::class, 'edit'])->name('edit');
        Route::put('/{unitKerja}', [UnitKerjaController::class, 'update'])->name('update');
        Route::delete('/{unitKerja}', [UnitKerjaController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('pengaturan')->name('pengaturan.')->group(function () {
        Route::get('/', [PengaturanController::class, 'index'])->name('index');
        Route::post('/simpan', [PengaturanController::class, 'simpan'])->name('simpan');
    });

    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'index'])->name('index');
        Route::put('/', [ProfileController::class, 'update'])->name('update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('delete');
    });

    Route::prefix('tanda-tangan-digital')->name('tanda-tangan-digital.')->group(function () {
        Route::get('/{suratKeluar}', [TandaTanganDigitalController::class, 'index'])->name('index');
        Route::post('/{suratKeluar}/sign', [TandaTanganDigitalController::class, 'sign'])->name('sign');
    });

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

// Route publik: verifikasi via QR scan (dengan ID)
Route::get('/tanda-tangan-digital/verify/{tandaTanganDigital}', [TandaTanganDigitalController::class, 'verify'])
    ->name('tanda-tangan-digital.verify');
Route::post('/tanda-tangan-digital/verify/{tandaTanganDigital}', [TandaTanganDigitalController::class, 'verifyUpload'])
    ->name('tanda-tangan-digital.verify-upload');

// Route publik: verifikasi mandiri — upload file saja, tanpa ID
Route::get('/verifikasi', [TandaTanganDigitalController::class, 'verifyByUpload'])
    ->name('verifikasi');
Route::post('/verifikasi', [TandaTanganDigitalController::class, 'verifyByUploadPost'])
    ->name('verifikasi.post');

// Route publik: serve file dari storage dengan pengecekan keberadaan file
Route::get('/files/{path}', [App\Http\Controllers\FileController::class, 'serve'])
    ->where('path', '.*')
    ->name('files.serve');
