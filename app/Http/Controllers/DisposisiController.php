<?php

namespace App\Http\Controllers;

use App\Models\Disposisi;
use App\Models\SuratKeluar;
use App\Models\User;
use App\Services\TelegramNotificationService;
use Illuminate\Http\Request;

/**
 * DisposisiController - Manages Letter Forwarding (Disposisi)
 * 
 * Konsep:
 * - Disposisi Masuk: Surat keluar yang di-forward ke saya
 *   * Penerima: Orang yang menerima disposisi (saya)
 *   * Pengirim: Orang yang forward-kan surat ke saya
 * 
 * - Disposisi Keluar: Surat keluar yang saya forward ke orang lain
 *   * Penerima: Orang yang menerima forward
 *   * Pengirim: Saya (yang forward-kan)
 */
class DisposisiController extends Controller
{
    /**
     * ════════════════════════════════════════════════════════
     * DISPOSISI MASUK (Surat keluar yg di-forward ke saya)
     * ════════════════════════════════════════════════════════
     */

    /**
     * List incoming dispositions - default: unread only, ?semua=1 to show all
     */
    public function masukIndex(Request $request)
    {
        $user = auth()->user();
        $semua = $request->boolean('semua');
        
        $disposisi = Disposisi::where('surat_keluar_id', '!=', null)
            ->where('pengguna_id', $user->id)
            ->when(!$semua, fn($q) => $q->where('dibaca', false))
            ->with(['suratKeluar', 'pengirim'])
            ->latest()
            ->get();

        return view('disposisi.masuk.index', compact('disposisi', 'semua'));
    }

    /**
     * Show incoming disposition detail
     */
    public function showMasuk(Disposisi $disposisi)
    {
        $user = auth()->user();

        // Hanya penerima disposisi yang bisa lihat
        if ($disposisi->pengguna_id !== $user->id) {
            abort(403);
        }

        // Auto-mark as read saat dibuka
        if (!$disposisi->dibaca) {
            $disposisi->update(['dibaca' => true]);
        }

        $disposisi->load(['suratKeluar', 'pengirim', 'pengguna']);

        $users = User::select('id', 'name', 'email')
            ->whereNotIn('id', [1, auth()->id()])
            ->get()->map(function ($u) {
            return [
                'value'  => $u->id,
                'name'   => $u->name,
                'email'  => $u->email,
                'avatar' => asset('assets/images/avatar/avatar1.webp'),
            ];
        });

        return view('disposisi.masuk.show', compact('disposisi', 'users'));
    }

    /**
     * Forward incoming disposition to another user
     * Juga update status disposisi saat ini menjadi "diteruskan"
     * dan update pivot surat_keluar_penerima sebagai dibaca
     */
    public function teruskanMasuk(Request $request, Disposisi $disposisi)
    {
        $user = auth()->user();

        // Hanya penerima disposisi yang bisa forward
        if ($disposisi->pengguna_id !== $user->id) {
            abort(403);
        }

        // Decode JSON dari Tagify jika dikirim sebagai string JSON
        if ($request->filled('pengguna_id') && is_string($request->pengguna_id)) {
            $decoded = json_decode($request->pengguna_id, true);
            if (is_array($decoded)) {
                $request->merge([
                    'pengguna_id' => collect($decoded)->pluck('value')->first(),
                ]);
            }
        }

        $validated = $request->validate([
            'pengguna_id' => 'required|integer|exists:users,id|different:' . $user->id,
            'keterangan' => 'nullable|string|max:500',
        ]);

        // Update status disposisi saat ini menjadi diteruskan
        $disposisi->update([
            'status' => 'diteruskan',
            'alasan' => $validated['keterangan'] ?? null,
        ]);

        // Update pivot surat_keluar_penerima sebagai dibaca
        if ($disposisi->surat_keluar_id) {
            $suratKeluar = $disposisi->suratKeluar;
            if ($suratKeluar) {
                $suratKeluar->penerima()->updateExistingPivot($user->id, [
                    'dibaca' => true,
                    'dibaca_at' => now(),
                ]);
            }
        }

        $newRecord = Disposisi::create([
            'surat_keluar_id' => $disposisi->surat_keluar_id,
            'pengguna_id' => $validated['pengguna_id'],
            'pengirim_id' => $user->id,
            'keterangan' => $validated['keterangan'] ?? $disposisi->keterangan,
            'status' => 'diteruskan',
            'dibaca' => false,
        ]);

        // Notifikasi Telegram ke penerima disposisi
        $telegram = app(TelegramNotificationService::class);
        $penerima = User::find($validated['pengguna_id']);
        if ($penerima) {
            $surat = $disposisi->suratKeluar;
            $telegram->notifyDisposisiMasuk(
                $penerima,
                $surat->nomor_surat ?? '-',
                $surat->perihal ?? '',
                $user->name,
                $validated['keterangan'] ?? $disposisi->keterangan ?? '',
                $user->name
            );
        }

        return redirect()->route('disposisi-masuk.show', $disposisi)
            ->with('success', 'Disposisi berhasil diteruskan');
    }

