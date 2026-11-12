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
        Schema::table('mst_barang', function (Blueprint $table) {
            $table->string('kategori_output_cd', 30)->nullable()->after('grade_cd')
                ->comment('Kategori grup output produksi: FCC_NON_BUMBU, EKSPOR, WIP_BERBUMBU, WIP_TAWAR, WIP_HOLD, WIP_AFKIR');
        });

        // Auto-seed data lama agar seluruh master barang langsung terisi kategorinya
        $barangs = DB::table('mst_barang')->get();
        $seasoningCodes = ['WIP-ASB', 'WIP-ASW', 'WIP-BLQ', 'WIP-UCM', 'WIP-KCL', 'WIP-BBB', 'WIP-AJB', 'WIP-PJB', 'WIP-OPA', 'WIP-OPB', 'WIP-PYA', 'WIP-PYB', 'WIP-UBI', 'WIP-UBU'];

        foreach ($barangs as $b) {
            $cd = strtoupper($b->barang_cd ?? '');
            $nm = strtoupper($b->barang_nm ?? '');
            $grade = strtoupper($b->grade_cd ?? '');
            $kategori = null;

            if ($cd === 'WIP-FCC' || str_starts_with($cd, 'WIP-FCC') || str_contains($nm, 'IFL') || str_contains($nm, 'INDOFOOD') || str_contains($nm, 'TANPA BUMBU')) {
                $kategori = 'FCC_NON_BUMBU';
            } elseif (str_contains($cd, 'HLD') || str_contains($nm, 'HOLD') || $grade === 'HOLD') {
                $kategori = 'WIP_HOLD';
            } elseif (str_contains($nm, 'BERKO') || str_contains($nm, 'LENGKET') || str_contains($nm, 'REMAHAN') || str_contains($nm, 'AFKIR') || str_contains($cd, 'BRK') || str_contains($cd, 'LGT') || str_contains($cd, 'RMH') || str_contains($cd, 'AFK')) {
                $kategori = 'WIP_AFKIR';
            } elseif (str_contains($nm, 'EKSPOR') || str_starts_with($cd, 'FG-EA') || str_starts_with($cd, 'FG-EC')) {
                $kategori = 'EKSPOR';
            } elseif (in_array($cd, $seasoningCodes) || str_contains($nm, 'ASIN') || str_contains($nm, 'BUMBU') || str_contains($nm, 'BALADO') || str_contains($nm, 'LEMON') || str_contains($nm, 'PEDAS') || str_contains($nm, 'BARCO') || str_contains($nm, 'SAWIT') || str_contains($nm, 'BALQI') || str_contains($nm, 'UCAMP') || str_contains($nm, 'OPAK') || str_contains($nm, 'PUYUR')) {
                $kategori = 'WIP_BERBUMBU';
            } elseif (str_starts_with($cd, 'WIP-') || str_contains($nm, 'KERIPIK')) {
                $kategori = 'WIP_TAWAR';
            }

            if ($kategori) {
                DB::table('mst_barang')->where('barang_id', $b->barang_id)->update(['kategori_output_cd' => $kategori]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mst_barang', function (Blueprint $table) {
            $table->dropColumn('kategori_output_cd');
        });
    }
};

