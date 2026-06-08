<?php

namespace App\Http\Controllers;

use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class PermissionController extends Controller
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
     * Ambil semua route name dari sistem, kecuali yang dikecualikan.
     */
    private function getRouteNames(): array
    {
        $routes = Route::getRoutes();
        $names = [];

        foreach ($routes as $route) {
            $name = $route->getName();
            if ($name && !in_array($name, $this->excludedRoutes, true)) {
                $names[] = $name;
            }
        }

        sort($names);
        return $names;
    }

    public function index()
    {
        $permissions = Permission::all();

        return view('permissions.index', compact('permissions'));
    }

    public function create()
    {
        return view('permissions.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|unique:permissions',
        ]);

        Permission::create($validated);

        return redirect()->route('permissions.index')->with('success', 'Permission berhasil dibuat');
    }

    public function show(Permission $permission)
    {
        return view('permissions.show', compact('permission'));
    }

    public function edit(Permission $permission)
    {
        return view('permissions.edit', compact('permission'));
    }

    public function update(Request $request, Permission $permission)
    {
        $validated = $request->validate([
            'name' => 'required|unique:permissions,name,' . $permission->id,
        ]);

        $permission->update($validated);

        return redirect()->route('permissions.show', $permission)->with('success', 'Permission berhasil diperbarui');
    }

    public function destroy(Permission $permission)
    {
        $permission->delete();
        return redirect()->route('permissions.index')->with('success', 'Permission berhasil dihapus');
    }

    /**
     * Sinkronisasi: buat permission otomatis dari route name yang belum ada.
     */
    public function sync()
    {
        $routeNames = $this->getRouteNames();
        $created = 0;

        foreach ($routeNames as $name) {
            try {
                Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
                $created++;
            } catch (\Exception $e) {
                // skip jika gagal
            }
        }

        return redirect()->route('permissions.index')
            ->with('success', "Sinkronisasi selesai. {$created} permission baru berhasil dibuat.");
    }
}

