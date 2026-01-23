<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Training;
use App\Models\GapRecord; 

class KatalogController extends Controller
{
    public function index(Request $request)
    {
        // 1. QUERY FILTER PELATIHAN
        $query = Training::query()->where('status', 'approved');

        // Filter 1: Search (Tetap Ada)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  // Cari di Objective DAN Content
                  ->orWhere('objective', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%")
                  ->orWhere('provider', 'like', "%{$search}%")
                  ->orWhere('competency_name', 'like', "%{$search}%");
            });
        }

        // Filter 2: Level (Satu-satunya filter tambahan)
        if ($request->filled('levels') && is_array($request->levels)) {
            $query->whereIn('level', $request->levels);
        }

        // Filter Kategori, Method, Type -> DIHAPUS

        $trainings = $query->latest()->paginate(9)->withQueryString();


        // 2. LOGIC GAP KOMPETENSI (DATA DARI GAP_RECORDS - TETAP SAMA)
        $user = auth()->user();
        $rawGaps = $user->gapRecords; 

        $competencyGaps = collect([]);

        foreach ($rawGaps as $record) {
            $type = '-'; 
            $competencyGaps->push((object) [
                'name'   => $record->competency_name,
                'type'   => $type,
                'target' => $record->ideal_level,
                'actual' => $record->current_level, 
                'gap'    => $record->gap_value      
            ]);
        }

        return view('karyawan.katalog.index', compact('trainings', 'competencyGaps'));
    }
    
    public function show($id)
    {
        $training = Training::findOrFail($id);
        return view('karyawan.katalog.show', compact('training'));
    }
}