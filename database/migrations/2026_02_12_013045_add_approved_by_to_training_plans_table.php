<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('training_plans', function (Blueprint $table) {
            // Menambah kolom approved_by setelah kolom status
            $table->unsignedBigInteger('approved_by')->nullable()->after('status');
            
            // Relasi ke tabel users (opsional tapi bagus untuk integritas data)
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('training_plans', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn('approved_by');
        });
    }
};
