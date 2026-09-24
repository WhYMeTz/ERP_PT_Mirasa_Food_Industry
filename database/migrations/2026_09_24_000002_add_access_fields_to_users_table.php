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
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('karyawan_id')->nullable()->after('id');
            $table->string('role_cd', 50)->default('STAFF_PRODUKSI')->after('password')->comment('SUPERADMIN, ADMIN_GUDANG, STAFF_PRODUKSI, PURCHASING, FINANCE, QC');
            $table->unsignedBigInteger('gudang_id')->nullable()->after('role_cd')->comment('Gudang tugas default (misal Magelang)');
            $table->boolean('active_st')->default(true)->after('remember_token');
            $table->boolean('deleted_st')->default(false)->after('active_st');

            $table->foreign('karyawan_id')->references('karyawan_id')->on('mst_karyawan')->nullOnDelete();
            $table->foreign('gudang_id')->references('gudang_id')->on('mst_gudang')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['karyawan_id']);
            $table->dropForeign(['gudang_id']);
            $table->dropColumn(['karyawan_id', 'role_cd', 'gudang_id', 'active_st', 'deleted_st']);
        });
    }
};
