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
        // 1. Tambah kolom penanda koreksi & catatan verifikasi di dat_pakai_hdr
        Schema::table('dat_pakai_hdr', function (Blueprint $table) {
            if (!Schema::hasColumn('dat_pakai_hdr', 'is_ada_koreksi')) {
                $table->boolean('is_ada_koreksi')->default(false)->after('status_st');
            }
            if (!Schema::hasColumn('dat_pakai_hdr', 'catatan_verifikasi')) {
                $table->text('catatan_verifikasi')->nullable()->after('is_ada_koreksi');
            }
        });

        // 2. Buat tabel audit log riwayat koreksi kesalahan pemakaian
        if (!Schema::hasTable('dat_pakai_revisi_log')) {
            Schema::create('dat_pakai_revisi_log', function (Blueprint $table) {
                $table->id('revisi_id');
                $table->unsignedBigInteger('pakai_id')->index();
                $table->string('revisi_no', 50)->nullable();
                $table->timestamp('revisi_tgl')->useCurrent();
                $table->string('pembuat_nama', 150)->nullable()->comment('Nama staf gudang pembuat input awal');
                $table->string('verifikator_nama', 150)->nullable()->comment('Nama staf keuangan/verifikator pengoreksi');
                $table->string('kategori_kesalahan', 100)->nullable()->comment('SALAH_QTY, SALAH_BATCH, SALAH_BARANG, LAINNYA');
                $table->text('catatan_koreksi')->nullable();
                $table->json('data_sebelum_json')->nullable();
                $table->json('data_sesudah_json')->nullable();
                $table->json('perubahan_json')->nullable();

                // 8 Kolom Standar Audit Trail ERP Mirasa
                $table->string('created_by', 100)->nullable();
                $table->string('updated_by', 100)->nullable();
                $table->string('deleted_by', 100)->nullable();
                $table->boolean('deleted_st')->default(false);
                $table->boolean('active_st')->default(true);
                $table->timestamps();

                $table->foreign('pakai_id')
                    ->references('pakai_id')
                    ->on('dat_pakai_hdr')
                    ->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dat_pakai_revisi_log');

        Schema::table('dat_pakai_hdr', function (Blueprint $table) {
            if (Schema::hasColumn('dat_pakai_hdr', 'is_ada_koreksi')) {
                $table->dropColumn('is_ada_koreksi');
            }
            if (Schema::hasColumn('dat_pakai_hdr', 'catatan_verifikasi')) {
                $table->dropColumn('catatan_verifikasi');
            }
        });
    }
};

