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
    public function index(Request $request)
    {
        $query = CompetenciesMaster::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('competency_name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%"); 
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $competencies = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('admin.competencies.index', compact('competencies'));
    }

    public function import(Request $request)
    {
        ini_set('memory_limit', '-1'); 
        ini_set('max_execution_time', 0); 

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

    public function edit($id)
    {
        $competency = CompetenciesMaster::with('keyBehaviors')->findOrFail($id);
        return view('admin.competencies.edit', compact('competency'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'competency_name' => 'required|string|max:255',
            'description'     => 'nullable|string',
            'behaviors'       => 'nullable|array', 
        ]);

        $competency = CompetenciesMaster::findOrFail($id);
        
        $competency->update([
            'competency_name' => $request->competency_name,
            'description'     => $request->description,
        ]);

        if ($request->has('behaviors')) {
            foreach ($request->behaviors as $level => $text) {
                
                $competency->keyBehaviors()->where('level', $level)->delete();

                if (trim($text) === '') continue;

                if ($level == 0) {
                    $lines = explode("\n", str_replace("\r", "", $text));
                    
                    foreach ($lines as $line) {
                        $clean = trim($line);
                        $clean = preg_replace('/^[\d\-\)\.]+\s*/', '', $clean);
                        
                        if (!empty($clean)) {
                            $competency->keyBehaviors()->create([
                                'level' => 0,
                                'behavior' => $clean
                            ]);
                        }
                    }
                } 
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
            Excel::import(new BehaviorMatrixImport, $request->file('file'));
            
            return redirect()->back()->with('success', 'Matrix Struktural & Fungsional berhasil diimport sekaligus!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal import matrix: ' . $e->getMessage());
        }
    }
}