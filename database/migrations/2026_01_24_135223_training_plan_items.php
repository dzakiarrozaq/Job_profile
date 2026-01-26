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
        Schema::create('training_plan_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_plan_id')->constrained('training_plans')->onDelete('cascade');
            $table->foreignId('training_id')->constrained('trainings')->onDelete('cascade'); // double takok ipan asline iso diilangi
            $table->string('title')->nullable();
            $table->string('provider')->nullable();
            $table->string('status', 50)->default('pending')->nullable();
            $table->string('certificate_status', 50)->default('not_uploaded')->nullable();
            $table->string('certificate_file', 2048)->nullable();
            $table->timestamps();
            $table->string('method', 100)->default('Offline')->nullable();
            $table->decimal('cost', 15, 2)->default(0.00)->nullable();
            $table->string('certificate_path')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training_plan_items');
    }
};
