<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('employee_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('competency_code');
            $table->string('competency_name')->nullable();
            $table->integer('current_level')->default(0)->comment('Level kompetensi yang sudah diverifikasi');
            $table->integer('submitted_level')->nullable()->comment('Level yang diajukan oleh karyawan');
            $table->string('status', 50)->default('draft');
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->text('reviewer_notes')->nullable();
            
            $table->unique(['user_id', 'competency_code']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('employee_profiles');
    }
};