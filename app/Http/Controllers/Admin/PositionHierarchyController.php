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
        $rootPositions = Position::whereNull('atasan_id')
            ->with(['bawahanRecursive', 'organization']) 
            ->orderBy('title')
            ->get();

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

        if ($position->id == $newParentId) {
            return back()->with('error', 'Tidak bisa menjadikan diri sendiri sebagai atasan.');
        }

        if ($newParentId) {
            if ($this->isDescendant($position->id, $newParentId)) {
                return back()->with('error', 'Gagal! Posisi tujuan adalah bawahan dari posisi ini (Circular Loop).');
            }
        }

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
        $childrenIds = Position::where('atasan_id', $sourceId)->pluck('id')->toArray();

        if (in_array($targetId, $childrenIds)) {
            return true;
        }

        foreach ($childrenIds as $childId) {
            if ($this->isDescendant($childId, $targetId)) {
                return true;
            }
        }

        return false;
    }
}