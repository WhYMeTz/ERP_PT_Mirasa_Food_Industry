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
        // 1. Tambahkan kolom status & statistik revisi di dat_po_hdr
        Schema::table('dat_po_hdr', function (Blueprint $table) {
            if (!Schema::hasColumn('dat_po_hdr', 'is_ada_revisi')) {
                $table->boolean('is_ada_revisi')->default(false)->after('status_cd');
            }
            if (!Schema::hasColumn('dat_po_hdr', 'total_revisi_count')) {
                $table->integer('total_revisi_count')->default(0)->after('is_ada_revisi');
            }
            if (!Schema::hasColumn('dat_po_hdr', 'terakhir_direvisi_at')) {
                $table->timestamp('terakhir_direvisi_at')->nullable()->after('total_revisi_count');
            }
            if (!Schema::hasColumn('dat_po_hdr', 'terakhir_direvisi_by')) {
                $table->string('terakhir_direvisi_by', 100)->nullable()->after('terakhir_direvisi_at');
            }
        });

        // 2. Buat tabel riwayat audit trail edit dokumen PO
        if (!Schema::hasTable('dat_po_revisi_log')) {
            Schema::create('dat_po_revisi_log', function (Blueprint $table) {
                $table->id('revisi_id');
                $table->unsignedBigInteger('po_id')->index();
                $table->string('revisi_no', 50)->nullable()->comment('REV-1, REV-2, dst');
                $table->timestamp('revisi_tgl')->useCurrent();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('user_nama', 150)->nullable()->comment('Nama operator/user yang melakukan edit');
                $table->string('user_role', 100)->nullable()->comment('Role/Jabatan user saat melakukan edit');
                $table->string('kategori_perubahan', 100)->nullable()->comment('PERUBAHAN_ITEM, PERUBAHAN_HARGA, PERUBAHAN_QTY, PERUBAHAN_HEADER, KOMPREHENSIF');
                $table->text('alasan_edit')->nullable()->comment('Alasan revisi yang diinput oleh user');
                $table->text('ringkasan_perubahan')->nullable()->comment('Ringkasan poin-poin yang diubah');
                $table->json('data_sebelum_json')->nullable()->comment('Snapshot lengkap PO sebelum diedit');
                $table->json('data_sesudah_json')->nullable()->comment('Snapshot lengkap PO sesudah diedit');
                $table->json('perubahan_json')->nullable()->comment('Array detail field sebelum vs sesudah');

                // 8 Kolom Standar Audit Trail ERP Mirasa
                $table->string('created_by', 100)->nullable();
                $table->string('updated_by', 100)->nullable();
                $table->string('deleted_by', 100)->nullable();
                $table->boolean('deleted_st')->default(false);
                $table->boolean('active_st')->default(true);
                $table->timestamps();

                $table->foreign('po_id')
                    ->references('po_id')
                    ->on('dat_po_hdr')
                    ->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dat_po_revisi_log');

        Schema::table('dat_po_hdr', function (Blueprint $table) {
            if (Schema::hasColumn('dat_po_hdr', 'is_ada_revisi')) {
                $table->dropColumn('is_ada_revisi');
            }
            if (Schema::hasColumn('dat_po_hdr', 'total_revisi_count')) {
                $table->dropColumn('total_revisi_count');
            }
            if (Schema::hasColumn('dat_po_hdr', 'terakhir_direvisi_at')) {
                $table->dropColumn('terakhir_direvisi_at');
            }
            if (Schema::hasColumn('dat_po_hdr', 'terakhir_direvisi_by')) {
                $table->dropColumn('terakhir_direvisi_by');
            }
        });
    }
};

