<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Keuangan\MstCoa;
use App\Models\MasterData\MstPajak;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Pastikan Akun 2106 - PPH 22 terdaftar di Bagan Akun (COA)
        $existing = MstCoa::where('kode_akun', '2106')->first();
        if (!$existing) {
            $coa = MstCoa::create([
                'kode_akun'        => '2106',
                'nama_akun'        => 'PPH 22',
                'kategori_akun'    => 'PASIVA',
                'sub_kategori'     => 'Hutang Pajak',
                'posisi_keuangan'  => 'NERACA',
                'saldo_normal'     => 'KREDIT',
                'saldo_awal'       => 0,
                'saldo_berjalan'   => 0,
                'is_kas_bank'      => false,
                'keterangan'       => 'Hutang pemotongan PPh pasal 22 atas komoditas/pedagang pengumpul',
                'active_st'        => true,
                'deleted_st'       => false,
                'created_by'       => 'SYSTEM',
                'version_no'       => 1,
            ]);
            $pph22Id = $coa->coa_id;
        } else {
            $pph22Id = $existing->coa_id;
        }

        // 2. Hubungkan Master Data Pajak PPH-22-KOMODITAS ke Akun COA 2106
        $pajak22 = MstPajak::where('pajak_cd', 'PPH-22-KOMODITAS')->first();
        if ($pajak22) {
            $pajak22->coa_id = $pph22Id;
            $pajak22->coa_pembelian_id = $pph22Id;
            $pajak22->save();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $coa = MstCoa::where('kode_akun', '2106')->first();
        if ($coa) {
            MstPajak::where('coa_pembelian_id', $coa->coa_id)->update([
                'coa_pembelian_id' => null,
                'coa_id' => null,
            ]);
            $coa->delete();
        }
    }
};

