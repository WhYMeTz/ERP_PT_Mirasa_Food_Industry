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
        Schema::table('dat_laporan_kemas_operator', function (Blueprint $table) {
            $table->unsignedBigInteger('karton_puteran_barang_id')->nullable()->after('karton_barang_id');
            $table->unsignedBigInteger('karton_baru_barang_id')->nullable()->after('karton_puteran_barang_id');
            $table->decimal('karton_puteran_qty', 12, 2)->default(0)->nullable()->after('karton_qty');
            $table->decimal('karton_baru_qty', 12, 2)->default(0)->nullable()->after('karton_puteran_qty');

            $table->foreign('karton_puteran_barang_id')
                ->references('barang_id')
                ->on('mst_barang')
                ->nullOnDelete();

            $table->foreign('karton_baru_barang_id')
                ->references('barang_id')
                ->on('mst_barang')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dat_laporan_kemas_operator', function (Blueprint $table) {
            $table->dropForeign(['karton_puteran_barang_id']);
            $table->dropForeign(['karton_baru_barang_id']);
            $table->dropColumn([
                'karton_puteran_barang_id',
                'karton_baru_barang_id',
                'karton_puteran_qty',
                'karton_baru_qty',
            ]);
        });
    }
};

