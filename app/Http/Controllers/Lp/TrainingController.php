<?php

namespace App\Http\Controllers\Lp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Training; 

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
                      ->orWhere('provider', 'like', "%{$search}%");
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
            'title' => 'required|string|max:255',
            'provider' => 'required|string|max:255',
            
            'type' => 'required|in:Online,Offline,Hybrid', 
            
            'cost' => 'required|numeric',
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
            'title' => 'required|string|max:255',
            'provider' => 'required|string|max:255',
            
            'type' => 'required|in:Online,Offline,Hybrid', 
            
            'cost' => 'required|numeric',
            'description' => 'nullable|string',
        ]);

        $training->update($validated);

        return redirect()->route('lp.katalog.index')->with('success', 'Data pelatihan berhasil diperbarui.');
    }

    public function show($id)
    {
        $training = Training::findOrFail($id);
        
        return view('lp.katalog.show', compact('training'));
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
}