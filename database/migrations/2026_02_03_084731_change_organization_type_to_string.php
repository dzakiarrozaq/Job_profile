<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // <--- Jangan lupa import DB

return new class extends Migration
{
    public function up(): void
    {
        // Kita ubah kolom 'type' menjadi VARCHAR agar bisa menampung 'directorate'
        // Menggunakan Raw Statement agar tidak perlu install doctrine/dbal
        DB::statement("ALTER TABLE organizations MODIFY COLUMN type VARCHAR(50) NULL");
    }

    public function down(): void
    {
        // Kembalikan ke enum jika rollback (sesuaikan dengan enum lama Anda)
        DB::statement("ALTER TABLE organizations MODIFY COLUMN type ENUM('department', 'unit', 'section')");
    }
};