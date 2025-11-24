<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('training_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->string('status', 50)->default('draft');
            $table->timestamp('supervisor_approved_at')->nullable();
            $table->foreignId('lp_approver_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('lp_approved_at')->nullable();
            $table->text('rejection_notes')->nullable();
            $table->timestamps(); // <-- Menambahkan created_at/updated_at
        });
    }
    public function down(): void {
        Schema::dropIfExists('training_plans');
    }
};