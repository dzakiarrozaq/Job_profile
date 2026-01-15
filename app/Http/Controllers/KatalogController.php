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

        if ($request->filled('categories') && is_array($request->categories)) {
            $query->whereIn('category', $request->categories);
        }

        if ($request->filled('levels') && is_array($request->levels)) {
            $query->whereIn('difficulty', $request->levels);
        }

        if ($request->filled('methods') && is_array($request->methods)) {
            $query->whereIn('method', $request->methods);
        }

        if ($request->filled('types') && is_array($request->types)) {
            $query->whereIn('type', $request->types);
        } 

        elseif ($request->filled('type') && $request->type !== 'all') {
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