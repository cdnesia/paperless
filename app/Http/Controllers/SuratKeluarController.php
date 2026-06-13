<?php

namespace App\Http\Controllers;

use App\Models\PenerimaSurat;
use App\Models\SuratKeluar;
use App\Models\User;
use App\Services\GoogleDocsService;
use App\Services\TelegramNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SuratKeluarController extends Controller
{
    /**
     * Periksa error pada file upload dan kembalikan array pesan error yang readable.
     */
    protected function validateUploadErrors(Request $request): array
    {
        $errors = [];
        $fields = ['file_pdf', 'lampiran'];

        foreach ($fields as $field) {
            // Jangan pakai hasFile() karena return false saat file error upload
            $file = $request->file($field);

            if (!$file) {
                continue;
            }

            $errorCode = $file->getError();

            if ($errorCode === UPLOAD_ERR_OK) {
                continue;
            }

            $label = $field === 'file_pdf' ? 'Dokumen Surat (PDF)' : 'Lampiran';

            $errors[$field] = match ($errorCode) {
                UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE =>
                    "{$label} terlalu besar. Maksimal ukuran file adalah 10 MB.",
                UPLOAD_ERR_PARTIAL =>
                    "{$label} hanya terunggah sebagian. Silakan coba lagi.",
                UPLOAD_ERR_NO_FILE =>
                    "{$label} tidak ditemukan. Silakan pilih file terlebih dahulu.",
                default =>
                    "{$label} gagal diunggah (kode error: {$errorCode}). Silakan coba lagi.",
            };
        }

        return $errors;
    }

    public function index()
    {
        $user = Auth::guard('web')->user();
        $suratKeluars = SuratKeluar::query()
            ->with('penerima')
            ->when(!$user->hasRole('superadmin'), function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })->get();
        return view('surat-keluar.index', compact('suratKeluars'));
    }

    public function create()
    {
        $metode = request('metode', 'upload');
        $users = User::select('id', 'name', 'email')
            ->whereNotIn('id', [1, auth()->id()])
            ->get()->map(function ($user) {
            return [
                'value' => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'avatar' => asset('assets/images/avatar/avatar1.webp'),
            ];
        });
        return view('surat-keluar.create', compact('metode', 'users'));
    }

    public function store(Request $request)
    {
        $rawTujuan = $request->input('tujuan');

        if ($request->filled('tujuan') && is_string($rawTujuan)) {
            $request->merge([
                'tujuan' => json_decode($rawTujuan, true)
            ]);
        }

        $rules = [
            'nomor_surat' => 'nullable|unique:surat_keluars',
            'perihal' => 'required|string|max:255',
            'tujuan' => 'nullable|array',
            'jenis_surat' => 'required|in:internal,eksternal,broadcast',
            'metode_surat' => 'required|in:upload,gdocs',
            'tanggal_surat' => 'required|date',
            'lampiran' => 'nullable|mimes:pdf|max:10240',
            'status' => 'nullable|in:d,r,a,s,e',
            'sent_at' => 'nullable|date',
        ];

        if ($request->metode_surat === 'gdocs') {
            $rules['file_pdf'] = 'nullable';
            $rules['google_doc_id'] = 'nullable';
        } else {
            $rules['file_pdf'] = 'required|mimes:pdf|max:10240';
            $rules['google_doc_id'] = 'nullable';
        }

        // Cek error upload (file terlalu besar, dll) sebelum validasi
        $uploadErrors = $this->validateUploadErrors($request);
        if (!empty($uploadErrors)) {
            return redirect()->back()
                ->withInput()
                ->withErrors($uploadErrors);
        }

        $validated = $request->validate($rules, [
            'file_pdf.max' => 'Ukuran file PDF maksimal 10 MB.',
            'lampiran.max' => 'Ukuran file lampiran maksimal 10 MB.',
            'file_pdf.mimes' => 'Dokumen surat harus berformat PDF.',
            'lampiran.mimes' => 'Lampiran harus berformat PDF.',
        ]);

        if ($request->hasFile('file_pdf')) {
            $validated['file_pdf'] = $request->file('file_pdf')->store('surat-keluar/pdf', 'public');
        }
        if ($request->hasFile('lampiran')) {
            $validated['lampiran'] = $request->file('lampiran')->store('surat-keluar/lampiran', 'public');
        }

        if ($request->metode_surat === 'upload' && $request->hasFile('file_pdf')) {
            $validated['status'] = 'a';
        }

        $validated['user_id'] = Auth::guard('web')->id();

        $tujuanData = collect($request->tujuan)
            ->pluck('value')
            ->map(fn($id) => (int) $id)
            ->toArray() ?? [];

        unset($validated['tujuan']);

        $suratKeluar = DB::transaction(function () use ($validated, $tujuanData) {
            $suratKeluar = SuratKeluar::create($validated);

            if (!empty($tujuanData) && is_array($tujuanData)) {
                foreach ($tujuanData as $userId) {
                    PenerimaSurat::create([
                        'surat_keluar_id' => $suratKeluar->id,
                        'user_id' => $userId,
                    ]);
                }
            }

            $suratKeluar->logHistory('created', 'Surat keluar dibuat');

            return $suratKeluar;
        });

        if ($request->metode_surat === 'gdocs') {
            try {
                $googleDocs = app(GoogleDocsService::class);
                $title = 'Surat Keluar - ' . $validated['perihal'];

                $documentId = $googleDocs->createDocumentFromTemplate($title);
                $suratKeluar->update(['google_doc_id' => $documentId]);

                $googleDocs->shareDocument($documentId);

                $suratKeluar->logHistory('gdocs_created', 'Dokumen Google Docs berhasil dibuat', [
                    'document_id' => $documentId,
                ]);
            } catch (\Exception $e) {
                Log::error('Gagal membuat Google Docs: ' . $e->getMessage(), [
                    'surat_keluar_id' => $suratKeluar->id,
                ]);
                $suratKeluar->logHistory('gdocs_error', 'Gagal membuat Google Docs: ' . $e->getMessage());
            }
        } elseif ($request->metode_surat === 'upload' && $request->hasFile('file_pdf')) {
            $oldPath = $suratKeluar->file_pdf;
            $newPath = 'surat-keluar/pdf/' . $suratKeluar->id . '.pdf';
            $fullOldPath = storage_path('app/public/' . $oldPath);
            $fullNewPath = storage_path('app/public/' . $newPath);

            $dir = dirname($fullNewPath);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            if (file_exists($fullOldPath)) {
                rename($fullOldPath, $fullNewPath);
                $suratKeluar->update(['file_pdf' => $newPath]);
            }

            $suratKeluar->logHistory('status_changed', 'Status berubah ke Siap Dikirim (Upload PDF)');
        }

        if ($request->action === 'final') {
            $suratKeluar->update(['status' => 'a']);
            $suratKeluar->logHistory('status_changed', 'Status berubah ke Siap Dikirim (Final Google Docs)');
        }

        if ($suratKeluar->status === 's' && is_null($suratKeluar->sent_at)) {
            $suratKeluar->update(['sent_at' => now()]);
        }

        if ($request->metode_surat === 'gdocs') {
            return redirect()->route('surat-keluar.edit', $suratKeluar)
                ->with('success', 'Surat berhasil dibuat. Silakan edit dokumen Google Docs.');
        }

        return redirect()->route('surat-keluar.index')->with('success', 'Surat keluar berhasil ditambahkan');
    }

    public function show(SuratKeluar $suratKeluar)
    {
        if (!$suratKeluar->dibaca) {
            $suratKeluar->update(['dibaca' => true]);
            $suratKeluar->logHistory('read', 'Surat dibaca oleh ' . Auth::guard('web')->user()->name);
        }

        $suratKeluar->load(['histories.user', 'penerima']);

        return view('surat-keluar.show', compact('suratKeluar'));
    }

    public function edit(SuratKeluar $suratKeluar)
    {
        if (in_array($suratKeluar->status, ['s', 'e'])) {
            return redirect()->route('surat-keluar.show', $suratKeluar)
                ->with('error', 'Surat yang sudah terkirim atau diarsipkan tidak dapat diedit.');
        }

        $suratKeluar->load('penerima');

        $view = $suratKeluar->google_doc_id ? 'surat-keluar.edit-gdocs' : 'surat-keluar.edit';

        $users = User::select('id', 'name', 'email')
            ->whereNotIn('id', [1, auth()->id()])
            ->get()->map(function ($user) {
            return [
                'value' => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'avatar' => asset('assets/images/avatar/avatar1.webp'),
            ];
        });

        return view($view, compact('suratKeluar', 'users'));
    }

    public function update(Request $request, SuratKeluar $suratKeluar)
    {
        if (in_array($suratKeluar->status, ['s', 'e'])) {
            return redirect()->route('surat-keluar.show', $suratKeluar)
                ->with('error', 'Surat yang sudah terkirim atau diarsipkan tidak dapat diubah.');
        }

        if ($request->filled('tujuan')) {
            $request->merge([
                'tujuan' => json_decode($request->tujuan, true)
            ]);
        }

        $rules = [
            'nomor_surat' => 'nullable|unique:surat_keluars,nomor_surat,' . $suratKeluar->id,
            'perihal' => 'required|string|max:255',
            'tujuan' => 'nullable|array',
            'jenis_surat' => 'required|in:internal,eksternal,broadcast',
            'tanggal_surat' => 'nullable|date',
            'lampiran' => 'nullable|mimes:pdf|max:10240',
            'status' => 'nullable|in:d,r,a,s,e',
            'sent_at' => 'nullable|date',
        ];

        // Always use existing metode_surat, cannot be changed via form
        // Cek error upload (file terlalu besar, dll) sebelum validasi
        $uploadErrors = $this->validateUploadErrors($request);
        if (!empty($uploadErrors)) {
            return redirect()->back()
                ->withInput()
                ->withErrors($uploadErrors);
        }

        $validated = $request->validate($rules);
        $validated['metode_surat'] = $suratKeluar->metode_surat;

        // Conditional based on existing metode_surat
        if ($suratKeluar->metode_surat === 'gdocs') {
            $rules['file_pdf'] = 'nullable';
            $rules['google_doc_id'] = 'nullable';
        } else {
            $rules['file_pdf'] = 'nullable|mimes:pdf|max:10240';
            $rules['google_doc_id'] = 'nullable';
        }

        $validated = $request->validate($rules, [
            'file_pdf.max' => 'Ukuran file PDF maksimal 10 MB.',
            'lampiran.max' => 'Ukuran file lampiran maksimal 10 MB.',
            'file_pdf.mimes' => 'Dokumen surat harus berformat PDF.',
            'lampiran.mimes' => 'Lampiran harus berformat PDF.',
        ]);

        // Track what changed
        $changes = [];
        $oldStatus = $suratKeluar->status;
        $oldMetode = $suratKeluar->file_pdf ? 'upload' : ($suratKeluar->google_doc_id ? 'gdocs' : null);
        $newMetode = $request->metode_surat;

        // Handle metode change - if switching, clean up old data
        if ($oldMetode && $oldMetode !== $newMetode) {
            if ($newMetode === 'gdocs') {
                // Switching from upload to gdocs: delete old PDF
                if ($suratKeluar->file_pdf) {
                    Storage::disk('public')->delete($suratKeluar->file_pdf);
                }
                $validated['file_pdf'] = null;
                $changes[] = 'beralih ke Google Docs';
            } else {
                // Switching from gdocs to upload: clear google_doc_id
                $validated['google_doc_id'] = null;
                $changes[] = 'beralih ke upload PDF';
            }
        } elseif ($newMetode === 'gdocs') {
            // Stay in gdocs mode - google_doc_id might be sent from editor form
            if ($request->filled('google_doc_id')) {
                $validated['google_doc_id'] = $request->google_doc_id;
            } else {
                unset($validated['google_doc_id']);
            }
        }

        // Handle file uploads - hapus file lama jika diganti
        if ($request->hasFile('file_pdf')) {
            if ($suratKeluar->file_pdf) {
                Storage::disk('public')->delete($suratKeluar->file_pdf);
            }
            // Simpan sementara, nanti di-rename setelah update
            $validated['file_pdf'] = $request->file('file_pdf')->store('surat-keluar/pdf', 'public');
            $changes[] = 'file surat';
        } elseif ($newMetode !== 'gdocs') {
            // Only unset if staying in upload mode but no new file
            unset($validated['file_pdf']);
        }

        if ($request->hasFile('lampiran')) {
            if ($suratKeluar->lampiran) {
                Storage::disk('public')->delete($suratKeluar->lampiran);
            }
            $validated['lampiran'] = $request->file('lampiran')->store('surat-keluar/lampiran', 'public');
            $changes[] = 'lampiran';
        } else {
            unset($validated['lampiran']);
        }

        // Extract tujuan untuk PenerimaSurat sebelum update SuratKeluar
        $tujuanData = collect($validated['tujuan'] ?? [])
            ->pluck('value')
            ->map(fn($id) => (int) $id)
            ->toArray();
        unset($validated['tujuan']);

        $suratKeluar = DB::transaction(function () use ($suratKeluar, $validated, $tujuanData, $changes) {
            $suratKeluar->update($validated);
            $suratKeluar->touch();
            $suratKeluar->penerima()->sync($tujuanData);

            return $suratKeluar;
        });

        if (!empty($tujuanData)) {
            $changes[] = 'penerima';
        }

        // Rename uploaded PDF to ID-based filename (for upload mode)
        if ($request->hasFile('file_pdf') && $newMetode === 'upload' && $suratKeluar->file_pdf) {
            $oldPath = $suratKeluar->file_pdf;
            $newPath = 'surat-keluar/pdf/' . $suratKeluar->id . '.pdf';

            if ($oldPath !== $newPath) {
                $fullOldPath = storage_path('app/public/' . $oldPath);
                $fullNewPath = storage_path('app/public/' . $newPath);

                $dir = dirname($fullNewPath);
                if (!is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }

                if (file_exists($fullOldPath)) {
                    rename($fullOldPath, $fullNewPath);
                    $suratKeluar->update(['file_pdf' => $newPath]);
                }
            }
        }

        // Handle action from editor page
        if ($request->action === 'final') {
            $suratKeluar->update(['status' => 'a']);
            $suratKeluar->logHistory('status_changed', 'Status berubah ke Siap Dikirim (final)');

            // Export ke PDF dan cleanup Google Docs
            $this->finalizeSurat($suratKeluar);
        }

        // Auto-fill sent_at when status changes to terkirim
        if ($suratKeluar->status === 's' && is_null($suratKeluar->sent_at)) {
            $suratKeluar->update(['sent_at' => now()]);
        }

        // Log status change separately
        if ($oldStatus != $suratKeluar->status) {
            $suratKeluar->logHistory('status_changed', "Status berubah dari {$oldStatus} ke {$suratKeluar->status}", [
                'old_status' => $oldStatus,
                'new_status' => $suratKeluar->status,
            ]);
        }

        // Log general update
        $keterangan = 'Data surat diperbarui';
        if (!empty($changes)) {
            $keterangan .= ' (' . implode(', ', $changes) . ')';
        }
        $suratKeluar->logHistory('updated', $keterangan);

        // Redirect based on action
        if ($request->action === 'draft') {
            return redirect()->route('surat-keluar.show', $suratKeluar)
                ->with('success', 'Draft berhasil disimpan');
        }

        if ($request->action === 'final') {
            return redirect()->route('surat-keluar.show', $suratKeluar)
                ->with('success', 'Surat final berhasil disimpan');
        }

        return redirect()->route('surat-keluar.show', $suratKeluar)->with('success', 'Surat keluar berhasil diperbarui');
    }

    /**
     * Finalisasi surat: export ke PDF, simpan dengan nama ID, cleanup Google Docs.
     */
    protected function finalizeSurat(SuratKeluar $suratKeluar): void
    {
        $pdfPath = 'surat-keluar/pdf/' . $suratKeluar->id . '.pdf';

        if ($suratKeluar->metode_surat === 'gdocs' && $suratKeluar->google_doc_id) {
            // Export Google Docs ke PDF
            try {
                $googleDocs = app(GoogleDocsService::class);
                $googleDocs->exportToPdf($suratKeluar->google_doc_id, $pdfPath);

                // Hapus Google Docs dari Drive
                $googleDocs->deleteDocument($suratKeluar->google_doc_id);

                // Update record: simpan file_pdf, hapus google_doc_id
                $suratKeluar->update([
                    'file_pdf' => $pdfPath,
                    'google_doc_id' => null,
                ]);

                $suratKeluar->logHistory('updated', 'Google Docs di-export ke PDF dan dihapus dari Drive');
            } catch (\Exception $e) {
                Log::error('Gagal finalisasi Google Docs: ' . $e->getMessage(), [
                    'surat_keluar_id' => $suratKeluar->id,
                ]);
                $suratKeluar->logHistory('gdocs_error', 'Gagal export PDF: ' . $e->getMessage());
            }
        } elseif ($suratKeluar->metode_surat === 'upload' && $suratKeluar->file_pdf) {
            // Rename file upload ke format ID surat
            $oldPath = $suratKeluar->file_pdf;

            // Cek apakah file sudah di path yang benar
            if ($oldPath !== $pdfPath) {
                $fullOldPath = storage_path('app/public/' . $oldPath);
                $fullNewPath = storage_path('app/public/' . $pdfPath);

                if (file_exists($fullOldPath)) {
                    $dir = dirname($fullNewPath);
                    if (!is_dir($dir)) {
                        mkdir($dir, 0755, true);
                    }

                    rename($fullOldPath, $fullNewPath);

                    $suratKeluar->update(['file_pdf' => $pdfPath]);
                    $suratKeluar->logHistory('updated', 'File PDF di-rename ke format ID surat');
                }
            }
        }
    }

    public function destroy(Request $request, SuratKeluar $suratKeluar)
    {
        if (in_array($suratKeluar->status, ['s', 'e'])) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Surat yang sudah terkirim atau diarsipkan tidak bisa dihapus'
                ], 422);
            }
            return redirect()->route('surat-keluar.index')
                ->with('error', 'Surat yang sudah terkirim atau diarsipkan tidak bisa dihapus');
        }
        // Hapus file tanda tangan digital terkait
        $tandaTanganList = $suratKeluar->tandaTanganDigital;
        foreach ($tandaTanganList as $signature) {
            $signature->deleteFiles();
        }

        // Hapus file terkait surat
        if ($suratKeluar->file_pdf) {
            Storage::disk('public')->delete($suratKeluar->file_pdf);
        }
        if ($suratKeluar->lampiran) {
            Storage::disk('public')->delete($suratKeluar->lampiran);
        }

        // Hapus Google Docs jika ada
        if ($suratKeluar->google_doc_id) {
            try {
                $googleDocs = app(GoogleDocsService::class);
                $googleDocs->deleteDocument($suratKeluar->google_doc_id);
            } catch (\Exception $e) {
                Log::warning('Gagal hapus Google Docs: ' . $e->getMessage());
            }
        }

        $suratKeluar->logHistory('deleted', 'Surat keluar dihapus oleh ' . auth()->user()->name);

        $suratKeluar->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Surat keluar berhasil dihapus'
            ]);
        }

        return redirect()->route('surat-keluar.index')->with('success', 'Surat keluar berhasil dihapus');
    }

    public function markAsRead(SuratKeluar $suratKeluar)
    {
        $suratKeluar->update(['dibaca' => true]);
        $suratKeluar->logHistory('read', 'Ditandai sudah dibaca oleh ' . auth()->user()->name);
        return back()->with('success', 'Surat ditandai sebagai sudah dibaca');
    }

    public function markAsUnread(SuratKeluar $suratKeluar)
    {
        $suratKeluar->update(['dibaca' => false]);
        $suratKeluar->logHistory('unread', 'Ditandai belum dibaca oleh ' . auth()->user()->name);
        return back()->with('success', 'Surat ditandai sebagai belum dibaca');
    }

    public function send(SuratKeluar $suratKeluar)
    {
        if ($suratKeluar->status === 's') {
            return redirect()->route('surat-keluar.show', $suratKeluar)
                ->with('error', 'Surat ini sudah terkirim.');
        }

        if ($suratKeluar->status !== 'a') {
            return redirect()->route('surat-keluar.show', $suratKeluar)
                ->with('error', 'Hanya surat dengan status Siap Dikirim yang dapat dikirim.');
        }

        // Pastikan ada penerima di pivot table
        $penerimaCount = $suratKeluar->penerima()->count();
        if ($penerimaCount === 0) {
            return redirect()->back()
                ->with('error', 'Surat tidak bisa dikirim karena belum memiliki penerima (tujuan). Silakan isi tujuan terlebih dahulu.');
        }

        // Replace PDF asli dengan PDF final yang sudah ditandatangani
        $latestSignature = $suratKeluar->tandaTanganDigital()->latest()->first();
        if ($latestSignature && $latestSignature->file_pdf_final && $suratKeluar->file_pdf) {
            $finalFullPath = Storage::disk('public')->path($latestSignature->file_pdf_final);
            if (file_exists($finalFullPath)) {
                // Copy file final menimpa file asli (nama file tetap sama)
                $originalFullPath = Storage::disk('public')->path($suratKeluar->file_pdf);
                copy($finalFullPath, $originalFullPath);

                // Hapus file PDF final — tidak perlu double, verifikasi cukup dari suratKeluar->file_pdf
                Storage::disk('public')->delete($latestSignature->file_pdf_final);
                $latestSignature->update(['file_pdf_final' => null]);
            }
        }

        $suratKeluar->update([
            'status' => 's',
            'sent_at' => now(),
        ]);

        $suratKeluar->logHistory('status_changed', 'Surat dikirim ke tujuan');

        // Kirim notifikasi Telegram ke semua penerima
        $telegram = app(TelegramNotificationService::class);
        $pengirim = auth()->user()->name;
        foreach ($suratKeluar->penerima as $penerima) {
            $telegram->notifySuratMasuk(
                $penerima,
                $suratKeluar->nomor_surat ?? '-',
                $suratKeluar->perihal,
                $pengirim
            );
        }

        return redirect()->route('surat-keluar.show', $suratKeluar)
            ->with('success', 'Surat berhasil dikirim');
    }

    public function tambahHistory(Request $request, SuratKeluar $suratKeluar)
    {
        $validated = $request->validate([
            'action' => 'required|string|max:50',
            'keterangan' => 'required|string|max:500',
        ]);

        $suratKeluar->logHistory($validated['action'], $validated['keterangan']);

        return redirect()->route('surat-keluar.show', $suratKeluar)
            ->with('success', 'Riwayat berhasil ditambahkan');
    }

    public function disposisi(Request $request, SuratKeluar $suratKeluar)
    {
        $validated = $request->validate([
            'pengguna_id' => 'required|exists:users,id',
            'keterangan' => 'nullable|string|max:500',
        ]);

        $disposisi = \App\Models\Disposisi::create([
            'surat_keluar_id' => $suratKeluar->id,
            'pengguna_id' => $validated['pengguna_id'],
            'pengirim_id' => auth()->id(),
            'keterangan' => $validated['keterangan'],
            'status' => 'diteruskan',
        ]);

        $penerima = \App\Models\User::find($validated['pengguna_id']);
        $suratKeluar->logHistory('disposisi', 'Surat didisposisikan ke ' . ($penerima->name ?? 'User #' . $validated['pengguna_id']));

        return redirect()->route('surat-keluar.show', $suratKeluar)
            ->with('success', 'Surat berhasil didisposisikan');
    }
}
