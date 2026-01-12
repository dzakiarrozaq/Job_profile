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

        $gapRecords = $user->gapRecords()->where('gap_value', '<', 0)->get();
        $gapText = $gapRecords->pluck('competency_name')->implode(', ');

        if (empty($gapText)) {
            return view('karyawan.rekomendasi', ['recommendations' => [], 'gapText' => '']);
        }

        $pythonPath = "C:\\Users\\Asus\\AppData\\Local\\Programs\\Python\\Python312\\python.exe";
        $scriptPath = base_path('recommender.py');
        
        $command = "\"{$pythonPath}\" \"{$scriptPath}\" \"" . addslashes($gapText) . "\"";

        
        $process = Process::env([
            'SYSTEMROOT' => getenv('SYSTEMROOT') ?: 'C:\\Windows',
            'WINDIR'     => getenv('WINDIR') ?: 'C:\\Windows',
            'PATH'       => getenv('PATH'),
            'TEMP'       => getenv('TEMP'),
            
            'PYTHONIOENCODING' => 'utf-8',
        ])->run($command);


        if ($process->successful()) {
            $output = $process->output();
            $recommendationData = json_decode($output, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                dd([
                    'Status' => 'Output bukan JSON Valid',
                    'Raw Output' => $output, 
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