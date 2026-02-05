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
        Schema::create('competency_matrices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('job_grade_id'); // Relasi ke Job Grade
            $table->unsignedBigInteger('competency_master_id'); // Relasi ke Master Kompetensi
            $table->string('type'); // 'structural' atau 'functional'
            $table->timestamps();
            
            // Optional: Foreign Keys
            $table->foreign('job_grade_id')->references('id')->on('job_grades');
            $table->foreign('competency_master_id')->references('id')->on('competencies_master');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('competency_matrices');
        
    }
};
