<?php

namespace App\Http\Controllers;

use App\Models\SuratKeluar;
use App\Models\TandaTanganDigital;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TandaTanganDigitalController extends Controller
{
    /**
     * Tampilkan halaman tanda tangan digital untuk surat tertentu.
     */
    public function index(SuratKeluar $suratKeluar)
    {
        $signatures = $suratKeluar->tandaTanganDigital()
            ->with('user')
            ->latest()
            ->get();

        $hashOriginal = null;
        $filePath = $suratKeluar->file_pdf
            ? Storage::disk('public')->path($suratKeluar->file_pdf)
            : null;

        if ($suratKeluar->file_pdf && $filePath && file_exists($filePath)) {
            $hashOriginal = hash_file('sha256', $filePath);
        }

        return view('tanda-tangan-digital.index', compact(
            'suratKeluar',
            'signatures',
            'hashOriginal'
        ));
    }

    /**
     * Proses tanda tangan digital.
     */
    public function sign(Request $request, SuratKeluar $suratKeluar)
    {
        // 1. Hash SHA256 file PDF asli
        if (!$suratKeluar->file_pdf) {
            return back()->with('error', 'File PDF surat tidak ditemukan.');
        }
        $filePath = Storage::disk('public')->path($suratKeluar->file_pdf);
        if (!file_exists($filePath)) {
            return back()->with('error', 'File PDF surat tidak ditemukan.');
        }
        $hashOriginal = hash_file('sha256', $filePath);

        // 2. Simpan dulu ke DB (qr_code & file_pdf_final diisi nanti)
        $signedAt = $request->filled('signed_at')
            ? $request->signed_at
            : now();

        $signature = TandaTanganDigital::create([
            'surat_keluar_id'      => $suratKeluar->id,
            'user_id'              => Auth::id(),
            'hash_sha256_original' => $hashOriginal,
            'penandatangan'        => Auth::user()->name,
            'lokasi'               => $request->lokasi ?? 'Jambi',
            'ip_address'           => $request->ip(),
            'signed_at'            => $signedAt,
        ]);

        // 4. Generate QR Code (isi: URL verifikasi)
        $verifyUrl = route('tanda-tangan-digital.verify', $signature);

        $qrPath = 'tanda-tangan/qr-' . $suratKeluar->id . '-' . time() . '.png';
        $qrFullPath = Storage::disk('public')->path($qrPath);
        $qrDir = dirname($qrFullPath);
        if (!is_dir($qrDir)) {
            mkdir($qrDir, 0755, true);
        }

        try {
            \SimpleSoftwareIO\QrCode\Facades\QrCode::format('png')
                ->size(300)
                ->margin(1)
                ->generate($verifyUrl, $qrFullPath);
        } catch (\Exception $e) {
            $qrUrl = 'https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=' . urlencode($verifyUrl) . '&choe=UTF-8';
            copy($qrUrl, $qrFullPath);
        }

        // 5. Buat PDF final: tempel QR Code & teks verifikasi ke PDF asli
        $pdfFinalPath = 'tanda-tangan/draft-final-' . $suratKeluar->id . '-' . time() . '.pdf';
        $pdfFinalFullPath = Storage::disk('public')->path($pdfFinalPath);

        $this->stampPdf($filePath, $pdfFinalFullPath, $qrFullPath, $verifyUrl, $signature->lokasi ?? 'Jambi', $signature->signed_at);

        // 6. Hash SHA256 file final
        $hashFinal = hash_file('sha256', $pdfFinalFullPath);

        // 7. Update signature dengan QR, PDF final & hash final
        $signature->update([
            'hash_sha256_final' => $hashFinal,
            'qr_code'           => $qrPath,
            'file_pdf_final'    => $pdfFinalPath,
        ]);

        // Catat history
        $suratKeluar->logHistory('signed', 'Ditandatangan secara digital oleh ' . Auth::user()->name, [
            'signature_id'  => $signature->id,
            'hash_original' => $hashOriginal,
            'hash_final'    => $hashFinal,
        ]);

        return redirect()->route('tanda-tangan-digital.index', $suratKeluar)
            ->with('success', 'Surat berhasil ditandatangani secara digital.');
    }

    /**
     * Tempel gambar tanda tangan & QR code ke PDF asli menggunakan FPDI.
     */
    private function stampPdf(string $inputPath, string $outputPath, string $qrPath, string $verifyUrl, ?string $lokasi = null, $signedAt = null): void
    {
        try {
            $fpdi = new \setasign\Fpdi\Fpdi('P', 'mm', 'A4');
            $pageCount = $fpdi->setSourceFile($inputPath);

            for ($i = 1; $i <= $pageCount; $i++) {
                $templateId = $fpdi->importPage($i);
                $size = $fpdi->getTemplateSize($templateId);
                $orientation = ($size['width'] > $size['height']) ? 'L' : 'P';
                $fpdi->AddPage($orientation, [$size['width'], $size['height']]);
                $fpdi->useTemplate($templateId);

                // Tempel di halaman TERAKHIR saja
                if ($i === $pageCount) {
                    // QR Code (pojok kanan bawah)
                    if (file_exists($qrPath)) {
                        $qrSize = 30;
                        $qrX = $size['width'] - $qrSize - 35;
                        $qrY = $size['height'] - $qrSize - 30;

                        // Teks lokasi & tanggal di ATAS QR
                        $fpdi->SetFont('Arial', '', 11);
                        $fpdi->SetTextColor(0, 0, 0);
                        $lokasiTeks = $lokasi ?? 'Jambi';
                        $tgl = $signedAt ? \Illuminate\Support\Carbon::parse($signedAt)->translatedFormat('d F Y') : now()->translatedFormat('d F Y');
                        $teksLokasi = $lokasiTeks . ', ' . $tgl;
                        $teksLW = $fpdi->GetStringWidth($teksLokasi);
                        $fpdi->Text($qrX + ($qrSize - $teksLW) / 2, $qrY - 3, $teksLokasi);

                        $fpdi->Image($qrPath, $qrX, $qrY, $qrSize, $qrSize);

                        // Teks "Ditandatangani secara digital" di bawah QR (center)
                        $fpdi->SetFont('Arial', '', 12);
                        $fpdi->SetTextColor(0, 0, 0);
                        $teks = 'Ditandatangani secara digital';
                        $teksW = $fpdi->GetStringWidth($teks);
                        $fpdi->Text($qrX + ($qrSize - $teksW) / 2, $qrY + $qrSize + 5, $teks);
                    }

                    // Footer: tautan verifikasi keaslian
                    $fpdi->SetFont('Arial', '', 10);
                    $fpdi->SetTextColor(100, 100, 100);
                    $footerText = 'Keaslian dokumen ini dapat diverifikasi di: ' . route('verifikasi');
                    $fw = $fpdi->GetStringWidth($footerText);
                    $fpdi->Text(($size['width'] - $fw) / 2, $size['height'] - 12, $footerText);
                }
            }

            $fpdi->Output('F', $outputPath);
        } catch (\Exception $e) {
            // Fallback: copy file asli jika gagal stamp
            copy($inputPath, $outputPath);
            \Illuminate\Support\Facades\Log::warning('Gagal stempel PDF: ' . $e->getMessage());
        }
    }

    /**
     * Halaman verifikasi publik.
     */
    public function verify(TandaTanganDigital $tandaTanganDigital)
    {
        return view('tanda-tangan-digital.verify-public', compact(
            'tandaTanganDigital'
        ));
    }

    /**
     * Verifikasi dengan upload file — user upload PDF lalu dicocokkan hash-nya.
     */
    public function verifyUpload(Request $request, TandaTanganDigital $tandaTanganDigital)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:pdf', 'max:10240'],
        ]);

        $uploadedFile = $request->file('file');
        $hashUpload = hash_file('sha256', $uploadedFile->getRealPath());

        // Cocokkan dengan hash_final (prioritas) atau hash_original
        $hashPembanding = $tandaTanganDigital->hash_sha256_final
            ?? $tandaTanganDigital->hash_sha256_original;

        $uploadValid = $hashUpload === $hashPembanding;

        return view('tanda-tangan-digital.verify-public', [
            'tandaTanganDigital' => $tandaTanganDigital,
            'uploadHash'         => $hashUpload,
            'uploadValid'        => $uploadValid,
        ]);
    }

    /**
     * Halaman verifikasi mandiri — tanpa ID, user tinggal upload file PDF.
     */
    public function verifyByUpload()
    {
        return view('tanda-tangan-digital.verify-by-upload');
    }

    /**
     * Proses verifikasi mandiri: upload PDF → hash → cari di DB → tampilkan hasil.
     */
    public function verifyByUploadPost(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:pdf', 'max:10240'],
        ]);

        $uploadedFile = $request->file('file');
        $hashUpload = hash_file('sha256', $uploadedFile->getRealPath());

        // Cari tanda tangan digital yang cocok (hash_final atau hash_original)
        $tandaTanganDigital = TandaTanganDigital::where('hash_sha256_final', $hashUpload)
            ->orWhere('hash_sha256_original', $hashUpload)
            ->with('suratKeluar')
            ->first();

        if (!$tandaTanganDigital) {
            return back()
                ->with('hashUpload', $hashUpload)
                ->with('error', 'Berkas tidak terdaftar dalam sistem. Dokumen ini tidak memiliki tanda tangan digital yang sah.');
        }

        return view('tanda-tangan-digital.verify-public', [
            'tandaTanganDigital' => $tandaTanganDigital,
            'uploadHash'         => $hashUpload,
            'uploadValid'        => true,
        ]);
    }
}
