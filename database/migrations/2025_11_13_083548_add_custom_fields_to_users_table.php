<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('users', function (Blueprint $table) {
            // Tambahkan kolom-kolom baru setelah kolom 'password'
            $table->string('nik')->nullable()->after('password');
            $table->string('status', 50)->default('active')->after('nik');

            // Tambahkan foreign keys
            $table->unsignedBigInteger('role_id')->nullable()->after('status');
            $table->unsignedBigInteger('department_id')->nullable()->after('role_id');
            $table->unsignedBigInteger('position_id')->nullable()->after('department_id');
            $table->unsignedBigInteger('manager_id')->nullable()->after('position_id');
        });
    }

    public function down(): void {
        Schema::table('users', function (Blueprint $table) {
            // Hapus foreign keys terlebih dahulu
            $table->dropForeign(['role_id']);
            $table->dropForeign(['department_id']);
            $table->dropForeign(['position_id']);
            $table->dropForeign(['manager_id']);

            // Hapus kolom-kolom
            $table->dropColumn(['nik', 'status', 'role_id', 'department_id', 'position_id', 'manager_id']);
        });
    }
};