<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('gap_records', function (Blueprint $table) {
            // Menambahkan kolom competency_master_id (nullable dulu biar aman untuk data lama)
            // Letakkan setelah job_profile_id agar rapi
            $table->unsignedBigInteger('competency_master_id')->nullable()->after('job_profile_id');
            
            // Tambahkan Index biar pencarian cepat (Optional tapi sangat disarankan)
            $table->index('competency_master_id');
        });
    }

    public function down()
    {
        Schema::table('gap_records', function (Blueprint $table) {
            $table->dropColumn('competency_master_id');
        });
    }
};