    /**
     * Mark incoming disposition as read
     */
    public function markAsReadMasuk(Disposisi $disposisi)
    {
        $user = auth()->user();
        
        if ($disposisi->pengguna_id !== $user->id) {
            abort(403);
        }

        $disposisi->update(['dibaca' => true]);
        return back()->with('success', 'Disposisi ditandai sudah dibaca');
    }

    /**
     * Mark incoming disposition as unread
     */
    public function markAsUnreadMasuk(Disposisi $disposisi)
    {
        $user = auth()->user();
        
        if ($disposisi->pengguna_id !== $user->id) {
            abort(403);
        }

        $disposisi->update(['dibaca' => false]);
        return redirect()->route('disposisi-masuk.index')->with('success', 'Disposisi ditandai belum dibaca');
    }

    /**
     * ════════════════════════════════════════════════════════
     * DISPOSISI KELUAR (Surat keluar yg saya forward ke orang lain)
     * ════════════════════════════════════════════════════════
     */

    /**
     * List outgoing dispositions — tampilkan semua (tidak filter belum dibaca).
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        
        $disposisi = Disposisi::where('surat_keluar_id', '!=', null)
            ->where('pengirim_id', $user->id)
            ->with(['suratKeluar', 'pengguna'])
            ->latest()
            ->get();
            
        return view('disposisi.index', compact('disposisi'));
    }

    /**
     * Show outgoing disposition detail
     */
    public function show(Disposisi $disposisi)
    {
        $user = auth()->user();

        // Hanya forwarder (pengirim_id) yang bisa lihat
        if ($disposisi->pengirim_id !== $user->id) {
            abort(403);
        }

        // Auto-mark as read saat dibuka
        if (!$disposisi->dibaca) {
            $disposisi->update(['dibaca' => true]);
        }

        $disposisi->load(['suratKeluar', 'pengirim', 'pengguna']);

        return view('disposisi.show', compact('disposisi'));
    }

    /**
     * Mark outgoing disposition as read (by forwarder)
     */
    public function markAsRead(Disposisi $disposisi)
    {
        $user = auth()->user();
        
        if ($disposisi->pengirim_id !== $user->id) {
            abort(403);
        }

        $disposisi->update(['dibaca' => true]);
        return back()->with('success', 'Disposisi ditandai sudah dibaca');
    }

    /**
     * Mark outgoing disposition as unread (by forwarder)
     */
    public function markAsUnread(Disposisi $disposisi)
    {
        $user = auth()->user();
        
        if ($disposisi->pengirim_id !== $user->id) {
            abort(403);
        }

        $disposisi->update(['dibaca' => false]);
        return back()->with('success', 'Disposisi ditandai belum dibaca');
    }

