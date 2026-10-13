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
        Schema::create('dat_produksi_adjustment_log', function (Blueprint $table) {
            $table->id('adj_id');
            $table->string('adj_no', 100)->unique();
            $table->foreignId('produksi_id')->constrained('dat_produksi_hdr', 'produksi_id')->onDelete('cascade');
            $table->date('adj_tgl');
            $table->string('kategori_komponen', 50)->comment('MINYAK, BAHAN, BUMBU, UPAH_TK, OVERHEAD, LAINNYA, MULTI');
            $table->decimal('total_biaya_sebelum', 16, 2)->default(0);
            $table->decimal('total_biaya_sesudah', 16, 2)->default(0);
            $table->decimal('selisih_biaya', 16, 2)->default(0);
            $table->decimal('hpp_sebelum', 16, 2)->default(0);
            $table->decimal('hpp_sesudah', 16, 2)->default(0);
            $table->decimal('selisih_hpp', 16, 2)->default(0);
            $table->text('alasan_txt')->comment('Berita acara / dasar dokumen penyesuaian (Wajib Diisi)');
            $table->json('perubahan_field_json')->nullable()->comment('Snapshot perubahan nilai field sebelum vs sesudah');

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

            $table->index(['produksi_id', 'adj_tgl'], 'idx_prod_adj_tgl');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dat_produksi_adjustment_log');
    }
};
