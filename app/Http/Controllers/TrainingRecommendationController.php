<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Training;

class TrainingRecommendationController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // 1. Ambil GAP Kompetensi User
        $gapRecords = $user->gapRecords()->where('gap_value', '<', 0)->get();
        $gapText = $gapRecords->pluck('competency_name')->implode(', ');

        if (empty($gapText)) {
            return view('karyawan.rekomendasi', ['recommendations' => [], 'gapText' => '']);
        }

        // 2. SETUP PATH & COMMAND
        $pythonPath = "C:\\Users\\Asus\\AppData\\Local\\Programs\\Python\\Python312\\python.exe";
        $scriptPath = base_path('recommender.py');
        
        // Escape argumen agar aman dari spasi/karakter aneh
        $command = "\"{$pythonPath}\" \"{$scriptPath}\" \"" . addslashes($gapText) . "\"";

        // ============================================================
        // [PERBAIKAN DISINI]
        // Jalankan HANYA SEKALI menggunakan Process::env
        // ============================================================
        $process = Process::env([
            // Variabel Windows Wajib
            'SYSTEMROOT' => getenv('SYSTEMROOT') ?: 'C:\\Windows',
            'WINDIR'     => getenv('WINDIR') ?: 'C:\\Windows',
            'PATH'       => getenv('PATH'),
            'TEMP'       => getenv('TEMP'),
            
            // Variabel Python agar stabil
            'PYTHONIOENCODING' => 'utf-8',
        ])->run($command);

        // ❌ HAPUS BARIS INI: $process = Process::run($command); 
        // Baris di atas (yg dihapus) adalah penyebab kenapa settingan env anda hilang tertimpa.

        // 3. TANGKAP HASIL
        if ($process->successful()) {
            $output = $process->output();
            $recommendationData = json_decode($output, true);

            // Cek validitas JSON
            if (json_last_error() !== JSON_ERROR_NONE) {
                dd([
                    'Status' => 'Output bukan JSON Valid',
                    'Raw Output' => $output, // Biasanya berisi pesan warning Python
                    'Json Error' => json_last_error_msg()
                ]);
            }

            if (isset($recommendationData['error'])) {
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
        } else {
            // Debugging jika Gagal
            dd([
                'Status' => 'Script Python Gagal Dijalankan',
                'Command' => $command,
                'Error Output' => $process->errorOutput(),
                'Standard Output' => $process->output(),
            ]);
        }

        return view('karyawan.rekomendasi', compact('recommendations', 'gapText'));
    }
}