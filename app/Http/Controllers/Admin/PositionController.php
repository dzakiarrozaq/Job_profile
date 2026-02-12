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
        // Mulai Query
        $query = Position::query();

        // 1. Lakukan JOIN ke tabel organizations agar bisa sort berdasarkan nama unit
        $query->join('organizations', 'positions.organization_id', '=', 'organizations.id')
            ->select('positions.*') // PENTING: Ambil data posisi saja agar ID tidak bentrok
            ->orderBy('organizations.name', 'asc') // Urutkan A-Z berdasarkan Nama Unit
            ->orderBy('positions.title', 'asc');   // Opsi tambahan: Urutkan nama jabatan setelah unit

        // 2. Logika Pencarian (Jika ada search)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('positions.title', 'like', "%{$search}%")
                ->orWhere('organizations.name', 'like', "%{$search}%");
            });
        }

        // 3. Eager Load relasi (agar tidak N+1 query di view) & Paginate
        $positions = $query->with(['organization', 'atasan'])->paginate(10);

        return view('admin.positions.index', compact('positions'));
    }

    /**
     * Menampilkan form tambah posisi baru
     */
    public function create()
    {
        $organizations = Organization::all(); 
        $parents = Position::all(); 

        return view('admin.positions.create', compact('organizations', 'parents'));
    }

    /**
     * Menyimpan posisi baru ke database
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'           => 'required|string|max:255',
            'organization_id' => 'required|exists:organizations,id', 
            'atasan_id'       => 'nullable|exists:positions,id', 
            'tipe'            => 'required|in:organik,outsourcing', 
        ]);

        if ($validated['tipe'] === 'outsourcing') {
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
        
        $parents = Position::where('id', '!=', $id)->get();

        return view('admin.positions.edit', compact('position', 'organizations', 'parents'));
    }

    /**
     * Update data posisi
     */
    public function update(Request $request, $id)
    {
        $position = Position::findOrFail($id);

        $validated = $request->validate([
            'title'           => 'required|string|max:255',
            'organization_id' => 'required|exists:organizations,id',
            'atasan_id'       => 'nullable|exists:positions,id',
            'tipe'            => 'required|in:organik,outsourcing',
        ]);

        if ($validated['tipe'] === 'outsourcing') {
            $validated['title'] = Str::finish($validated['title'], ' (OS)');
        } else {
            $validated['title'] = str_replace(' (OS)', '', $validated['title']);
        }

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