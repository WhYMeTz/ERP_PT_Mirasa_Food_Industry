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
        Schema::create('sys_role_permissions', function (Blueprint $table) {
            $table->id('permission_id');
            $table->string('role_cd', 50)->index()->comment('SUPERADMIN, ADMIN_GUDANG, STAFF_PRODUKSI, PURCHASING, FINANCE, QC');
            $table->string('permission_cd', 100)->index()->comment('Kode hak akses / izin menu');
            $table->boolean('allowed_st')->default(true)->comment('Status diizinkan');
            
            // Audit columns
            $table->string('created_by', 100)->nullable();
            $table->string('updated_by', 100)->nullable();
            $table->timestamps();

            $table->unique(['role_cd', 'permission_cd']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sys_role_permissions');
    }
};
