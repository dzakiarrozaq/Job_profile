<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('training_evidences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_plan_id')->nullable()->constrained('training_plans')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('filename')->nullable();
            $table->text('file_path');
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->string('status', 50)->default('pending');
            $table->timestamps(); // <-- Menambahkan created_at/updated_at
        });
    }
    public function down(): void {
        Schema::dropIfExists('training_evidences');
    }
};