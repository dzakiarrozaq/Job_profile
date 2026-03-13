<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http; // <-- Pakai HTTP untuk tembak API
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Training;

class TrainingRecommendationController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // 1. Ambil data GAP (Kompetensi yang kurang) milik User
        $gapRecords = $user->gapRecords()->where('gap_value', '<', 0)->get();
        $gapText = $gapRecords->pluck('competency_name')->implode(', ');

        // Jika user tidak punya GAP, kembalikan halaman kosong
        if (empty($gapText)) {
            return view('karyawan.rekomendasi', ['recommendations' => [], 'gapText' => '']);
        }

        // 2. Ambil data Pelatihan dari Database Hostinger untuk dikirim ke Python
        $trainingsData = Training::select('id', 'title', 'competency_name', 'objective')->get()->toArray();

        // 3. URL API PythonAnywhere Anda
        $apiUrl = 'https://devhub.pythonanywhere.com/recommend';

        try {
            // 4. Kirim data DENGAN HEADER KTP agar tidak diblokir OpenResty (403 Forbidden)
            $response = Http::withoutVerifying() // Abaikan cek SSL ketat
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'Referer' => 'https://devhub.pythonanywhere.com/',
                    'Accept-Language' => 'id-ID,id;q=0.9,en-US;q=0.8,en;q=0.7',
                    'Connection' => 'keep-alive'
                ])
                ->timeout(20)
                ->post($apiUrl, [
                    'gap_text' => $gapText,
                    'trainings' => $trainingsData
                ]);

            // Cek apakah response dari PythonAnywhere itu gagal (misal 403, 500)
            if ($response->failed()) {
                Log::error('Python API Request Failed: ' . $response->body());
                $recommendations = [];
            } else {
                $recommendationData = $response->json();

                // 5. Olah Hasilnya
                if (isset($recommendationData['error']) || empty($recommendationData)) {
                    $recommendations = [];
                } else {
                    $ids = array_column($recommendationData, 'id');
                    
                    if(!empty($ids)) {
                        $idsString = implode(',', $ids);
                        $recommendations = Training::whereIn('id', $ids)
                            ->orderByRaw("FIELD(id, $idsString)")
                            ->get();
                    } else {
                        $recommendations = [];
                    }
                }
            }
        } catch (\Exception $e) {
            // Jika API Python error atau tidak bisa dihubungi, kosongkan rekomendasi
            Log::error('API Python Error: ' . $e->getMessage());
            $recommendations = [];
        }

        // 6. Tampilkan ke Halaman Web
        return view('karyawan.rekomendasi', compact('recommendations', 'gapText'));
    }
}