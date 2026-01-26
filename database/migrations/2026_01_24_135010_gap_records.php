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
        Schema::create('gap_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('job_profile_id')->constrained('job_profiles')->onDelete('cascade');
            $table->string('competency_code');
            $table->string('competency_name')->nullable();
            $table->integer('ideal_level')->default(0)->nullable();
            $table->integer('weight')->default(1)->nullable();
            $table->integer('current_level')->default(0)->nullable();
            $table->integer('gap_value')->default(0)->nullable();
            $table->decimal('weighted_gap', 10, 2)->default(0.00)->nullable();
            $table->timestamp('calculated_at')->useCurrent();
            
            $table->unique(['user_id', 'competency_code', 'job_profile_id'], 'user_job_competency_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gap_records');
    }
};
