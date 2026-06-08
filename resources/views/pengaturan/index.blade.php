@extends('layouts.app')
@section('title', 'Pengaturan Sistem')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header border-0 py-3 d-flex justify-content-between align-items-center">
                        <h6 class="card-title mb-0">
                            <i class="fi fi-rr-settings me-1"></i> Pengaturan Sistem
                        </h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('pengaturan.simpan') }}" method="POST">
                            @csrf

                            {{-- APLIKASI --}}
                            <div class="border rounded p-3 mb-3">
                                <h6 class="text-uppercase text-muted small mb-3 fw-bold">
                                    <i class="fi fi-rr-globe me-1"></i> Informasi Aplikasi
                                </h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="app_nama" class="form-label small fw-semibold">Nama Aplikasi</label>
                                        <input type="text" class="form-control form-control-sm" id="app_nama"
                                            name="app_nama" value="{{ $pengaturan['app_nama'] ?? config('app.name') }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="app_deskripsi" class="form-label small fw-semibold">Deskripsi</label>
                                        <input type="text" class="form-control form-control-sm" id="app_deskripsi"
                                            name="app_deskripsi" value="{{ $pengaturan['app_deskripsi'] ?? '' }}">
                                    </div>
                                </div>
                            </div>

                            {{-- TELEGRAM --}}
                            <div class="border rounded p-3 mb-3">
                                <h6 class="text-uppercase text-muted small mb-3 fw-bold">
                                    <i class="fi fi-rr-paper-plane me-1"></i> Konfigurasi Telegram
                                </h6>

                                <div class="mb-3">
                                    <label for="telegram_bot_token" class="form-label small fw-semibold">Bot Token</label>
                                    <input type="text" class="form-control form-control-sm @error('telegram_bot_token') is-invalid @enderror"
                                        id="telegram_bot_token" name="telegram_bot_token"
                                        value="{{ $pengaturan['telegram_bot_token'] ?? '' }}"
                                        placeholder="123456:ABC-DEF1234gh...">
                                    <small class="text-muted">Dapatkan dari @BotFather di Telegram</small>
                                    @error('telegram_bot_token')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <hr class="my-3">

                                <h6 class="small fw-semibold mb-2">Notifikasi Surat Masuk</h6>
                                <div class="row g-2 mb-3">
                                    <div class="col-md-3">
                                        <label class="form-label small fw-semibold">Status</label>
                                        <select class="form-select form-select-sm select2" name="telegram_notif_surat_masuk">
                                            <option value="1" {{ ($pengaturan['telegram_notif_surat_masuk'] ?? '1') === '1' ? 'selected' : '' }}>Aktif</option>
                                            <option value="0" {{ ($pengaturan['telegram_notif_surat_masuk'] ?? '') === '0' ? 'selected' : '' }}>Nonaktif</option>
                                        </select>
                                    </div>
                                    <div class="col-md-9">
                                        <label class="form-label small fw-semibold">Template Pesan</label>
                                        <textarea class="form-control form-control-sm" name="telegram_tpl_surat_masuk" rows="6">{{ $pengaturan['telegram_tpl_surat_masuk'] ?? "📨 <b>Surat Masuk Baru</b>\n\nHalo {name},\n\nAnda menerima surat masuk:\n📄 <b>{perihal}</b>\n🔢 No. Surat: {nomor}\n👤 Pengirim: {pengirim}\n\nSilakan buka aplikasi " . config('app.name') . " untuk melihat detailnya." }}</textarea>
                                        <small class="text-muted">Variable: <code>{name}</code> <code>{nomor}</code> <code>{perihal}</code> <code>{pengirim}</code></small>
                                    </div>
                                </div>

                                <hr class="my-3">

                                <h6 class="small fw-semibold mb-2">Notifikasi Disposisi Masuk</h6>
                                <div class="row g-2">
                                    <div class="col-md-3">
                                        <label class="form-label small fw-semibold">Status</label>
                                        <select class="form-select form-select-sm select2" name="telegram_notif_disposisi_masuk">
                                            <option value="1" {{ ($pengaturan['telegram_notif_disposisi_masuk'] ?? '1') === '1' ? 'selected' : '' }}>Aktif</option>
                                            <option value="0" {{ ($pengaturan['telegram_notif_disposisi_masuk'] ?? '') === '0' ? 'selected' : '' }}>Nonaktif</option>
                                        </select>
                                    </div>
                                    <div class="col-md-9">
                                        <label class="form-label small fw-semibold">Template Pesan</label>
                                        <textarea class="form-control form-control-sm" name="telegram_tpl_disposisi_masuk" rows="6">{{ $pengaturan['telegram_tpl_disposisi_masuk'] ?? "↻ <b>Disposisi Masuk Baru</b>\n\nHalo {name},\n\nSurat telah didisposisikan kepada Anda:\n📄 <b>{perihal}</b>\n🔢 No. Surat: {nomor}\n👤 Dari: {pengirim}\n📝 Keterangan: {keterangan}\n\nSilakan buka aplikasi " . config('app.name') . " untuk menindaklanjuti." }}</textarea>
                                        <small class="text-muted">Variable: <code>{name}</code> <code>{nomor}</code> <code>{perihal}</code> <code>{pengirim}</code> <code>{keterangan}</code></small>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex gap-2 pt-3 border-top">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="fi fi-rr-check me-1"></i> Simpan Pengaturan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- PANEL PETUNJUK --}}
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm sticky-top" style="top: 80px;">
                    <div class="card-header border-0 bg-transparent pb-0">
                        <h6 class="card-title mb-0 small fw-bold text-uppercase text-muted">
                            <i class="fi fi-rr-info me-1"></i> Panduan Telegram
                        </h6>
                    </div>
                    <div class="card-body pt-2">
                        <div class="d-flex mb-3">
                            <div class="text-muted me-2" style="font-size: 11px; width: 20px; flex-shrink: 0;">1.</div>
                            <div>
                                <strong class="small">Buat Bot</strong>
                                <p class="text-muted small mb-0">Buka Telegram, cari <code>@BotFather</code>, kirim <code>/newbot</code>, ikuti instruksinya.</p>
                            </div>
                        </div>
                        <div class="d-flex mb-3">
                            <div class="text-muted me-2" style="font-size: 11px; width: 20px; flex-shrink: 0;">2.</div>
                            <div>
                                <strong class="small">Dapatkan Token</strong>
                                <p class="text-muted small mb-0">Setelah bot dibuat, @BotFather akan memberikan token. Copy dan paste di kolom Bot Token.</p>
                            </div>
                        </div>
                        <div class="d-flex mb-3">
                            <div class="text-muted me-2" style="font-size: 11px; width: 20px; flex-shrink: 0;">3.</div>
                            <div>
                                <strong class="small">Dapatkan Chat ID</strong>
                                <p class="text-muted small mb-0">Setiap user harus kirim pesan ke bot Anda terlebih dahulu. Chat ID bisa dicek melalui <code>@userinfobot</code> atau bot Anda sendiri.</p>
                            </div>
                        </div>
                        <div class="d-flex mb-0">
                            <div class="text-muted me-2" style="font-size: 11px; width: 20px; flex-shrink: 0;">4.</div>
                            <div>
                                <strong class="small">Isi Chat ID di User</strong>
                                <p class="text-muted small mb-0">Buka Data Pengguna → Edit → isi kolom <strong>Telegram Chat ID</strong> untuk setiap user yang ingin menerima notifikasi.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
