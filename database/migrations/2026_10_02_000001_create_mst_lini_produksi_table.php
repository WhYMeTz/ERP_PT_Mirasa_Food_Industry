<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mst_lini_produksi', function (Blueprint $table) {
            $table->id('lini_id');
            $table->string('lini_cd', 50)->unique()->comment('Kode Singkat Lini Produksi');
            $table->string('lini_nm', 100)->comment('Nama Lini Produksi / Tujuan');
            $table->string('tipe_batch', 30)->default('REGULER')->comment('Format Batch: IFM (Shift & Karton Awal-Akhir) atau REGULER (Standar Persediaan DD MM YYYY)');
            $table->string('keterangan', 255)->nullable()->comment('Keterangan / Deskripsi Operasional Lini');

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

        // Seed data awal sesuai dropdown default operasional
        $now = now();
        DB::table('mst_lini_produksi')->insert([
            [
                'lini_cd'    => 'IFM',
                'lini_nm'    => 'PRODUKSI IFM',
                'tipe_batch' => 'IFM',
                'keterangan' => 'Indofood IFM (WIP-FCC Keripik Singkong)',
                'created_at' => $now,
                'created_by' => 'SYSTEM',
                'active_st'  => true,
                'deleted_st' => false,
            ],
            [
                'lini_cd'    => 'PP2000',
                'lini_nm'    => 'PRODUKSI PING-PING 2000',
                'tipe_batch' => 'REGULER',
                'keterangan' => 'Produksi Keripik Ping-Ping 2000',
                'created_at' => $now,
                'created_by' => 'SYSTEM',
                'active_st'  => true,
                'deleted_st' => false,
            ],
            [
                'lini_cd'    => 'PP-UMUM',
                'lini_nm'    => 'PRODUKSI PING-PING',
                'tipe_batch' => 'REGULER',
                'keterangan' => 'Produksi Keripik Ping-Ping (Umum / Retail)',
                'created_at' => $now,
                'created_by' => 'SYSTEM',
                'active_st'  => true,
                'deleted_st' => false,
            ],
            [
                'lini_cd'    => 'LAINNYA',
                'lini_nm'    => 'PRODUKSI LAINNYA',
                'tipe_batch' => 'REGULER',
                'keterangan' => 'Lini Produksi / Tujuan Lainnya',
                'created_at' => $now,
                'created_by' => 'SYSTEM',
                'active_st'  => true,
                'deleted_st' => false,
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mst_lini_produksi');
    }
};
