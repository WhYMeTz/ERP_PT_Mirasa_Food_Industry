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
        // 1. MASTER AKUN KAS & REKENING BANK PABRIK
        Schema::create('mst_kas_bank', function (Blueprint $table) {
            $table->id('kas_bank_id');
            $table->string('kode_akun', 50)->unique();
            $table->string('nama_akun', 150);
            $table->string('tipe_akun', 20)->default('BANK')->comment('KAS, BANK');
            $table->string('nomor_rekening', 100)->nullable();
            $table->string('atas_nama', 150)->nullable();
            $table->string('bank_nama', 100)->nullable()->comment('BCA, MANDIRI, BRI, BNI, TUNAI');
            $table->string('cabang', 100)->nullable();
            $table->decimal('saldo_awal', 16, 4)->default(0);
            $table->decimal('saldo_berjalan', 16, 4)->default(0);
            $table->text('keterangan')->nullable();

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

            $table->index(['tipe_akun', 'active_st'], 'idx_kas_bank_tipe_active');
        });

        // 2. HEADER TAGIHAN PEMBELIAN & HUTANG USAHA (INVOICE AP)
        Schema::create('dat_hutang_hdr', function (Blueprint $table) {
            $table->id('hutang_id');
            $table->string('hutang_no', 100)->unique();
            $table->string('no_faktur_supplier', 100)->nullable()->comment('No invoice/nota fisik dari rekanan');
            $table->foreignId('supplier_id')->nullable()->constrained('mst_supplier', 'supplier_id')->onDelete('restrict');
            $table->foreignId('gudang_id')->nullable()->constrained('mst_gudang', 'gudang_id')->onDelete('restrict');
            $table->date('tgl_tagihan');
            $table->date('tgl_jatuh_tempo');
            $table->string('tipe_hutang', 50)->default('PEMBELIAN_GRN')->comment('PEMBELIAN_GRN, GAS_UTILITAS, OPERASIONAL_UMUM');
            $table->string('periode_bulan', 20)->nullable()->comment('Contoh: 2026-10 untuk rekap tagihan gas bulanan');

            // Nilai Moneter
            $table->decimal('total_bruto', 16, 4)->default(0);
            $table->decimal('total_diskon', 16, 4)->default(0);
            $table->decimal('total_potongan_retur', 16, 4)->default(0)->comment('Potongan jika ada retur barang rusak');
            $table->decimal('total_ppn', 16, 4)->default(0);
            $table->decimal('total_tagihan', 16, 4)->default(0)->comment('Total netto yang wajib dibayar');
            $table->decimal('total_dibayar', 16, 4)->default(0)->comment('Akumulasi pembayaran yang sudah dilakukan');
            $table->decimal('sisa_hutang', 16, 4)->default(0)->comment('Sisa tagihan yang belum lunas');

            $table->string('status_pembayaran', 30)->default('BELUM_LUNAS')->comment('BELUM_LUNAS, SEBAGIAN, LUNAS, DIBATALKAN');
            $table->text('catatan')->nullable();

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

            $table->index(['supplier_id', 'status_pembayaran'], 'idx_hutang_supp_status');
            $table->index(['tgl_jatuh_tempo', 'status_pembayaran'], 'idx_hutang_tempo_status');
            $table->index(['tipe_hutang', 'periode_bulan'], 'idx_hutang_tipe_periode');
        });

        // 3. DETAIL ITEM RINCIAN TAGIHAN HUTANG (DARI GRN / PEMAKAIAN GAS / JASA)
        Schema::create('dat_hutang_dtl', function (Blueprint $table) {
            $table->id('hutangdtl_id');
            $table->foreignId('hutang_id')->constrained('dat_hutang_hdr', 'hutang_id')->onDelete('cascade');
            $table->foreignId('terima_id')->nullable()->constrained('dat_terima_hdr', 'terima_id')->onDelete('set null');
            $table->unsignedBigInteger('terima_dtl_id')->nullable();
            $table->foreignId('barang_id')->nullable()->constrained('mst_barang', 'barang_id')->onDelete('restrict');
            $table->string('deskripsi', 255);
            $table->decimal('qty', 16, 4)->default(0);
            $table->string('satuan', 50)->nullable();
            $table->decimal('harga_satuan', 16, 4)->default(0);
            $table->decimal('subtotal', 16, 4)->default(0);
            $table->decimal('diskon_nominal', 16, 4)->default(0);
            $table->decimal('ppn_nominal', 16, 4)->default(0);
            $table->decimal('total_netto', 16, 4)->default(0);
            $table->string('catatan', 255)->nullable();

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

            $table->index(['hutang_id', 'terima_id'], 'idx_hutang_dtl_terima');
        });

        // 4. HEADER PEMBAYARAN HUTANG (BUKTI KAS/BANK KELUAR - BKK)
        Schema::create('dat_bayar_hutang_hdr', function (Blueprint $table) {
            $table->id('bayar_id');
            $table->string('bayar_no', 100)->unique();
            $table->date('tgl_bayar');
            $table->foreignId('supplier_id')->nullable()->constrained('mst_supplier', 'supplier_id')->onDelete('restrict');
            $table->foreignId('kas_bank_id')->constrained('mst_kas_bank', 'kas_bank_id')->onDelete('restrict');
            $table->string('metode_bayar', 30)->default('TRANSFER')->comment('TRANSFER, CASH, GIRO, LAINNYA');
            $table->string('no_referensi_bank', 100)->nullable()->comment('No ref transfer bank / nomor cek');
            $table->decimal('total_bayar', 16, 4)->default(0);
            $table->string('penerima_nama', 150)->nullable()->comment('Pihak/orang yang menerima dana');
            $table->text('catatan')->nullable();
            $table->string('status_cd', 20)->default('POSTED')->comment('POSTED, VOID');

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

            $table->index(['tgl_bayar', 'kas_bank_id'], 'idx_bayar_tgl_kas');
        });

        // 5. DETAIL ALOKASI PEMBAYARAN KE INVOICE TAGIHAN
        Schema::create('dat_bayar_hutang_dtl', function (Blueprint $table) {
            $table->id('bayardt_id');
            $table->foreignId('bayar_id')->constrained('dat_bayar_hutang_hdr', 'bayar_id')->onDelete('cascade');
            $table->foreignId('hutang_id')->constrained('dat_hutang_hdr', 'hutang_id')->onDelete('restrict');
            $table->decimal('nominal_bayar', 16, 4)->default(0);
            $table->decimal('sisa_hutang_sebelum', 16, 4)->default(0);
            $table->decimal('sisa_hutang_sesudah', 16, 4)->default(0);
            $table->string('catatan', 255)->nullable();

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

            $table->index(['bayar_id', 'hutang_id'], 'idx_bayardt_hutang');
        });

        // 6. BUKU MUTASI KAS & BANK (CASH & BANK LEDGER)
        Schema::create('dat_kas_mutasi', function (Blueprint $table) {
            $table->id('mutasi_id');
            $table->foreignId('kas_bank_id')->constrained('mst_kas_bank', 'kas_bank_id')->onDelete('restrict');
            $table->date('tgl_mutasi');
            $table->string('tipe_mutasi', 20)->comment('KELUAR, MASUK');
            $table->decimal('nominal', 16, 4)->default(0);
            $table->decimal('saldo_sebelum', 16, 4)->default(0);
            $table->decimal('saldo_sesudah', 16, 4)->default(0);
            $table->string('jenis_transaksi', 50)->comment('BAYAR_HUTANG, BIAYA_OPERASIONAL, MUTASI_KAS, TERIMA_PIUTANG, SALDO_AWAL');
            $table->string('ref_no', 100)->nullable()->comment('No referensi dokumen, misal BKK-xxxx');
            $table->unsignedBigInteger('ref_id')->nullable();
            $table->text('keterangan')->nullable();

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

            $table->index(['kas_bank_id', 'tgl_mutasi'], 'idx_mutasi_kas_tgl');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dat_kas_mutasi');
        Schema::dropIfExists('dat_bayar_hutang_dtl');
        Schema::dropIfExists('dat_bayar_hutang_hdr');
        Schema::dropIfExists('dat_hutang_dtl');
        Schema::dropIfExists('dat_hutang_hdr');
        Schema::dropIfExists('mst_kas_bank');
    }
};
