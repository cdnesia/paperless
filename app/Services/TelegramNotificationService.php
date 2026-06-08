<?php

namespace App\Services;

use App\Models\Pengaturan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramNotificationService
{
    private string $botToken;
    private string $baseUrl;

    public function __construct()
    {
        $this->botToken = Pengaturan::dapatkan('telegram_bot_token', env('TELEGRAM_BOT_TOKEN', ''));
        $this->baseUrl = "https://api.telegram.org/bot{$this->botToken}";
    }

    /**
     * Send notification to a single user.
     */
    public function send(int|string $chatId, string $message): bool
    {
        if (empty($this->botToken)) {
            Log::warning('Telegram: Bot token not configured.');
            return false;
        }

        if (empty($chatId)) {
            return false;
        }

        try {
            $response = Http::timeout(10)->post("{$this->baseUrl}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'HTML',
                'disable_web_page_preview' => true,
            ]);

            if (!$response->successful()) {
                Log::warning('Telegram send failed', [
                    'chat_id' => $chatId,
                    'response' => $response->json(),
                ]);
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Telegram send error: ' . $e->getMessage(), ['chat_id' => $chatId]);
            return false;
        }
    }

    /**
     * Notify user about new incoming letter (surat masuk).
     */
    public function notifySuratMasuk($user, string $nomorSurat, string $perihal, string $pengirim): bool
    {
        $chatId = $user->telegram_chat_id ?? null;
        if (!$chatId) return false;

        // Cek enable
        if (Pengaturan::dapatkan('telegram_notif_surat_masuk', '1') !== '1') return false;

        $tpl = Pengaturan::dapatkan('telegram_tpl_surat_masuk', $this->defaultSuratMasuk());
        $msg = $this->renderTemplate($tpl ?: $this->defaultSuratMasuk(), [
            '{name}' => $user->name,
            '{nomor}' => $nomorSurat,
            '{perihal}' => $perihal,
            '{pengirim}' => $pengirim,
        ]);

        return $this->send($chatId, $msg);
    }

    /**
     * Notify user about new disposition (disposisi masuk).
     */
    public function notifyDisposisiMasuk($user, string $nomorSurat, string $perihal, string $pengirim, string $keterangan = ''): bool
    {
        $chatId = $user->telegram_chat_id ?? null;
        if (!$chatId) return false;

        // Cek enable
        if (Pengaturan::dapatkan('telegram_notif_disposisi_masuk', '1') !== '1') return false;

        $tpl = Pengaturan::dapatkan('telegram_tpl_disposisi_masuk', $this->defaultDisposisiMasuk());
        $msg = $this->renderTemplate($tpl ?: $this->defaultDisposisiMasuk(), [
            '{name}' => $user->name,
            '{nomor}' => $nomorSurat,
            '{perihal}' => $perihal,
            '{pengirim}' => $pengirim,
            '{keterangan}' => $keterangan ?: '(tanpa keterangan)',
        ]);

        return $this->send($chatId, $msg);
    }

    private function renderTemplate(string $template, array $replacements): string
    {
        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }

    private function defaultSuratMasuk(): string
    {
        return implode("\n", [
            "📨 <b>Surat Masuk Baru</b>",
            "",
            "Halo {name},",
            "",
            "Anda menerima surat masuk:",
            "📄 <b>{perihal}</b>",
            "🔢 No. Surat: {nomor}",
            "👤 Pengirim: {pengirim}",
            "",
            "Silakan buka aplikasi " . Pengaturan::dapatkan('app_nama', 'E-Office') . " untuk melihat detailnya.",
        ]);
    }

    private function defaultDisposisiMasuk(): string
    {
        return implode("\n", [
            "↻ <b>Disposisi Masuk Baru</b>",
            "",
            "Halo {name},",
            "",
            "Surat telah didisposisikan kepada Anda:",
            "📄 <b>{perihal}</b>",
            "🔢 No. Surat: {nomor}",
            "👤 Dari: {pengirim}",
            "📝 Keterangan: {keterangan}",
            "",
            "Silakan buka aplikasi " . Pengaturan::dapatkan('app_nama', 'E-Office') . " untuk menindaklanjuti.",
        ]);
    }
}
