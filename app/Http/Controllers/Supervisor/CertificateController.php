<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TrainingPlanItem;
use App\Models\AuditLog; // Jangan lupa audit log biar aman
use Illuminate\Support\Facades\Auth;
use App\Notifications\StatusDiperbarui;

class CertificateController extends Controller
{
    public function index()
    {
        $items = TrainingPlanItem::whereHas('plan.user', function($q) {
                $q->where('manager_id', Auth::id());
            })
            ->where('certificate_status', 'pending_approval')
            ->with(['plan.user', 'training'])
            ->latest()
            ->paginate(10);

        return view('supervisor.sertifikat.index', compact('items'));
    }

    public function approve($id)
    {
        $item = TrainingPlanItem::findOrFail($id);
        
        $item->update([
            'certificate_status' => 'verified'
        ]);

        $item->plan->update([
            'status' => 'completed',
            'completed_at' => now() // Bagus untuk report durasi
        ]);

        AuditLog::record('VERIFY CERTIFICATE', 'Memvalidasi kelulusan pelatihan: ' . $item->title, $item->plan);

        $item->plan->user->notify(new StatusDiperbarui(
            'Sertifikat Valid', // Judul Notifikasi
            'Selamat! Sertifikat pelatihan "' . $item->title . '" telah divalidasi. Status pelatihan kini Selesai.', // Pesan
            route('riwayat'), // Link saat diklik (Ke halaman Riwayat)
            'success' // Tipe (Ikon Hijau)
        ));

        return back()->with('success', 'Sertifikat divalidasi. Pelatihan dinyatakan SELESAI.');
    }

    public function reject(Request $request, $id)
    {
        $item = TrainingPlanItem::findOrFail($id);
        
        $item->update([
            'certificate_status' => 'rejected'
        ]);

        AuditLog::record('REJECT CERTIFICATE', 'Menolak sertifikat pelatihan: ' . $item->title, $item->plan);

        $item->plan->user->notify(new StatusDiperbarui(
            'Sertifikat Ditolak', // Judul Notifikasi
            'Maaf, sertifikat pelatihan "' . $item->title . '" ditolak. Mohon periksa kembali file Anda dan upload ulang.', // Pesan
            route('rencana.sertifikat', $item->id), // Link saat diklik (Langsung ke halaman Upload Ulang)
            'error' // Tipe (Ikon Merah)
        ));

        return back()->with('error', 'Sertifikat ditolak. Karyawan diminta upload ulang.');
    }
}