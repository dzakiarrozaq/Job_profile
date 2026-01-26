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
        Schema::create('idp_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('idp_id')->constrained('idps')->onDelete('cascade');
            $table->text('development_goal');
            $table->string('dev_category');
            $table->text('activity');
            $table->string('expected_date', 100);
            $table->text('progress')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('idp_details');
    }
};
