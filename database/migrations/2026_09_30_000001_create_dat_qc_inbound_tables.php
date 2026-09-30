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
        // 1. Header Inspeksi QC Bahan Masuk (Incoming QC)
        Schema::create('dat_qc_inbound_hdr', function (Blueprint $table) {
            $table->id('qc_id');
            $table->string('qc_no', 50)->unique();
            $table->foreignId('po_id')->nullable()->constrained('dat_po_hdr', 'po_id')->nullOnDelete();
            $table->foreignId('supplier_id')->constrained('mst_supplier', 'supplier_id')->onDelete('restrict');
            $table->foreignId('gudang_id')->constrained('mst_gudang', 'gudang_id')->onDelete('restrict');
            $table->string('surat_jalan_supplier', 100)->nullable();
            $table->string('plat_nomor_truk', 30)->nullable();
            $table->string('sopir_nama', 100)->nullable();
            $table->dateTime('tgl_periksa');
            $table->string('petugas_qc_nama', 100)->nullable();
            $table->string('status_qc', 30)->default('SIAP_GUDANG'); // DRAFT, SIAP_GUDANG, DITERIMA_GUDANG, DITOLAK_TOTAL
            $table->text('catatan_umum')->nullable();

            // 8 Kolom Audit Trail Standar ERP Mirasa
            $table->timestamp('created_at')->nullable();
            $table->string('created_by', 100)->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('updated_by', 100)->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->string('deleted_by', 100)->nullable();
            $table->boolean('deleted_st')->default(false);
            $table->boolean('active_st')->default(true);
        });

        // 2. Detail Item Hasil Uji QC (Kadar Air, Refraksi, Gross, Reject, Netto)
        Schema::create('dat_qc_inbound_dtl', function (Blueprint $table) {
            $table->id('qcdtl_id');
            $table->foreignId('qc_id')->constrained('dat_qc_inbound_hdr', 'qc_id')->onDelete('cascade');
            $table->foreignId('podtl_id')->nullable()->constrained('dat_po_dtl', 'podtl_id')->nullOnDelete();
            $table->foreignId('barang_id')->constrained('mst_barang', 'barang_id')->onDelete('restrict');
            $table->decimal('qty_timbang_gross', 16, 4)->default(0);
            $table->decimal('kadar_air_persen', 5, 2)->default(0);
            $table->decimal('refraksi_persen', 5, 2)->default(0);
            $table->decimal('qty_refraksi', 16, 4)->default(0);
            $table->decimal('qty_reject', 16, 4)->default(0);
            $table->decimal('qty_netto_lolos', 16, 4)->default(0);
            $table->string('grade_cd', 20)->default('A'); // A, B, REJECT
            $table->string('kondisi_fisik', 50)->nullable(); // NORMAL, SERAT_BIRU_RINGAN, BERLUMPUR, AFKIR
            $table->string('keputusan_qc', 30)->default('PASSED'); // PASSED, PASSED_REFRAKSI, REJECT_PARTIAL, REJECT_TOTAL
            $table->text('catatan_dtl')->nullable();

            // 8 Kolom Audit Trail Standar ERP Mirasa
            $table->timestamp('created_at')->nullable();
            $table->string('created_by', 100)->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('updated_by', 100)->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->string('deleted_by', 100)->nullable();
            $table->boolean('deleted_st')->default(false);
            $table->boolean('active_st')->default(true);
        });

        // 3. Tambahkan relasi qc_id di dat_terima_hdr dan dat_terima_dtl
        if (Schema::hasTable('dat_terima_hdr') && !Schema::hasColumn('dat_terima_hdr', 'qc_id')) {
            Schema::table('dat_terima_hdr', function (Blueprint $table) {
                $table->foreignId('qc_id')->nullable()->constrained('dat_qc_inbound_hdr', 'qc_id')->nullOnDelete();
            });
        }

        if (Schema::hasTable('dat_terima_dtl')) {
            Schema::table('dat_terima_dtl', function (Blueprint $table) {
                if (!Schema::hasColumn('dat_terima_dtl', 'qcdtl_id')) {
                    $table->foreignId('qcdtl_id')->nullable()->constrained('dat_qc_inbound_dtl', 'qcdtl_id')->nullOnDelete();
                }
                if (!Schema::hasColumn('dat_terima_dtl', 'kadar_air_persen')) {
                    $table->decimal('kadar_air_persen', 5, 2)->default(0);
                }
                if (!Schema::hasColumn('dat_terima_dtl', 'refraksi_persen')) {
                    $table->decimal('refraksi_persen', 5, 2)->default(0);
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('dat_terima_dtl')) {
            Schema::table('dat_terima_dtl', function (Blueprint $table) {
                if (Schema::hasColumn('dat_terima_dtl', 'qcdtl_id')) {
                    $table->dropForeign(['qcdtl_id']);
                    $table->dropColumn(['qcdtl_id']);
                }
                if (Schema::hasColumn('dat_terima_dtl', 'kadar_air_persen')) {
                    $table->dropColumn('kadar_air_persen');
                }
                if (Schema::hasColumn('dat_terima_dtl', 'refraksi_persen')) {
                    $table->dropColumn('refraksi_persen');
                }
            });
        }

        if (Schema::hasTable('dat_terima_hdr') && Schema::hasColumn('dat_terima_hdr', 'qc_id')) {
            Schema::table('dat_terima_hdr', function (Blueprint $table) {
                $table->dropForeign(['qc_id']);
                $table->dropColumn('qc_id');
            });
        }

        Schema::dropIfExists('dat_qc_inbound_dtl');
        Schema::dropIfExists('dat_qc_inbound_hdr');
    }
};