    /**
     * Update disposition status (recipient responding to forwarder)
     * 
     * - diterima → accept the letter (updates pivot as read)
     * - ditolak → reject the letter (updates pivot as read)
     * - diteruskan → forward to another user (creates new Disposisi records)
     */
    public function updateStatus(Request $request, Disposisi $disposisi)
    {
        $user = auth()->user();

        // Hanya penerima disposisi yang bisa update status
        if ($disposisi->pengguna_id !== $user->id) {
            abort(403);
        }

        // Tagify mengirim JSON string; decode, atau kosongkan jika tidak "diteruskan"
        if ($request->filled('pengguna_id') && is_string($request->pengguna_id)) {
            $decoded = json_decode($request->pengguna_id, true);
            if (is_array($decoded)) {
                $request->merge([
                    'pengguna_id' => collect($decoded)->pluck('value')->map(fn($id) => (int) $id)->toArray(),
                ]);
            }
        } elseif ($request->exists('pengguna_id') && (is_null($request->pengguna_id) || $request->pengguna_id === '')) {
            $request->merge(['pengguna_id' => []]);
        }

        $validated = $request->validate([
            'aksi' => 'required|in:diteruskan,diterima,ditolak,selesai',
            'pengguna_id' => 'required_if:aksi,diteruskan|array',
            'pengguna_id.*' => 'exists:users,id',
            'alasan' => 'nullable|string|max:500',
        ]);

        // Update current disposisi status & alasan
        $disposisi->update([
            'status' => $validated['aksi'],
            'alasan' => $validated['alasan'] ?? null,
        ]);

        // Update pivot surat_keluar_penerima
        if ($disposisi->surat_keluar_id) {
            $suratKeluar = $disposisi->suratKeluar;
            if ($suratKeluar) {
                $suratKeluar->penerima()->updateExistingPivot($user->id, [
                    'status' => $validated['aksi'],
                    'alasan' => $validated['alasan'] ?? null,
                    'dibaca' => true,
                    'dibaca_at' => now(),
                ]);
            }
        }

        // Jika diteruskan, buat disposisi baru ke user yang dipilih
        if ($validated['aksi'] === 'diteruskan' && !empty($validated['pengguna_id'])) {
            $telegram = app(TelegramNotificationService::class);
            $surat = $disposisi->suratKeluar;
            foreach ($validated['pengguna_id'] as $penggunaId) {
                if ($penggunaId == $user->id) continue;
                Disposisi::create([
                    'surat_keluar_id' => $disposisi->surat_keluar_id,
                    'pengguna_id' => $penggunaId,
                    'pengirim_id' => $user->id,
                    'keterangan' => $validated['alasan'] ?? $disposisi->keterangan,
                    'status' => 'diteruskan',
                    'dibaca' => false,
                ]);

                // Notifikasi Telegram
                $penerima = User::find($penggunaId);
                if ($penerima) {
                    $telegram->notifyDisposisiMasuk(
                        $penerima,
                        $surat->nomor_surat ?? '-',
                        $surat->perihal ?? '',
                        $user->name,
                        $validated['alasan'] ?? $disposisi->keterangan ?? '',
                        $user->name
                    );
                }
            }

            $count = count($validated['pengguna_id']);
            return redirect()->route('disposisi-masuk.show', $disposisi)
                ->with('success', "Disposisi berhasil diteruskan ke {$count} user");
        }

        $label = $validated['aksi'] === 'diterima' ? 'diterima' : 'ditolak';
        return redirect()->route('disposisi-masuk.show', $disposisi)
            ->with('success', "Disposisi berhasil {$label}");
    }

    /**
     * Delete disposition
     */
    public function destroy(Disposisi $disposisi)
    {
        $user = auth()->user();

        // Hanya yang forward atau superadmin yang bisa hapus
        if ($disposisi->pengirim_id !== $user->id && !$user->hasRole('superadmin')) {
            abort(403);
        }

        $disposisi->delete();
        return back()->with('success', 'Disposisi berhasil dihapus');
    }
}
