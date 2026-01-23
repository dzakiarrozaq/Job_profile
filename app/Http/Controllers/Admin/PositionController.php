<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Position;
use App\Models\Unit; // Pastikan Anda punya Model Unit
use Illuminate\Http\Request;
use Illuminate\Support\Str; // PENTING: Untuk fungsi Str::finish

class PositionController extends Controller
{
    /**
     * Menampilkan daftar posisi
     */
    public function index(Request $request)
    {
        // Query data posisi beserta relasi Unit dan Atasan
        $query = Position::with(['unit', 'parent']);

        // Fitur Pencarian
        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        // Urutkan dan Pagination
        $positions = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('admin.positions.index', compact('positions'));
    }

    /**
     * Menampilkan form tambah posisi baru
     */
    public function create()
    {
        $units = Unit::all(); // Data untuk dropdown Unit
        $parents = Position::all(); // Data untuk dropdown Atasan

        return view('admin.positions.create', compact('units', 'parents'));
    }

    /**
     * Menyimpan posisi baru ke database
     */
    public function store(Request $request)
    {
        // 1. Validasi Input
        $validated = $request->validate([
            'title'     => 'required|string|max:255',
            'unit_id'   => 'required|exists:units,id', // Wajib pilih unit
            'atasan_id' => 'nullable|exists:positions,id', // Opsional (bisa null)
            'tipe'      => 'required|in:organik,outsourcing', // Validasi Tipe
        ]);

        // 2. LOGIKA OTOMATIS: Tambah (OS) jika outsourcing
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
        $units = Unit::all();
        
        // Ambil semua posisi KECUALI dirinya sendiri (untuk dropdown atasan)
        // Supaya tidak bisa memilih diri sendiri sebagai atasan (error loop)
        $parents = Position::where('id', '!=', $id)->get();

        return view('admin.positions.edit', compact('position', 'units', 'parents'));
    }

    /**
     * Update data posisi
     */
    public function update(Request $request, $id)
    {
        $position = Position::findOrFail($id);

        // 1. Validasi
        $validated = $request->validate([
            'title'     => 'required|string|max:255',
            'unit_id'   => 'required|exists:units,id',
            'atasan_id' => 'nullable|exists:positions,id',
            'tipe'      => 'required|in:organik,outsourcing',
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
        // if($position->children()->count() > 0) {
        //     return back()->with('error', 'Gagal hapus! Posisi ini masih menjadi atasan posisi lain.');
        // }

        $position->delete();

        return redirect()->route('admin.positions.index')
            ->with('success', 'Posisi berhasil dihapus!');
    }
}