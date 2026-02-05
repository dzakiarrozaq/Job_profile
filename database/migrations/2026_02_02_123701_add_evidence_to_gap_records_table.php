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
        Schema::table('gap_records', function (Blueprint $table) {
            // Menambahkan kolom evidence setelah kolom gap_value
            $table->text('evidence')->nullable()->after('gap_value');
        });
    }

    public function down(): void
    {
        Schema::table('gap_records', function (Blueprint $table) {
            $table->dropColumn('evidence');
        });
    }
};
