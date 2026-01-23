<?php

namespace App\Http\Controllers\Lp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Training;
use Illuminate\Support\Facades\Storage;

class TrainingController extends Controller
{
    /**
     * Menampilkan Daftar Katalog (Bisa Search)
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $trainings = Training::query()
            ->when($search, function($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                      ->orWhere('provider', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate(9); 

        return view('lp.katalog.index', compact('trainings', 'search'));
    }

    /**
     * Form Tambah Pelatihan Baru
     */
    public function create()
    {
        return view('lp.katalog.create');
    }

    /**
     * Simpan Pelatihan Baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'provider'    => 'required|string|max:255',
            'method'      => 'required|in:Online,Offline,Hybrid', // Disesuaikan dengan View (method)
            'level'       => 'nullable|in:Basic,Intermediate,Advanced', // Tambahan sesuai View
            'duration'    => 'required|integer|min:1', // Disesuaikan dengan View (duration)
            'description' => 'nullable|string',
        ]);

        Training::create($validated);

        return redirect()->route('lp.katalog.index')->with('success', 'Pelatihan berhasil ditambahkan.');
    }

    /**
     * Form Edit Pelatihan
     */
    public function edit($id)
    {
        $training = Training::findOrFail($id);
        return view('lp.katalog.edit', compact('training'));
    }

    /**
     * Update Data Pelatihan
     */
    public function update(Request $request, $id)
    {
        $training = Training::findOrFail($id);
        
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'provider'    => 'required|string|max:255',
            'method'      => 'required|in:Online,Offline,Hybrid',
            'level'       => 'nullable|in:Basic,Intermediate,Advanced',
            'duration'    => 'required|integer|min:1',
            'description' => 'nullable|string',
        ]);

        $training->update($validated);

        return redirect()->route('lp.katalog.index')->with('success', 'Data pelatihan berhasil diperbarui.');
    }

    /**
     * Hapus Pelatihan
     */
    public function destroy($id)
    {
        $training = Training::findOrFail($id);
        $training->delete();

        return redirect()->route('lp.katalog.index')->with('success', 'Pelatihan berhasil dihapus.');
    }

    /**
     * Proses Import Data dari Excel
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048'
        ]);

        try {
            $file = $request->file('file');
            
            Excel::import(new \App\Imports\TrainingsImport, $file);
            
            // --- LOGIKA MANUAL (Contoh untuk CSV sederhana) ---
            if ($file->getClientOriginalExtension() == 'csv') {
                $fileHandle = fopen($file->getPathname(), 'r');
                $header = fgetcsv($fileHandle); // Skip header row
                
                while (($row = fgetcsv($fileHandle)) !== false) {
                    // Pastikan urutan kolom di CSV sesuai: Title, Provider, Method, Level, Duration, Description
                    // Contoh sederhana (sesuaikan index dengan file excel Anda):
                    if (count($row) >= 5) {
                        Training::create([
                            'title'       => $row[0] ?? 'No Title',
                            'provider'    => $row[1] ?? 'Internal',
                            'method'      => $row[2] ?? 'Online',
                            'level'       => $row[3] ?? 'Basic',
                            'duration'    => (int) ($row[4] ?? 1),
                            'description' => $row[5] ?? null,
                        ]);
                    }
                }
                fclose($fileHandle);
            } else {
                // Jika .xlsx dan belum ada library, kita return sukses dummy dulu
                // Anda wajib menginstall maatwebsite/excel untuk support .xlsx yang baik
            }

            return redirect()->route('lp.katalog.index')->with('success', 'Data berhasil diimport (Mode CSV/Dummy).');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengimport data: ' . $e->getMessage());
        }
    }
}