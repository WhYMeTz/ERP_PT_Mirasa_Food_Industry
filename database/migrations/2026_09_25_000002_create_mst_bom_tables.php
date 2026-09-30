<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations (SEMENTARA UNTUK PENGEMBANGAN LOKAL).
     */
    public function up(): void
    {
        if (!Schema::hasTable('mst_bom_hdr')) {
            Schema::create('mst_bom_hdr', function (Blueprint $table) {
                $table->id('bom_id');
                $table->string('bom_no', 50)->unique();
                $table->string('bom_nm', 200);
                $table->foreignId('barang_jadi_id')->constrained('mst_barang', 'barang_id');
                $table->decimal('batch_ukuran_qty', 14, 4)->default(1.0000);
                $table->text('catatan_txt')->nullable();
                $table->string('created_by', 100)->nullable();
                $table->string('updated_by', 100)->nullable();
                $table->timestamp('deleted_at')->nullable();
                $table->string('deleted_by', 100)->nullable();
                $table->boolean('deleted_st')->default(false);
                $table->boolean('active_st')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('mst_bom_dtl')) {
            Schema::create('mst_bom_dtl', function (Blueprint $table) {
                $table->id('bomdtl_id');
                $table->foreignId('bom_id')->constrained('mst_bom_hdr', 'bom_id')->cascadeOnDelete();
                $table->foreignId('barang_mentah_id')->constrained('mst_barang', 'barang_id');
                $table->decimal('kebutuhan_qty', 14, 4);
                $table->text('catatan_txt')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mst_bom_dtl');
        Schema::dropIfExists('mst_bom_hdr');
    }
};
