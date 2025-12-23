<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Training; // Pastikan Model Training diimport

class KatalogController extends Controller
{
    public function index(Request $request)
    {
        // 1. Mulai Query (Hanya tampilkan yang statusnya approved/aktif)
        $query = Training::query()->where('status', 'approved');

        // 2. Logika Pencarian (Search)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('provider', 'like', "%{$search}%");
            });
        }

        // 3. Logika Filter Tipe (Internal/External)
        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        // 4. Ambil data dengan Pagination (9 item per halaman)
        $trainings = $query->latest()->paginate(9)->withQueryString();

        return view('karyawan.katalog.index', compact('trainings'));
    }
    
    // Method untuk show detail (opsional, jika mau halaman detail terpisah)
    public function show($id)
    {
        $training = Training::findOrFail($id);
        return view('karyawan.katalog.show', compact('training'));
    }
}