<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('gap_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('job_profile_id')->constrained('job_profiles')->onDelete('cascade');
            $table->string('competency_code');
            $table->string('competency_name')->nullable(); // <-- Ditambahkan dari perbaikan kita
            $table->integer('ideal_level');
            $table->integer('current_level');
            $table->float('gap_value');
            $table->float('weighted_gap');
            $table->timestamp('calculated_at')->useCurrent();
            
            $table->unique(['user_id', 'competency_code', 'job_profile_id'], 'user_job_competency_unique');
        });
    }
    public function down(): void {
        Schema::dropIfExists('gap_records');
    }
};