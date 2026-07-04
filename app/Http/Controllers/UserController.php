<?php

namespace App\Http\Controllers;

use App\Models\UnitKerja;
use App\Models\User;
use App\Services\TelegramNotificationService;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with(['roles', 'unitKerja'])->get();
        return view('users.index', compact('users'));
    }

    public function create()
    {
        $roles = Role::all();
        $rolesWhitelist = $roles->map(fn($r) => ['value' => $r->name, 'name' => $r->name])->values();
        $unitKerjas = UnitKerja::orderBy('nama')->get();
        return view('users.create', compact('roles', 'rolesWhitelist', 'unitKerjas'));
    }

    public function store(Request $request)
    {
        // Decode JSON dari Tagify jika roles dikirim sebagai string JSON
        if ($request->filled('roles') && is_string($request->roles)) {
            $decoded = json_decode($request->roles, true);
            if (is_array($decoded)) {
                $request->merge([
                    'roles' => collect($decoded)->pluck('value')->toArray(),
                ]);
            }
        }

        $validated = $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
            'telegram_chat_id' => 'nullable|string|max:100',
            'unit_kerja_id' => 'required|exists:unit_kerjas,id',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,name',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'telegram_chat_id' => $validated['telegram_chat_id'] ?? null,
            'unit_kerja_id' => $validated['unit_kerja_id'],
        ]);

        if (!empty($validated['roles'])) {
            $user->syncRoles($validated['roles']);
        }

        return redirect()->route('users.index')->with('success', 'User berhasil dibuat');
    }

    public function show(User $user)
    {
        return view('users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $roles = Role::all();
        $rolesWhitelist = $roles->map(fn($r) => ['value' => $r->name, 'name' => $r->name])->values();
        $unitKerjas = UnitKerja::orderBy('nama')->get();
        $userRoles = $user->roles->pluck('name')->toArray();
        return view('users.edit', compact('user', 'roles', 'rolesWhitelist', 'unitKerjas', 'userRoles'));
    }

    public function update(Request $request, User $user)
    {
        // Decode JSON dari Tagify jika roles dikirim sebagai string JSON
        if ($request->filled('roles') && is_string($request->roles)) {
            $decoded = json_decode($request->roles, true);
            if (is_array($decoded)) {
                $request->merge([
                    'roles' => collect($decoded)->pluck('value')->toArray(),
                ]);
            }
        }

        $validated = $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|min:8|confirmed',
            'telegram_chat_id' => 'nullable|string|max:100',
            'unit_kerja_id' => 'required|exists:unit_kerjas,id',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,name',
        ]);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'telegram_chat_id' => $validated['telegram_chat_id'] ?? null,
            'unit_kerja_id' => $validated['unit_kerja_id'],
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        if (array_key_exists('roles', $validated)) {
            $user->syncRoles($validated['roles'] ?? []);
        } else {
            $user->syncRoles([]);
        }

        return redirect()->route('users.index', $user)->with('success', 'User berhasil diperbarui');
    }

    /**
     * Test kirim pesan Telegram ke user.
     */
    public function testTelegram(User $user)
    {
        $chatId = $user->telegram_chat_id;

        if (!$chatId) {
            return response()->json([
                'success' => false,
                'message' => 'User ini belum memiliki Telegram Chat ID.',
            ], 422);
        }

        $telegram = app(TelegramNotificationService::class);
        $appName = config('app.name', 'E-Office');

        $result = $telegram->send($chatId, implode("\n", [
            "✅ <b>Test Notifikasi</b>",
            "",
            "Halo <b>{$user->name}</b>,",
            "",
            "Ini adalah pesan test dari aplikasi <b>{$appName}</b>.",
            "Notifikasi Telegram berfungsi dengan baik!",
            "",
            "📅 " . now()->translatedFormat('d F Y H:i'),
        ]), [
            'inline_keyboard' => [
                [
                    ['text' => 'Buka E-Office 🌐', 'url' => 'https://eoffice.umjambi.ac.id'],
                ],
            ],
        ]);

        if ($result) {
            return response()->json([
                'success' => true,
                'message' => "Pesan test berhasil dikirim ke {$user->name}.",
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Gagal mengirim pesan. Pastikan Bot Token sudah dikonfigurasi.',
        ], 500);
    }

    public function destroy(User $user)
    {
        // Prevent deletion of the current user
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak dapat menghapus akun Anda sendiri');
        }

        $user->delete();
        return redirect()->route('users.index')->with('success', 'User berhasil dihapus');
    }
}
