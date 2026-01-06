<?php

namespace App\Http\Controllers\Lp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TrainingPlan;
use Carbon\Carbon;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        // 1. Ambil Input Filter (Default: Bulan ini)
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate   = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $status    = $request->input('status', 'all');

        // 2. Query Dasar
        $query = TrainingPlan::with(['user.position', 'items'])
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

        // 3. Filter Status jika dipilih
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        // 4. Ambil Data untuk Statistik (Sebelum dipaginate)
        $allData = $query->get();

        $summary = [
            'total_pengajuan' => $allData->count(),
            'total_disetujui' => $allData->where('status', 'approved')->count(),
            'total_ditolak'   => $allData->where('status', 'rejected')->count(),
            'total_pending'   => $allData->whereIn('status', ['pending_supervisor', 'pending_lp'])->count(),
            // Hitung total biaya dari semua item di dalam plan
            'total_biaya'     => $allData->sum(function ($plan) {
                return $plan->items->sum('cost');
            }),
        ];

        // 5. Data Tabel (Paginate)
        $trainings = $query->latest()->paginate(10)->withQueryString();

        return view('lp.laporan.index', compact('trainings', 'summary', 'startDate', 'endDate', 'status'));
    }
}