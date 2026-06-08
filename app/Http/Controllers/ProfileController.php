<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function index()
    {
        $user = auth()->guard('web')->user();
        return view('profile.index', compact('user'));
    }

    public function update(Request $request)
    {
        $user = auth()->guard('web')->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'current_password' => 'nullable|required_with:new_password|current_password',
            'new_password' => 'nullable|min:8|confirmed',
        ]);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
        ];

        if (!empty($validated['new_password'])) {
            $updateData['password'] = Hash::make($validated['new_password']);
        }

        $user->update($updateData);

        return redirect()->route('profile.index')->with('success', 'Profil berhasil diperbarui');
    }

    public function destroy()
    {
        $user = auth()->guard('web')->user();

        if ($user->hasRole('super-admin')) {
            return back()->with('error', 'Akun super-admin tidak dapat dihapus');
        }

        auth()->guard('web')->logout();
        $user->delete();

        return redirect()->route('login')->with('success', 'Akun berhasil dihapus');
    }
}
