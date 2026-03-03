<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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

        $pythonPath = config('services.recommender.python_binary', PHP_OS_FAMILY === 'Windows' ? 'python' : 'python3');
        $scriptPath = base_path('recommender.py');

        $process = Process::env([
            'SYSTEMROOT' => getenv('SYSTEMROOT') ?: 'C:\\Windows',
            'WINDIR'     => getenv('WINDIR') ?: 'C:\\Windows',
            'PATH'       => getenv('PATH'),
            'TEMP'       => getenv('TEMP'),
            
            'PYTHONIOENCODING' => 'utf-8',
        ])->run([$pythonPath, $scriptPath, $gapText]);


        if ($process->successful()) {
            $output = $process->output();
            $recommendationData = json_decode($output, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Recommendation script returned invalid JSON.', [
                    'raw_output' => $output,
                    'json_error' => json_last_error_msg(),
                ]);

                $recommendations = [];
            }

            if (isset($recommendationData['error'])) {
                Log::error('Recommendation script returned an error.', [
                    'error' => $recommendationData['error'],
                ]);
                $recommendations = [];
            } elseif (!empty($recommendationData)) {
                $ids = array_column($recommendationData, 'id');
                
                if(!empty($ids)) {
                    $idsString = implode(',', $ids);
                    $recommendations = Training::whereIn('id', $ids)
                        ->orderByRaw("FIELD(id, $idsString)")
                        ->get();
                } else {
                    $recommendations = [];
                }
            } else {
                $recommendations = [];
            }
        } else {
            Log::error('Failed to execute recommendation script.', [
                'python_binary' => $pythonPath,
                'script_path' => $scriptPath,
                'error_output' => $process->errorOutput(),
                'standard_output' => $process->output(),
            ]);

            $recommendations = [];
        }

        return view('karyawan.rekomendasi', compact('recommendations', 'gapText'));
    }
}
