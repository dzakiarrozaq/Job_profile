<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('master_responsibilities', function (Blueprint $table) {
            $table->id();
            // Relasi ke JobGrade (Band 1, 2, 3...)
            $table->foreignId('job_grade_id')->constrained('job_grades')->cascadeOnDelete();
            
            // Tipe: Struktural / Fungsional / General (untuk Band 5 yg tidak spesifik)
            $table->enum('type', ['structural', 'functional', 'general'])->default('structural');
            
            $table->text('responsibility'); // Kolom Tanggung Jawab
            $table->text('expected_outcome'); // Kolom Hasil yang Diharapkan
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_responsibilities');
    }
};
