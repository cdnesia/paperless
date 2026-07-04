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
        $this->botToken = config('services.telegram.bot_token', '');
        $this->baseUrl = "https://api.telegram.org/bot{$this->botToken}";
    }

    /**
     * Send notification to a single user.
     */
    public function send(int|string $chatId, string $message, array $replyMarkup = []): bool
    {
        if (empty($this->botToken)) {
            Log::warning('Telegram: Bot token not configured.');
            return false;
        }

        if (empty($chatId)) {
            return false;
        }

        try {
            $payload = [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'HTML',
                'disable_web_page_preview' => true,
            ];

            // Tambahkan reply_markup (tombol) jika ada
            if (!empty($replyMarkup)) {
                $payload['reply_markup'] = json_encode($replyMarkup);
            }

            $response = Http::timeout(10)->post("{$this->baseUrl}/sendMessage", $payload);

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
    public function notifySuratMasuk($user, string $nomorSurat, string $perihal, string $pengirim, string $url = 'https://eoffice.umjambi.ac.id'): bool
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

        $replyMarkup = [];
        if (!empty($url)) {
            $replyMarkup = [
                'inline_keyboard' => [
                    [
                        ['text' => 'Buka Surat 📥', 'url' => $url]
                    ]
                ]
            ];
        }

        return $this->send($chatId, $msg, $replyMarkup);
    }

    /**
     * Notify user about new disposition (disposisi masuk).
     */
    public function notifyDisposisiMasuk($user, string $nomorSurat, string $perihal, string $pengirim, string $keterangan = '', string $pendisposisi = '', string $url = 'https://eoffice.umjambi.ac.id'): bool
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
            '{pendisposisi}' => $pendisposisi ?: '(tidak diketahui)',
        ]);

        $replyMarkup = [];
        if (!empty($url)) {
            $replyMarkup = [
                'inline_keyboard' => [
                    [
                        ['text' => 'Buka Surat 📥', 'url' => $url]
                    ]
                ]
            ];
        }

        return $this->send($chatId, $msg, $replyMarkup);
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
            "✍️ Pendisposisi: {pendisposisi}",
            "📝 Keterangan: {keterangan}",
            "",
            "Silakan buka aplikasi " . Pengaturan::dapatkan('app_nama', 'E-Office') . " untuk menindaklanjuti.",
        ]);
    }
}
