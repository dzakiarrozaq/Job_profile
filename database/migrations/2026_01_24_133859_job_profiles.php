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
        Schema::create('job_profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('position_id');
            $table->string('level_required', 50)->nullable();
            $table->text('description')->nullable();
            $table->text('tujuan_jabatan')->nullable();
            $table->text('wewenang')->nullable();
            $table->text('dimensi_keuangan')->nullable();
            $table->text('dimensi_non_keuangan')->nullable();
            $table->unsignedBigInteger('created_by')->nullable(); // User ID pembuat
            $table->integer('version')->default(0)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->string('status', 20)->default('draft')->nullable();
            $table->foreign('position_id')->references('id')->on('positions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_profiles');
    }
};
