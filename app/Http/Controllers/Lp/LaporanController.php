<?php

namespace App\Http\Controllers\Lp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TrainingPlan;
use App\Exports\LaporanTrainingExport; 
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate   = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $status    = $request->input('status', 'all');

        $query = TrainingPlan::with(['user.position', 'items'])
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $allData = $query->get();

        $summary = [
            'total_pengajuan' => $allData->count(),
            'total_disetujui' => $allData->where('status', 'approved')->count(),
            'total_ditolak'   => $allData->where('status', 'rejected')->count(),
            'total_pending'   => $allData->whereIn('status', ['pending_supervisor', 'pending_lp'])->count(),
            'total_biaya'     => $allData->sum(function ($plan) {
                return $plan->items->sum('cost');
            }),
        ];

        $trainings = $query->latest()->paginate(10)->withQueryString();

        return view('lp.laporan.index', compact('trainings', 'summary', 'startDate', 'endDate', 'status'));
    }

    public function export(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate   = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $status    = $request->input('status', 'all');

        return Excel::download(
            new LaporanTrainingExport($startDate, $endDate, $status), 
            'Laporan-Training-'.$startDate.'-sd-'.$endDate.'.xlsx'
        );
    }

    public function show($id)
    {
        $plan = \App\Models\TrainingPlan::with(['user.position', 'items', 'user.manager'])
            ->findOrFail($id);

        return view('lp.laporan.show', compact('plan'));
    }
}