<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\CompetenciesImport;
use App\Models\CompetenciesMaster;
use App\Models\CompetencyKeyBehavior;
use Illuminate\Support\Facades\DB; // <--- WAJIB ADA BARIS INI!

class CompetencyController extends Controller
{
    // Halaman List Kompetensi
    public function index()
    {
        $competencies = CompetenciesMaster::with('keyBehaviors')->paginate(10);
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
            'behaviors'       => 'array',
            'behaviors.*'     => 'nullable|string',
        ]);

        try {
            // DB Transaction digunakan di sini, makanya butuh 'use Illuminate\Support\Facades\DB;'
            DB::transaction(function () use ($request, $id) {
                
                // 1. Update Master
                $competency = CompetenciesMaster::findOrFail($id);
                $competency->update([
                    'competency_name' => $request->competency_name,
                    'description'     => $request->description,
                ]);

                // 2. Update Behaviors (Level 1-5)
                foreach ($request->behaviors as $level => $text) {
                    $cleanText = trim($text);

                    if (!empty($cleanText)) {
                        CompetencyKeyBehavior::updateOrCreate(
                            ['competency_master_id' => $id, 'level' => $level],
                            ['behavior' => $cleanText]
                        );
                    } else {
                        // Jika dikosongkan, hapus
                        CompetencyKeyBehavior::where('competency_master_id', $id)
                            ->where('level', $level)
                            ->delete();
                    }
                }
            });

            return redirect()->route('admin.competencies.index')->with('success', 'Kompetensi berhasil diperbarui.');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal update: ' . $e->getMessage())->withInput();
        }
    }
}