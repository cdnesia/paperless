# E-OFFICE SYSTEM REFACTOR PLAN

## KONSEP YANG DIPERBARUI

### 1. SURAT MASUK (Incoming Letters)
**Definisi**: Surat yang diterima dari luar/pengirim eksternal
- User membuka: lihat detail surat masuk (tanpa riwayat)
- Bisa didisposisikan ke user lain → menjadi **DISPOSISI MASUK**
- Simple model, tidak perlu tracking kompleks
- Mark as read/unread hanya untuk knowledge

**Database**: `surat_masuks` table (UUID PK)
- `nomor_surat`, `perihal`, `pengirim`, `tanggal_diterima`, `google_docs_url`, `dibaca`, `timestamps`
- No relationships ke surat_keluar_penerima

---

### 2. DISPOSISI MASUK (Received Dispositions)  
**Definisi**: Surat Masuk yang DITERUSKAN kepada user lain
- User menerima disposisi dari rekan
- Menampilkan: surat masuk original + info disposisi (pengirim, keterangan)
- Bisa lanjut teruskan ke user lain → chain disposisi
- Mark as read/unread tracking untuk disposisi

**Database**: `disposisis` table (UUID PK)
- `surat_masuk_id` (nullable - bisa null untuk disposisi keluar)
- `pengguna_id` (penerima disposisi)
- `pengirim_id` (siapa yg teruskan)
- `keterangan`, `status` (diteruskan/diterima/ditolak), `dibaca`, `timestamps`

**Routes**:
- `/disposisi-masuk/belum-dibaca` - list unread dispositions received
- `/disposisi-masuk/sudah-dibaca` - list read dispositions received
- `/disposisi-masuk/{disposisi}` - show detail disposisi masuk
- POST `/disposisi-masuk/{disposisi}/teruskan` - forward disposisi to another user
- PATCH `/disposisi-masuk/{disposisi}/mark-as-read` - mark read

---

### 3. SURAT KELUAR (Outgoing Letters) - TETAP
**Definisi**: Surat dibuat oleh user, dikirim ke penerima
- Complex workflow: draft → review → approved → sent → archived
- Dual-mode: upload PDF atau Google Docs
- Multi-recipient tracking via pivot table
- **IMPORTANT**: History timeline TETAP ditampilkan (creation, approval, sending, revisions)
- Status history & audit trail PENTING untuk accountability

**Timeline kept**: surat_keluar_histories table

---

### 4. DISPOSISI KELUAR (OPTIONAL - skipped now)
**Note**: Tidak disertakan dalam fase 1 refactor. Fokus pada Masuk dulu.

---

## PERUBAHAN TEKNIS

### A. MODELS
1. **SuratMasuk**: Keep as is (simple model)
2. **Disposisi**: 
   - Add relation: `suratMasuk()` BelongsTo SuratMasuk
   - Add method: `isMasuk()` check if surat_masuk_id not null
3. **SuratKeluar**: Keep as is (complex, dengan history)

### B. CONTROLLERS

#### SuratMasukController
- `index()`, `belumDibaca()`, `sudahDibaca()` - **REMOVE** suratKeluarDiterima queries
- `show()` - **REMOVE** disposisi card display, hanya tampilkan surat masuk
- **DELETE** methods: `showSuratKeluar()`, `markAsReadSuratKeluar()`, `markAsUnreadSuratKeluar()`, `disposisiSuratKeluar()`
- ADD method: `disposisi(Request $r, SuratMasuk $sm)` - create disposisi masuk

#### DisposisiController (REFACTORED)
- **SEPARATE CONCERNS**:
  - `index()` - disposisi keluar only
  - `belumDibaca()` - disposisi keluar unread
  - `sudahDibaca()` - disposisi keluar read
  
