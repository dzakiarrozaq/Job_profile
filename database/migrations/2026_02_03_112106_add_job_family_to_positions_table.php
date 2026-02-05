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
        Schema::table('positions', function (Blueprint $table) {
            // Kolom untuk menyimpan 'Struktural' atau 'Fungsional'
            $table->string('job_family')->nullable()->after('title'); 
        });
    }

    public function down()
    {
        Schema::table('positions', function (Blueprint $table) {
            $table->dropColumn('job_family');
        });
    }
};
