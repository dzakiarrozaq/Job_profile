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
        Schema::create('positions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('job_grade_id')->nullable();
            $table->string('title');
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->string('code', 50)->nullable();
            $table->unsignedBigInteger('atasan_id')->nullable();
            $table->timestamps();
            $table->string('tipe', 20)->default('organik')->nullable()->comment('organik / outsourcing');
            $table->unsignedBigInteger('parent_id')->nullable();

            // Foreign Keys
            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('set null');
            $table->foreign('atasan_id')->references('id')->on('positions')->onDelete('set null');
            $table->foreign('parent_id')->references('id')->on('positions')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('positions');
    }
};
