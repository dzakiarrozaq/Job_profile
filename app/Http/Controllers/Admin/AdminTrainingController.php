<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Training;
use Illuminate\Http\Request;
use App\Imports\TrainingsImport;
use Maatwebsite\Excel\Facades\Excel;

class AdminTrainingController extends Controller
{
    
    public function create()
    {
        return view('admin.trainings.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'provider'    => 'required|string|max:255',
            'type'        => 'required|in:internal,external',
            'difficulty'  => 'required|in:Beginner,Intermediate,Advanced',
            'description' => 'required|string',
            'duration'    => 'nullable|string|max:50', 
            'link_url'    => 'nullable|url', 
        ]);

        $validated['status'] = 'approved';

        Training::create($validated);

        return redirect()->route('katalog') 
                         ->with('success', 'Pelatihan berhasil ditambahkan ke katalog!');
    }

    public function index(Request $request)
    {
        $query = Training::query();

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('provider', 'like', '%' . $request->search . '%');
        }

        $trainings = $query->latest()->paginate(10)->withQueryString();

        return view('admin.trainings.index', compact('trainings'));
    }

    public function edit($id)
    {
        $training = Training::findOrFail($id);
        return view('admin.trainings.edit', compact('training'));
    }

    public function update(Request $request, $id)
    {
        $training = Training::findOrFail($id);

        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'provider'    => 'required|string|max:255',
            'type'        => 'required|in:internal,external',
            'difficulty'  => 'required|in:Beginner,Intermediate,Advanced',
            'description' => 'required|string',
            'duration'    => 'nullable|string|max:50',
            'link_url'    => 'nullable|url',
        ]);

        $training->update($validated);

        return redirect()->route('admin.trainings.index')
                         ->with('success', 'Pelatihan berhasil diperbarui!');
    }
    public function import(Request $request) 
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv,xls'
        ]);

        try {
            Excel::import(new TrainingsImport, $request->file('file'));
            
            return back()->with('success', 'Data pelatihan berhasil diimpor!');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal impor data: ' . $e->getMessage());
        }
    }
}