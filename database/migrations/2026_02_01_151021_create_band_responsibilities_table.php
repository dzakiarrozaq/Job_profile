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
        Schema::create('band_responsibilities', function (Blueprint $table) {
            $table->id();
            $table->string('band'); // Misal: 'Band I', 'Band II', 'Manager', 'Staff'
            $table->longText('responsibility'); // Isi tanggung jawabnya
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('band_responsibilities');
    }
};
