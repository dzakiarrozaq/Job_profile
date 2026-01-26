<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Str; 

class PositionController extends Controller
{
    /**
     * Menampilkan daftar posisi
     */
    public function index(Request $request)
    {
        $query = Position::with(['organization', 'atasan']);

        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $positions = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('admin.positions.index', compact('positions'));
    }

    /**
     * Menampilkan form tambah posisi baru
     */
    public function create()
    {
        $organizations = Organization::all(); // Data untuk dropdown Organization
        $parents = Position::all(); // Data untuk dropdown Atasan

        return view('admin.positions.create', compact('organizations', 'parents'));
    }

    /**
     * Menyimpan posisi baru ke database
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'           => 'required|string|max:255',
            'organization_id' => 'required|exists:organizations,id', // Wajib pilih organization
            'atasan_id'       => 'nullable|exists:positions,id', // Opsional (bisa null)
            'tipe'            => 'required|in:organik,outsourcing', // Validasi Tipe
        ]);

        if ($validated['tipe'] === 'outsourcing') {
            // Str::finish akan menambahkan ' (OS)' di akhir HANYA jika belum ada
            $validated['title'] = Str::finish($validated['title'], ' (OS)');
        }

        // 3. Simpan
        Position::create($validated);

        return redirect()->route('admin.positions.index')
            ->with('success', 'Posisi berhasil dibuat!');
    }

    /**
     * Menampilkan form edit
     */
    public function edit($id)
    {
        $position = Position::findOrFail($id);
        $organizations = Organization::all();
        
        // Ambil semua posisi KECUALI dirinya sendiri (untuk dropdown atasan)
        // Supaya tidak bisa memilih diri sendiri sebagai atasan (error loop)
        $parents = Position::where('id', '!=', $id)->get();

        return view('admin.positions.edit', compact('position', 'organizations', 'parents'));
    }

    /**
     * Update data posisi
     */
    public function update(Request $request, $id)
    {
        $position = Position::findOrFail($id);

        // 1. Validasi
        $validated = $request->validate([
            'title'           => 'required|string|max:255',
            'organization_id' => 'required|exists:organizations,id',
            'atasan_id'       => 'nullable|exists:positions,id',
            'tipe'            => 'required|in:organik,outsourcing',
        ]);

        // 2. LOGIKA OTOMATIS SAAT UPDATE
        if ($validated['tipe'] === 'outsourcing') {
            // Pastikan ada (OS)
            $validated['title'] = Str::finish($validated['title'], ' (OS)');
        } else {
            // Jika diubah jadi Organik, hapus tulisan (OS) supaya bersih
            $validated['title'] = str_replace(' (OS)', '', $validated['title']);
        }

        // 3. Update
        $position->update($validated);

        return redirect()->route('admin.positions.index')
            ->with('success', 'Posisi berhasil diperbarui!');
    }

    /**
     * Hapus posisi
     */
    public function destroy($id)
    {
        $position = Position::findOrFail($id);
        
        // Cek apakah posisi ini punya bawahan? (Opsional: mencegah hapus atasan yg punya bawahan)
        // if($position->bawahan()->count() > 0) {
        //     return back()->with('error', 'Gagal hapus! Posisi ini masih menjadi atasan posisi lain.');
        // }

        $position->delete();

        return redirect()->route('admin.positions.index')
            ->with('success', 'Posisi berhasil dihapus!');
    }
}