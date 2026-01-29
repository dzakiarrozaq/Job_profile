<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('idps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->year('year');
            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected'])->default('draft');
            
            // Approval Info
            $table->foreignId('manager_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();

            // --- KOLOM BARU SESUAI UI ---
            
            // 1. Posisi Suksesi (Header)
            $table->string('successor_position')->nullable(); 

            // 2. Career Aspiration (Bagian A & B)
            // Kita simpan sebagai JSON agar bisa menampung array data dari form
            // Isinya nanti: { "a": [...], "b": [...] }
            $table->json('career_aspirations')->nullable(); 

            // Kolom lama ini dihapus saja karena sudah diganti JSON di atas agar lebih rapi
            // $table->string('career_preference'); // HAPUS
            // $table->string('career_interest'); // HAPUS
            // $table->string('future_job_interest'); // HAPUS

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('idps');
    }
};