- **ADD disposisi masuk methods**:
  - `masukBelumDibaca()` - disposisi masuk unread (received by me)
  - `masukSudahDibaca()` - disposisi masuk read  
  - `showMasuk(Disposisi $d)` - view disposisi masuk detail
  - `teruskanMasuk(Request $r, Disposisi $d)` - forward disposisi masuk

#### SuratKeluarController
- Keep all methods (no change)
- History timeline TETAP ditampilkan di show()

### C. MIGRATIONS
**Consolidate into one clean file**: `2026_05_24_150000_create_e_office_tables.php`

Remove these fragmented files:
- `2026_05_24_070152_create_surat_keluars_table.php`
- `2026_05_24_070152_create_surat_masuks_table.php`  
- `2026_05_24_090104_create_surat_keluar_histories_table.php`
- `2026_05_24_131627_create_disposisis_table.php`
- `2026_05_24_133858_create_surat_keluar_penerima_table.php`
- `2026_05_24_140000_change_disposisis_id_to_uuid.php`

**New structure** (in single file):
```
surat_masuks
├─ id (UUID)
├─ nomor_surat (unique)
├─ perihal
├─ pengirim
├─ tanggal_diterima
├─ google_docs_url
├─ dibaca (boolean)
└─ timestamps

surat_keluars
├─ id (UUID)
├─ user_id → users.id
├─ nomor_surat (unique)
├─ perihal
├─ jenis_surat (enum: internal, eksternal, broadcast)
├─ metode_surat (enum: upload, gdocs)
├─ status (enum: draft, review, approved, sent, archived)
├─ tanggal_surat
├─ file_pdf
├─ google_doc_id
├─ lampiran
├─ dibaca
├─ sent_at
└─ timestamps

surat_keluar_histories
├─ id (UUID)
├─ surat_keluar_id → surat_keluars.id (FK, cascade)
├─ user_id → users.id (FK, cascade)
├─ action (string)
├─ keterangan
├─ data (JSON)
└─ timestamps

surat_keluar_penerima (pivot)
├─ id (auto_increment) - keep for pivot
├─ surat_keluar_id → surat_keluars.id (FK, cascade)
├─ user_id → users.id (FK, cascade)
├─ dibaca (boolean)
├─ dibaca_at
└─ timestamps

disposisis
├─ id (UUID)
├─ surat_masuk_id → surat_masuks.id (FK, nullable, cascade) 
├─ surat_keluar_id → surat_keluars.id (FK, nullable, cascade)
├─ pengguna_id → users.id (FK, no action)
├─ pengirim_id → users.id (FK, nullable, set null)
├─ keterangan (text, nullable)
├─ status (enum: diteruskan, diterima, ditolak, default: diteruskan)
├─ dibaca (boolean, default: false)
└─ timestamps
```

### D. VIEWS

#### KEEP/UPDATE
- `surat-masuk/index.blade.php` - remove surat keluar masuk section
- `surat-masuk/show.blade.php` - **REMOVE timeline**, show only surat masuk data
- `surat-masuk/belum-dibaca.blade.php` - remove surat keluar
- `surat-masuk/sudah-dibaca.blade.php` - remove surat keluar  
- `surat-keluar/*` - KEEP ALL (tetap kompleks dengan history)

#### DELETE
- `surat-masuk/show-surat-keluar.blade.php` - NO MORE (konsep changed)

#### CREATE (Disposisi Masuk)
- `disposisi/masuk/belum-dibaca.blade.php`
- `disposisi/masuk/sudah-dibaca.blade.php`
- `disposisi/masuk/show.blade.php` - with forward form
- `disposisi/keluar/belum-dibaca.blade.php` (optional)
- `disposisi/keluar/sudah-dibaca.blade.php` (optional)

### E. ROUTES

