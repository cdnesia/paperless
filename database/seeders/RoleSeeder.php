<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ─── Buat Role ─────────────────────────────────────

        // Super Admin — akses penuh ke semua fitur
        $superAdmin = Role::firstOrCreate(['name' => 'super-admin']);
        $superAdmin->givePermissionTo(Permission::all());

        // Rektor — semua kecuali manajemen user/role/permission
        $rektor = Role::firstOrCreate(['name' => 'Rektor']);
        $rektor->givePermissionTo([
            'dashboard',
            // Surat keluar
            'surat-keluar.index', 'surat-keluar.show', 'surat-keluar.edit', 'surat-keluar.update',
            'surat-keluar.mark-as-read', 'surat-keluar.mark-as-unread',
            'surat-keluar.history', 'surat-keluar.disposisi',
            // Surat masuk
            'surat-masuk.index',
            'surat-masuk.show', 'surat-masuk.mark-as-read', 'surat-masuk.mark-as-unread',
            'surat-masuk.update-status',
            // Disposisi
            'disposisi.index', 'disposisi.show',
            'disposisi.mark-as-read', 'disposisi.mark-as-unread', 'disposisi.update-status',
            // Disposisi Masuk
            'disposisi-masuk.index', 'disposisi-masuk.show', 'disposisi-masuk.teruskan',
            'disposisi-masuk.mark-as-read', 'disposisi-masuk.mark-as-unread', 'disposisi-masuk.update-status',
            // Profile
            'profile.index', 'profile.update', 'profile.delete',
            // Tanda Tangan Digital
            'tanda-tangan-digital.index', 'tanda-tangan-digital.sign',
        ]);

        // Wakil Rektor — sama seperti Rektor
        $wakilRektor = Role::firstOrCreate(['name' => 'Wakil Rektor']);
        $wakilRektor->givePermissionTo($rektor->permissions()->pluck('name')->toArray());

        // Dekan — surat keluar (baca & kelola), surat masuk, disposisi, profile
        $dekan = Role::firstOrCreate(['name' => 'Dekan']);
        $dekan->givePermissionTo([
            'dashboard',
            'surat-keluar.index', 'surat-keluar.show', 'surat-keluar.mark-as-read', 'surat-keluar.mark-as-unread',
            'surat-keluar.history', 'surat-keluar.disposisi',
            'surat-masuk.index',
            'surat-masuk.show', 'surat-masuk.mark-as-read', 'surat-masuk.mark-as-unread',
            'surat-masuk.update-status',
            'disposisi.index', 'disposisi.show',
            'disposisi.mark-as-read', 'disposisi.mark-as-unread', 'disposisi.update-status',
            'disposisi-masuk.index', 'disposisi-masuk.show', 'disposisi-masuk.teruskan',
            'disposisi-masuk.mark-as-read', 'disposisi-masuk.mark-as-unread', 'disposisi-masuk.update-status',
            'profile.index', 'profile.update', 'profile.delete',
            'tanda-tangan-digital.index', 'tanda-tangan-digital.sign',
        ]);

        // Ketua Program Studi
        $kaprodi = Role::firstOrCreate(['name' => 'Ketua Program Studi']);
        $kaprodi->givePermissionTo([
            'dashboard',
            'surat-keluar.index', 'surat-keluar.show', 'surat-keluar.mark-as-read', 'surat-keluar.mark-as-unread',
            'surat-keluar.history', 'surat-keluar.disposisi',
            'surat-masuk.index',
            'surat-masuk.show', 'surat-masuk.mark-as-read', 'surat-masuk.mark-as-unread',
            'surat-masuk.update-status',
            'disposisi.update-status',
            'disposisi-masuk.index', 'disposisi-masuk.show', 'disposisi-masuk.teruskan',
            'disposisi-masuk.mark-as-read', 'disposisi-masuk.mark-as-unread', 'disposisi-masuk.update-status',
            'profile.index', 'profile.update', 'profile.delete',
        ]);

        // Ketua — sama seperti Kaprodi
        $ketua = Role::firstOrCreate(['name' => 'Ketua']);
        $ketua->givePermissionTo($kaprodi->permissions()->pluck('name')->toArray());

        // Kepala — sama seperti Kaprodi
        $kepala = Role::firstOrCreate(['name' => 'Kepala']);
        $kepala->givePermissionTo($kaprodi->permissions()->pluck('name')->toArray());

        // Sekretaris — bisa buat & kelola surat keluar, disposisi, profile
        $sekretaris = Role::firstOrCreate(['name' => 'Sekretaris']);
        $sekretaris->givePermissionTo([
            'dashboard',
            'surat-keluar.index', 'surat-keluar.create', 'surat-keluar.store',
            'surat-keluar.show', 'surat-keluar.edit', 'surat-keluar.update',
            'surat-keluar.send', 'surat-keluar.mark-as-read', 'surat-keluar.mark-as-unread',
            'surat-keluar.history', 'surat-keluar.disposisi',
            'surat-masuk.index',
            'surat-masuk.show', 'surat-masuk.mark-as-read', 'surat-masuk.mark-as-unread',
            'surat-masuk.update-status',
            'disposisi.index', 'disposisi.show',
            'disposisi.mark-as-read', 'disposisi.mark-as-unread', 'disposisi.update-status',
            'disposisi-masuk.index', 'disposisi-masuk.show', 'disposisi-masuk.teruskan',
            'disposisi-masuk.mark-as-read', 'disposisi-masuk.mark-as-unread', 'disposisi-masuk.update-status',
            'profile.index', 'profile.update', 'profile.delete',
            'tanda-tangan-digital.index', 'tanda-tangan-digital.sign',
        ]);

        // Staff
        $staff = Role::firstOrCreate(['name' => 'Staff']);
        $staff->givePermissionTo([
            'dashboard',
            'surat-masuk.index',
            'surat-masuk.show', 'surat-masuk.mark-as-read', 'surat-masuk.mark-as-unread',
            'surat-masuk.update-status',
            'disposisi.update-status',
            'disposisi-masuk.index', 'disposisi-masuk.show', 'disposisi-masuk.teruskan',
            'disposisi-masuk.mark-as-read', 'disposisi-masuk.mark-as-unread', 'disposisi-masuk.update-status',
            'profile.index', 'profile.update', 'profile.delete',
        ]);

        // User Biasa — minimal, hanya lihat surat masuk & disposisi masuk
        $userBiasa = Role::firstOrCreate(['name' => 'User Biasa']);
        $userBiasa->givePermissionTo([
            'dashboard',
            'surat-masuk.index',
            'surat-masuk.show', 'surat-masuk.mark-as-read',
            'surat-masuk.update-status',
            'disposisi.update-status',
            'disposisi-masuk.index', 'disposisi-masuk.show', 'disposisi-masuk.update-status',
            'profile.index', 'profile.update',
        ]);
    }
}
