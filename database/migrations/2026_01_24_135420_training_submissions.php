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
        Schema::create('training_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('training_id')->nullable()->constrained('trainings')->onDelete('set null');
            $table->string('title')->nullable();
            $table->string('provider')->nullable();
            $table->string('type', 50)->nullable();
            $table->enum('status', ['pending_supervisor', 'pending_lp', 'approved', 'rejected'])->default('pending_supervisor')->nullable();
            $table->date('submission_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training_submissions');
    }
};
