<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('trainings', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('provider')->nullable();
            $table->enum('type', ['internal', 'external']);
            $table->text('description')->nullable();
            $table->text('link')->nullable();
            $table->string('tags')->nullable();
            $table->integer('duration_hours')->nullable();
            $table->string('skill_tags')->nullable();
            $table->string('status', 50)->default('draft');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            
            // Persetujuan 2 Tahap (sesuai perbaikan kita)
            $table->foreignId('supervisor_approver_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('supervisor_approved_at')->nullable();
            $table->foreignId('lp_approver_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('lp_approved_at')->nullable();
            $table->text('rejection_notes')->nullable();

            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('trainings');
    }
};