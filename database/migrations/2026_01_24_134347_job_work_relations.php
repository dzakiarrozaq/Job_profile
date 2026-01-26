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
        Schema::create('job_work_relations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_profile_id')->constrained('job_profiles')->onDelete('cascade');
            $table->string('type', 50);
            $table->string('unit_instansi')->nullable();
            $table->text('purpose')->nullable();
            $table->timestamps();
            $table->string('relation_type', 100)->nullable();
            $table->text('description')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_work_relations');
    }
};
