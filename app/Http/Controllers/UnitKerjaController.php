<?php

namespace App\Http\Controllers;

use App\Models\UnitKerja;
use Illuminate\Http\Request;

class UnitKerjaController extends Controller
{
    public function index()
    {
        $units = UnitKerja::withCount('users')->paginate(15);
        return view('unit-kerja.index', compact('units'));
    }

    public function create()
    {
        return view('unit-kerja.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode' => 'required|unique:unit_kerjas|max:20',
            'nama' => 'required|string|max:255',
            'deskripsi' => 'nullable|string|max:500',
        ]);

        UnitKerja::create($validated);

        return redirect()->route('unit-kerja.index')
            ->with('success', 'Unit kerja berhasil ditambahkan');
    }

    public function edit(UnitKerja $unitKerja)
    {
        return view('unit-kerja.edit', compact('unitKerja'));
    }

    public function update(Request $request, UnitKerja $unitKerja)
    {
        $validated = $request->validate([
            'kode' => 'required|max:20|unique:unit_kerjas,kode,' . $unitKerja->id,
            'nama' => 'required|string|max:255',
            'deskripsi' => 'nullable|string|max:500',
        ]);

        $unitKerja->update($validated);

        return redirect()->route('unit-kerja.index')
            ->with('success', 'Unit kerja berhasil diperbarui');
    }

    public function destroy(UnitKerja $unitKerja)
    {
        if ($unitKerja->users()->count() > 0) {
            return back()->with('error', 'Unit kerja memiliki pengguna. Pindahkan pengguna terlebih dahulu.');
        }

        $unitKerja->delete();
        return redirect()->route('unit-kerja.index')
            ->with('success', 'Unit kerja berhasil dihapus');
    }
}
