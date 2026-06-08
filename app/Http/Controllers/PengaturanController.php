<?php

namespace App\Http\Controllers;

use App\Models\Pengaturan;
use Illuminate\Http\Request;

class PengaturanController extends Controller
{
    public function index()
    {
        $pengaturan = Pengaturan::dapatkanBanyak([
            'app_nama', 'app_deskripsi',
            'telegram_bot_token',
            'telegram_notif_surat_masuk', 'telegram_tpl_surat_masuk',
            'telegram_notif_disposisi_masuk', 'telegram_tpl_disposisi_masuk',
        ]);

        return view('pengaturan.index', compact('pengaturan'));
    }

    public function simpan(Request $request)
    {
        $validated = $request->validate([
            'app_nama' => 'required|string|max:100',
            'app_deskripsi' => 'nullable|string|max:255',
            'telegram_bot_token' => 'nullable|string|max:255',
            'telegram_notif_surat_masuk' => 'nullable|in:0,1',
            'telegram_tpl_surat_masuk' => 'nullable|string|max:1000',
            'telegram_notif_disposisi_masuk' => 'nullable|in:0,1',
            'telegram_tpl_disposisi_masuk' => 'nullable|string|max:1000',
        ]);

        foreach ([
            'app_nama', 'app_deskripsi',
            'telegram_bot_token',
            'telegram_notif_surat_masuk', 'telegram_tpl_surat_masuk',
            'telegram_notif_disposisi_masuk', 'telegram_tpl_disposisi_masuk',
        ] as $kunci) {
            Pengaturan::atur($kunci, $validated[$kunci] ?? '');
        }

        return redirect()->route('pengaturan.index')
            ->with('success', 'Pengaturan berhasil disimpan.');
    }
}
