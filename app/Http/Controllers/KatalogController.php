<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Training;

class KatalogController extends Controller
{
    public function index(Request $request)
    {
        $query = Training::query()->where('status', 'approved');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('provider', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        $trainings = $query->latest()->paginate(9)->withQueryString();

        return view('karyawan.katalog.index', compact('trainings'));
    }
    
    public function show($id)
    {
        $training = Training::findOrFail($id);
        return view('karyawan.katalog.show', compact('training'));
    }
}