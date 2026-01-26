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
        Schema::create('trainings', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('duration', 100)->nullable();
            $table->string('provider')->nullable();
            $table->string('status', 50)->default('draft')->nullable();
            $table->timestamps();
            $table->string('competency_name')->nullable()->comment('Nama Kompetensi');
            $table->text('objective')->nullable()->comment('Course Objective');
            $table->text('content')->nullable()->comment('Course Content');
            $table->string('level', 50)->nullable()->comment('Course Level (Basic/Intermediate/Advanced)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trainings');
    }
};
