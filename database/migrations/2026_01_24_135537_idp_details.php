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
            
            // Sasaran & Kategori (Satu per baris)
            $table->text('development_goal');
            $table->string('dev_category')->nullable();
            
            // --- PERUBAHAN UTAMA ---
            // Karena 1 Goal bisa punya BANYAK Activities & Dates, kita pakai JSON.
            // Struktur JSON nanti: [ {"desc": "Training A", "date": "Jan 2025"}, {"desc": "Mentoring", "date": "Feb 2025"} ]
            $table->json('activities')->nullable(); 
            
            // Kolom lama ini DIHAPUS karena sudah masuk ke dalam JSON activities
            // $table->text('activity'); // HAPUS
            // $table->string('expected_date', 100); // HAPUS

            // Progress bisa tetap text atau JSON tergantung kebutuhan tracking
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
