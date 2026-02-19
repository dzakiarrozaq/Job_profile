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
        Schema::table('users', function (Blueprint $table) {
            // TAMBAHKAN ->nullable()
            // Ini memberitahu MySQL: "Ubah jadi TEXT, dan biarkan tetap boleh kosong"
            $table->text('nik')->nullable()->change();
            
            // Jika Anda juga menambahkan kolom hash
            $table->string('nik_hash')->nullable()->index()->after('nik');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            // Kembalikan ke string dan nullable juga saat rollback
            $table->string('nik')->nullable()->change();
        });
    }
};
