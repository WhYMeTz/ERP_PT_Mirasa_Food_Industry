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
        Schema::create('mst_pajak', function (Blueprint $table) {
            $table->id('pajak_id');
            $table->string('pajak_cd', 30)->unique()->comment('Kode unik pajak, misal: PPN-11, PPH-23, NON-PPN');
            $table->string('pajak_nm', 100)->comment('Nama resmi pajak, misal: PPN 11%, PPh 23 Jasa 2%');
            $table->string('kategori_pajak', 30)->default('PPN')->comment('PPN, PPH_23, PPH_4_2, PPH_21, PPH_22, LAINNYA');
            $table->string('sifat_pajak', 15)->default('TAMBAH')->comment('TAMBAH (menaikkan total tagihan/PPN) atau POTONG (withholding/mengurangi pembayaran/PPh)');
            $table->decimal('tarif_persen', 5, 2)->default(0)->comment('Tarif pajak dalam persen (misal: 11.00, 2.00, 10.00)');
            $table->foreignId('coa_id')->nullable()->constrained('mst_coa', 'coa_id')->nullOnDelete()->comment('Akun COA pemetaan jurnal otomatis');
            $table->text('keterangan')->nullable()->comment('Penjelasan ketentuan / objek pajak');
            $table->boolean('is_default')->default(false)->comment('Apakah tarif ini menjadi pilihan bawaan transaksi');

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

            $table->index(['kategori_pajak', 'active_st'], 'idx_pajak_kategori_active');
            $table->index('active_st', 'idx_pajak_active');
            $table->index('is_default', 'idx_pajak_default');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mst_pajak');
    }
};
