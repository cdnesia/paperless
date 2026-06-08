<?php

namespace App\Http\Controllers;

use App\Models\Disposisi;
use App\Models\SuratKeluar;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $isSuperAdmin = $user->hasRole('superadmin');

        // Surat Masuk: surat keluar (status=s) yang diterima user ini via pivot
        $suratMasukQuery = SuratKeluar::where('status', 's');
        if (!$isSuperAdmin) {
            $suratMasukQuery->whereHas('penerima', fn($q) => $q->where('user_id', $user->id));
        }
        $suratMasuk = $suratMasukQuery->count();

        // Surat Keluar: surat yang dibuat user ini
        $suratKeluarQuery = SuratKeluar::query();
        if (!$isSuperAdmin) {
            $suratKeluarQuery->where('user_id', $user->id);
        }
        $suratKeluar = $suratKeluarQuery->count();

        // Disposisi Masuk: disposisi yang ditujukan ke user ini
        $disposisiMasukQuery = Disposisi::whereNotNull('surat_keluar_id');
        if (!$isSuperAdmin) {
            $disposisiMasukQuery->where('pengguna_id', $user->id);
        }
        $disposisiMasuk = $disposisiMasukQuery->count();

        // Disposisi Keluar: disposisi yang dikirim user ini
        $disposisiKeluarQuery = Disposisi::whereNotNull('surat_keluar_id');
        if (!$isSuperAdmin) {
            $disposisiKeluarQuery->where('pengirim_id', $user->id);
        }
        $disposisiKeluar = $disposisiKeluarQuery->count();

        return view('dashboard.dashboard', compact(
            'suratMasuk', 'suratKeluar', 'disposisiMasuk', 'disposisiKeluar'
        ));
    }
}
