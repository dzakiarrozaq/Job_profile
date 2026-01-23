<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Training;
use Illuminate\Http\Request;
use App\Imports\TrainingsImport;
use Maatwebsite\Excel\Facades\Excel;

class AdminTrainingController extends Controller
{
    
    // Menampilkan Form Tambah
    public function create()
    {
        return view('admin.trainings.create');
    }

    // Menyimpan Data ke Database
    public function store(Request $request)
    {
        // 1. Validasi Input
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'provider'    => 'required|string|max:255',
            'type'        => 'required|in:internal,external',
            'difficulty'  => 'required|in:Beginner,Intermediate,Advanced',
            'description' => 'required|string',
            'duration'    => 'nullable|string|max:50', // Misal: "2 Jam", "3 Hari"
            'link_url'    => 'nullable|url', // Jika ada link ke course external
        ]);

        // 2. Tambahkan data default (Admin input pasti langsung approved)
        $validated['status'] = 'approved';

        // 3. Simpan
        Training::create($validated);

        // 4. Redirect dengan pesan sukses
        return redirect()->route('katalog') // Atau route list admin jika ada
                         ->with('success', 'Pelatihan berhasil ditambahkan ke katalog!');
    }

    public function index(Request $request)
    {
        // Ambil data dengan fitur pencarian sederhana
        $query = Training::query();

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('provider', 'like', '%' . $request->search . '%');
        }

        // Urutkan dari yang terbaru, paginasi 10 item per halaman
        $trainings = $query->latest()->paginate(10)->withQueryString();

        return view('admin.trainings.index', compact('trainings'));
    }

    public function edit($id)
    {
        $training = Training::findOrFail($id);
        return view('admin.trainings.edit', compact('training'));
    }

    // [BARU] Menyimpan Perubahan (Update)
    public function update(Request $request, $id)
    {
        $training = Training::findOrFail($id);

        // 1. Validasi (Sama seperti store, tapi sesuaikan sedikit jika perlu)
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'provider'    => 'required|string|max:255',
            'type'        => 'required|in:internal,external',
            'difficulty'  => 'required|in:Beginner,Intermediate,Advanced',
            'description' => 'required|string',
            'duration'    => 'nullable|string|max:50',
            'link_url'    => 'nullable|url',
        ]);

        // 2. Update Data
        $training->update($validated);

        // 3. Redirect kembali ke Index
        return redirect()->route('admin.trainings.index')
                         ->with('success', 'Pelatihan berhasil diperbarui!');
    }
    public function import(Request $request) 
    {
        // 1. Validasi File
        $request->validate([
            'file' => 'required|mimes:xlsx,csv,xls'
        ]);

        // 2. Proses Import
        try {
            Excel::import(new TrainingsImport, $request->file('file'));
            
            return back()->with('success', 'Data pelatihan berhasil diimpor!');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal impor data: ' . $e->getMessage());
        }
    }
}