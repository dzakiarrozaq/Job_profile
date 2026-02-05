<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\CompetenciesImport;
use App\Models\CompetenciesMaster;
use App\Models\CompetencyKeyBehavior;
use Illuminate\Support\Facades\DB; 
use App\Imports\BehaviorDefinitionImport; 
use App\Imports\BehaviorMatrixImport;

class CompetencyController extends Controller
{
    // Halaman List Kompetensi
    public function index(Request $request)
    {
        $query = \App\Models\CompetenciesMaster::query();

        // Logika Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('competency_name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%"); // Ganti 'definition' jadi 'description'
            });
        }

        // Logika Filter Tipe
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Urutkan dan Paginate
        $competencies = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('admin.competencies.index', compact('competencies'));
    }

    // Proses Import Excel
    public function import(Request $request)
    {
        ini_set('memory_limit', '-1'); // Unlimited RAM (Hati-hati, pakai secukupnya)
        ini_set('max_execution_time', 0); // 5 Menit

        $request->validate([
            'file' => 'required|mimes:xlsx,csv,xls',
        ]);

        try {
            Excel::import(new CompetenciesImport, $request->file('file'));
            return back()->with('success', 'Data berhasil ditambahkan!');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal import: ' . $e->getMessage());
        }
    }

    // Hapus Kompetensi
    public function destroy($id)
    {
        try {
            CompetencyKeyBehavior::where('competency_master_id', $id)->delete();
            CompetenciesMaster::destroy($id);
            return back()->with('success', 'Kompetensi berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus: ' . $e->getMessage());
        }
    }

    // Halaman Edit Manual
    public function edit($id)
    {
        $competency = CompetenciesMaster::with('keyBehaviors')->findOrFail($id);
        return view('admin.competencies.edit', compact('competency'));
    }

    // Proses Update Manual
    public function update(Request $request, $id)
    {
        $request->validate([
            'competency_name' => 'required|string|max:255',
            'description'     => 'nullable|string',
            'behaviors'       => 'nullable|array', // Array of levels
        ]);

        $competency = CompetenciesMaster::findOrFail($id);
        
        // 1. Update Header
        $competency->update([
            'competency_name' => $request->competency_name,
            'description'     => $request->description,
        ]);

        // 2. Update Behaviors
        if ($request->has('behaviors')) {
            foreach ($request->behaviors as $level => $text) {
                
                // Hapus data lama di level ini
                $competency->keyBehaviors()->where('level', $level)->delete();

                if (trim($text) === '') continue;

                // KHUSUS PERILAKU (Level 0) - Perlu dipecah lagi baris per baris
                if ($level == 0) {
                    // Normalisasi Enter
                    $lines = explode("\n", str_replace("\r", "", $text));
                    
                    foreach ($lines as $line) {
                        $clean = trim($line);
                        // Hapus nomor di depan (1. A -> A)
                        $clean = preg_replace('/^[\d\-\)\.]+\s*/', '', $clean);
                        
                        if (!empty($clean)) {
                            $competency->keyBehaviors()->create([
                                'level' => 0,
                                'behavior' => $clean
                            ]);
                        }
                    }
                } 
                // KOMPETENSI TEKNIS (Level 1-5) - Simpan langsung
                else {
                    $competency->keyBehaviors()->create([
                        'level' => $level,
                        'behavior' => $text
                    ]);
                }
            }
        }

        return redirect()->route('admin.competencies.index')
            ->with('success', 'Kompetensi berhasil diperbarui!');
    }

    public function importBehaviorDefinition(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        try {
            Excel::import(new BehaviorDefinitionImport, $request->file('file'));
            return redirect()->back()->with('success', 'Master Definisi & Perilaku berhasil diimport!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal import: ' . $e->getMessage());
        }
    }

    public function importBehaviorMatrix(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        try {
            // Langsung panggil tanpa parameter type
            Excel::import(new BehaviorMatrixImport, $request->file('file'));
            
            return redirect()->back()->with('success', 'Matrix Struktural & Fungsional berhasil diimport sekaligus!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal import matrix: ' . $e->getMessage());
        }
    }
}