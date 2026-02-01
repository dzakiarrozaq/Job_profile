<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('competency_key_behaviors', function (Blueprint $table) {
            $table->id();
            
            // Relasi ke tabel competencies_master
            // Pastikan nama tabel di constrained() sesuai dengan nama tabel migration pertama Anda
            $table->foreignId('competency_master_id')
                  ->constrained('competencies_master')
                  ->onDelete('cascade'); 

            // Menyimpan Level (1, 2, 3, 4, 5)
            $table->integer('level')->comment('Level Kompetensi 1-5');

            // Menyimpan Teks Perilaku (Isi butir-butir dari Excel)
            $table->text('behavior')->comment('Perilaku Kunci / Key Behavior');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('competency_key_behaviors');
    }
};