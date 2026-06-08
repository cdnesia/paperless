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
     * Verifikasi keaslian tanda tangan digital.
     */
    public function verify(TandaTanganDigital $tandaTanganDigital)
    {
        $hashSekarang = null;
        $status = 'unknown';
        $mode = 'final'; // 'dual' = masih punya 2 file, 'final' = hanya 1 file (sudah di-replace)

        // Jika masih ada file_pdf_final (sebelum dikirim), verifikasi dual
        if ($tandaTanganDigital->file_pdf_final) {
            $mode = 'dual';
            $fileFinalPath = Storage::disk('public')->path($tandaTanganDigital->file_pdf_final);
            if (file_exists($fileFinalPath)) {
                $hashSekarang = hash_file('sha256', $fileFinalPath);
                $status = $hashSekarang === $tandaTanganDigital->hash_sha256_final ? 'valid' : 'invalid';
            }
        } elseif ($tandaTanganDigital->suratKeluar->file_pdf) {
            // Setelah dikirim: file sudah di-replace, verifikasi hash_final terhadap file saat ini
            $filePath = Storage::disk('public')->path($tandaTanganDigital->suratKeluar->file_pdf);
            if (file_exists($filePath)) {
                $hashSekarang = hash_file('sha256', $filePath);
                $status = $hashSekarang === $tandaTanganDigital->hash_sha256_final ? 'valid' : 'invalid';
            }
        }

        return view('tanda-tangan-digital.verify-public', compact(
            'tandaTanganDigital',
            'hashSekarang',
            'status',
            'mode'
        ));
    }
}