**REMOVE from `/surat-masuk` prefix**:
- GET `/surat-masuk/surat-keluar-dari/{suratKeluar}` → showSuratKeluar
- POST `/surat-masuk/surat-keluar-dari/{suratKeluar}/disposisi` → disposisiSuratKeluar  
- PATCH `/surat-masuk/surat-keluar-dari/{suratKeluar}/mark-as-read` → markAsReadSuratKeluar
- PATCH `/surat-masuk/surat-keluar-dari/{suratKeluar}/mark-as-unread` → markAsUnreadSuratKeluar

**ADD to `/surat-masuk` prefix**:
- POST `/surat-masuk/{suratMasuk}/disposisi` → disposisi (create disposisi masuk)

**ADD under new `/disposisi-masuk` prefix**:
- GET `/disposisi-masuk/belum-dibaca` → masukBelumDibaca
- GET `/disposisi-masuk/sudah-dibaca` → masukSudahDibaca
- GET `/disposisi-masuk/{disposisi}` → showMasuk
- POST `/disposisi-masuk/{disposisi}/teruskan` → teruskanMasuk
- PATCH `/disposisi-masuk/{disposisi}/mark-as-read` → markAsReadMasuk
- PATCH `/disposisi-masuk/{disposisi}/mark-as-unread` → markAsUnreadMasuk

### F. SIDEBAR MENU

**RESTRUCTURE**:
```
Surat Keluar (route: surat-keluar.index)
Surat Masuk (route: surat-masuk.index)
Disposisi Surat
├─ Disposisi Masuk (route: disposisi-masuk.belum-dibaca)
├─ Disposisi Keluar (route: disposisi.belum-dibaca) [optional]
```

---

## IMPLEMENTATION PRIORITY

1. **Phase 1: Database** (This PR)
   - Create consolidated migration file
   - Run migration cleanup
   
2. **Phase 2: Backend** 
   - Update controllers
   - Update models
   - Update routes

3. **Phase 3: Frontend**
   - Update surat-masuk views
   - Create disposisi-masuk views
   - Update sidebar

4. **Phase 4: Testing**
   - Test all workflows
   - Test permissions
   - Verify clean separation

---

## INDUSTRY STANDARDS APPLIED

✅ **Single Responsibility**: SuratMasuk ≠ Disposisi Masuk ≠ Surat Keluar  
✅ **Consolidated Migrations**: No fragmented files (cleaner git history)  
✅ **Separation of Concerns**: Controllers handle their domain only  
✅ **UUID for PK**: Consistent across all tables  
✅ **Audit Trail**: History kept where needed (SuratKeluar)  
✅ **Nullable FKs**: Disposisi can link masuk OR keluar (not both)  
✅ **Clear Status Enums**: No ambiguous values  
✅ **Meaningful Relationships**: Every FK has purpose  

---

## ROLLBACK PLAN (if needed)

Since we're consolidating migrations, no rollback needed after cleanup. Previous files are historical.
Just keep git history.

---

## FILES TO BE MODIFIED/CREATED

**Delete** (old migrations):
- 2026_05_24_070152_create_surat_keluars_table.php
- 2026_05_24_070152_create_surat_masuks_table.php
- 2026_05_24_090104_create_surat_keluar_histories_table.php
- 2026_05_24_131627_create_disposisis_table.php
- 2026_05_24_133858_create_surat_keluar_penerima_table.php
- 2026_05_24_140000_change_disposisis_id_to_uuid.php
- resources/views/surat-masuk/show-surat-keluar.blade.php

**Create** (new):
- 2026_05_24_150000_create_e_office_tables.php (consolidated)
- resources/views/disposisi/masuk/belum-dibaca.blade.php
- resources/views/disposisi/masuk/sudah-dibaca.blade.php
- resources/views/disposisi/masuk/show.blade.php

**Update**:
- app/Http/Controllers/SuratMasukController.php
- app/Http/Controllers/DisposisiController.php
- app/Models/Disposisi.php
- routes/web.php
- resources/views/surat-masuk/*.blade.php
- resources/views/layouts/partials/sidebar.blade.php

