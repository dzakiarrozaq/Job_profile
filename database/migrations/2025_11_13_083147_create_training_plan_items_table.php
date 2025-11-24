<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('training_plan_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_plan_id')->constrained('training_plans')->onDelete('cascade');
            $table->foreignId('training_id')->nullable()->constrained('trainings')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('training_plan_items');
    }
};