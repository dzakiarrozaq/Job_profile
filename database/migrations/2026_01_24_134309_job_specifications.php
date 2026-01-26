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
        Schema::create('job_specifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_profile_id')->constrained('job_profiles')->onDelete('cascade');
            $table->string('type', 50)->default('General')->nullable();
            $table->text('requirement')->nullable();
            $table->text('level_or_notes')->nullable();
            $table->timestamps();
            $table->text('description')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_specifications');
    }
};
