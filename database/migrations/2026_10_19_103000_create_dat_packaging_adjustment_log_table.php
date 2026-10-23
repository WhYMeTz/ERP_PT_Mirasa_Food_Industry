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
        if (!Schema::hasTable('dat_packaging_adjustment_log')) {
            Schema::create('dat_packaging_adjustment_log', function (Blueprint $table) {
                $table->id('adj_id');
                $table->string('adj_no', 100)->unique();
                $table->foreignId('pack_id')->constrained('dat_packaging_hdr', 'pack_id')->onDelete('cascade');
                $table->date('adj_tgl');
                $table->string('kategori_komponen', 50)->default('MULTI')->comment('WIP, KEMASAN, LABOR, OVERHEAD, MULTI');
                $table->decimal('total_biaya_sebelum', 16, 2)->default(0);
                $table->decimal('total_biaya_sesudah', 16, 2)->default(0);
                $table->decimal('selisih_biaya', 16, 2)->default(0);
                $table->decimal('hpp_karton_sebelum', 16, 4)->default(0);
                $table->decimal('hpp_karton_sesudah', 16, 4)->default(0);
                $table->decimal('selisih_hpp_karton', 16, 4)->default(0);
                $table->decimal('hpp_pcs_sebelum', 16, 4)->default(0);
                $table->decimal('hpp_pcs_sesudah', 16, 4)->default(0);
                $table->decimal('selisih_hpp_pcs', 16, 4)->default(0);
                $table->text('alasan_txt')->comment('Berita acara / dasar dokumen penyesuaian (Wajib Diisi)');
                $table->string('no_referensi_txt', 100)->nullable()->comment('Nomor memo atau referensi dokumen fisik');
                $table->json('perubahan_field_json')->nullable()->comment('Snapshot perubahan nilai rincian komponen sebelum vs sesudah');

                // 8 Kolom Audit Trail Standar ERP Mirasa
                $table->timestamp('created_at')->nullable();
                $table->string('created_by', 100)->nullable();
                $table->timestamp('updated_at')->nullable();
                $table->string('updated_by', 100)->nullable();
                $table->timestamp('deleted_at')->nullable();
                $table->string('deleted_by', 100)->nullable();
                $table->boolean('deleted_st')->default(false);
                $table->boolean('active_st')->default(true);
                $table->integer('version_no')->default(1);

                $table->index(['pack_id', 'adj_tgl'], 'idx_pack_adj_tgl');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dat_packaging_adjustment_log');
    }
};
