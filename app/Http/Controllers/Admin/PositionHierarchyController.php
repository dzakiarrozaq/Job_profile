<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Position;
use Illuminate\Http\Request;

class PositionHierarchyController extends Controller
{
    /**
     * Menampilkan Struktur Hierarki (Tree View)
     */
    public function index()
    {
        // 1. Ambil Posisi Root (yang tidak punya atasan / atasan_id NULL)
        // 2. Load relasi 'bawahanRecursive' (untuk struktur pohon) & 'organization'
        // PENTING: Jangan gunakan 'parent' atau 'unit' di sini agar tidak error
        $rootPositions = Position::whereNull('atasan_id')
            ->with(['bawahanRecursive', 'organization']) 
            ->orderBy('title')
            ->get();

        // Ambil semua posisi untuk dropdown "Pindah Atasan" di Modal
        $allPositions = Position::orderBy('title')->get();

        return view('admin.positions.hierarchy', compact('rootPositions', 'allPositions'));
    }

    /**
     * Proses Memindahkan Atasan (Update Parent)
     */
    public function updateParent(Request $request)
    {
        $request->validate([
            'position_id' => 'required|exists:positions,id',
            'new_parent_id' => 'nullable|exists:positions,id',
        ]);

        $position = Position::findOrFail($request->position_id);
        $newParentId = $request->new_parent_id;

        // Validasi 1: Tidak boleh menjadikan diri sendiri sebagai atasan
        if ($position->id == $newParentId) {
            return back()->with('error', 'Tidak bisa menjadikan diri sendiri sebagai atasan.');
        }

        // Validasi 2: Mencegah Circular Loop (Bawahan tidak boleh jadi atasan dari Boss-nya)
        if ($newParentId) {
            if ($this->isDescendant($position->id, $newParentId)) {
                return back()->with('error', 'Gagal! Posisi tujuan adalah bawahan dari posisi ini (Circular Loop).');
            }
        }

        // Simpan Perubahan ke kolom 'atasan_id'
        $position->atasan_id = $newParentId;
        $position->save();

        return back()->with('success', 'Struktur hierarki berhasil diperbarui.');
    }

    /**
     * Helper: Cek apakah targetId adalah bawahan dari sourceId (Secara Rekursif)
     * Digunakan untuk mencegah Loop.
     */
    private function isDescendant($sourceId, $targetId)
    {
        // Cari ID semua bawahan langsung menggunakan kolom atasan_id
        $childrenIds = Position::where('atasan_id', $sourceId)->pluck('id')->toArray();

        // Cek level 1
        if (in_array($targetId, $childrenIds)) {
            return true;
        }

        // Cek level berikutnya (rekursif)
        foreach ($childrenIds as $childId) {
            if ($this->isDescendant($childId, $targetId)) {
                return true;
            }
        }

        return false;
    }
}