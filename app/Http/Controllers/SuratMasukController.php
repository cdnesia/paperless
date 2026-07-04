<?php

namespace App\Http\Controllers;

use App\Models\SuratKeluar;
use App\Models\Disposisi;
use App\Models\User;
use App\Services\TelegramNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * SuratMasukController - Manages Inbox
 * 
 * Menampilkan Surat Keluar yang dikirim ke user ini
 * (dari pivot surat_keluar_penerima).
 */
class SuratMasukController extends Controller
{
    /**
     * Get inbox: Surat Keluar yg dikirim ke user ini (via pivot surat_keluar_penerima)
     */
    private function getInbox($filterDibaca = null)
    {
        $user = Auth::guard('web')->user();

        $suratMasuk = $user->suratKeluarPenerima()
            ->where('surat_keluars.status', 's')
            ->with('user')
            ->latest('surat_keluar_penerima.created_at')
            ->get()
            ->when($filterDibaca !== null, function ($collection) use ($filterDibaca) {
                return $collection->filter(function ($item) use ($filterDibaca) {
                    return (bool) $item->pivot->dibaca === $filterDibaca;
                });
            })
            ->map(function ($suratKeluar) {
                return (object) [
                    'surat_id' => $suratKeluar->id,
                    'nomor_surat' => $suratKeluar->nomor_surat ?? '-',
                    'perihal' => $suratKeluar->perihal,
                    'pengirim' => $suratKeluar->user->name ?? 'System',
                    'dari_user' => $suratKeluar->user->name ?? 'System',
                    'tanggal' => $suratKeluar->sent_at ?? $suratKeluar->created_at,
                    'dibaca' => (bool) $suratKeluar->pivot->dibaca,
                    'created_at' => $suratKeluar->created_at,
                    'show_route' => route('surat-masuk.show', $suratKeluar),
                ];
            });

        return $suratMasuk;
    }

    /**
     * Display incoming letters - default: unread only, ?semua=1 to show all
     */
    public function index(Request $request)
    {
        $semua = $request->boolean('semua');
        $suratMasuk = $this->getInbox($semua ? null : false);
        return view('surat-masuk.index', compact('suratMasuk', 'semua'));
    }

    /**
     * Show incoming Surat Keluar detail (surat keluar yg dikirim ke user ini)
     */
    public function show(SuratKeluar $suratKeluar)
    {
        $user = Auth::guard('web')->user();

        $penerima = $suratKeluar->penerima()->where('user_id', $user->id)->first();
        if (!$penerima) {
            abort(403, 'Anda tidak memiliki akses ke surat ini');
        }

        if (!$penerima->pivot->dibaca) {
            $suratKeluar->penerima()->updateExistingPivot($user->id, [
                'dibaca' => true,
                'dibaca_at' => now(),
            ]);
        }

        $dibaca = true;
        $pivotStatus = $penerima->pivot->status;

        $suratKeluar->load('user', 'histories.user');

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

        return view('surat-masuk.show-surat-keluar', compact('suratKeluar', 'dibaca', 'pivotStatus', 'users'));
    }

    /**
     * Mark incoming Surat Keluar as read (update pivot)
     */
    public function markAsRead(SuratKeluar $suratKeluar)
    {
        $user = Auth::guard('web')->user();
        $suratKeluar->penerima()->updateExistingPivot($user->id, [
            'dibaca' => true,
            'dibaca_at' => now(),
        ]);
        return back()->with('success', 'Surat ditandai sebagai sudah dibaca');
    }

    /**
     * Mark incoming Surat Keluar as unread (update pivot)
     */
    public function markAsUnread(SuratKeluar $suratKeluar)
    {
        $user = Auth::guard('web')->user();
        $suratKeluar->penerima()->updateExistingPivot($user->id, [
            'dibaca' => false,
            'dibaca_at' => null,
        ]);
        return redirect()->route('surat-masuk.index')->with('success', 'Surat ditandai sebagai belum dibaca');
    }

    /**
     * Update status penerima surat: diterima / ditolak / diteruskan
     */
    public function updateStatus(Request $request, SuratKeluar $suratKeluar)
    {
        $user = Auth::guard('web')->user();

        $penerima = $suratKeluar->penerima()->where('user_id', $user->id)->first();
        if (!$penerima) {
            abort(403, 'Anda tidak memiliki akses ke surat ini');
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
            'aksi' => 'required|in:diterima,ditolak,diteruskan',
            'pengguna_id' => 'required_if:aksi,diteruskan|array',
            'pengguna_id.*' => 'exists:users,id',
            'alasan' => 'nullable|string|max:500',
        ]);

        // Update pivot status
        $suratKeluar->penerima()->updateExistingPivot($user->id, [
            'status' => $validated['aksi'],
            'alasan' => $validated['alasan'] ?? null,
            'dibaca' => true,
            'dibaca_at' => now(),
        ]);

        // Jika diteruskan, buat disposisi ke user yang dipilih
        if ($validated['aksi'] === 'diteruskan') {
            foreach ($validated['pengguna_id'] as $penggunaId) {
                Disposisi::firstOrCreate([
                    'surat_keluar_id' => $suratKeluar->id,
                    'pengguna_id' => $penggunaId,
                    'pengirim_id' => $user->id,
                ], [
                    'keterangan' => $validated['alasan'] ?? 'Diteruskan',
                    'status' => 'diteruskan',
                ]);

                // Notifikasi Telegram ke penerima disposisi
                $penerima = User::find($penggunaId);
                if ($penerima) {
                    $telegram = app(TelegramNotificationService::class);
                    $telegram->notifyDisposisiMasuk(
                        $penerima,
                        $suratKeluar->nomor_surat ?? '-',
                        $suratKeluar->perihal,
                        $user->name,
                        $validated['alasan'] ?? 'Diteruskan',
                        $user->name
                    );
                }
            }

            $count = count($validated['pengguna_id']);
            return redirect()->route('surat-masuk.show', $suratKeluar)
                ->with('success', "Surat berhasil didisposisikan ke {$count} user");
        }

        $label = $validated['aksi'] === 'diterima' ? 'diterima' : 'ditolak';
        return redirect()->route('surat-masuk.show', $suratKeluar)
            ->with('success', "Surat berhasil {$label}");
    }


}
