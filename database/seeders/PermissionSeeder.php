<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Route;

class PermissionSeeder extends Seeder
{
    /**
     * Daftar route name yang dikecualikan (tidak perlu permission).
     */
    private array $excludedRoutes = [
        'login',
        'login.post',
        'logout',
        'storage.local',
        'storage.local.upload',
        'up',
        'tanda-tangan-digital.verify',
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $routes = Route::getRoutes();
        $names = [];

        foreach ($routes as $route) {
            $name = $route->getName();
            if ($name && !in_array($name, $this->excludedRoutes, true)) {
                $names[] = $name;
            }
        }

        sort($names);

        foreach ($names as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $this->command->info('Permissions berhasil disinkronkan dari ' . count($names) . ' route.');
    }
}
