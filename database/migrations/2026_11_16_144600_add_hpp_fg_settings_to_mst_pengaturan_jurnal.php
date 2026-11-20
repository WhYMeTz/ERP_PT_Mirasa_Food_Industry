<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\Keuangan\MstCoa;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $coas = MstCoa::pluck('coa_id', 'kode_akun')->toArray();

        $fgSettings = [
            [
                'grup'         => 'HPP_FG',
                'kunci'        => 'HPP_FG_AKUN_FG',
                'label'        => 'Akun Persediaan Barang Jadi (FG) (Debet)',
                'deskripsi'    => 'Akun debet kapitalisasi seluruh biaya pengemasan & HPP ke aset persediaan barang jadi (FG)',
                'tipe_nilai'   => 'COA',
                'nilai_coa_id' => $coas['1151'] ?? null, // 1151 Persediaan WIP & FG
                'nilai_text'   => null,
                'urutan'       => 1,
            ],
            [
                'grup'         => 'HPP_FG',
                'kunci'        => 'HPP_FG_AKUN_WIP',
                'label'        => 'Akun Persediaan WIP / Keripik Curah Terpakai (Kredit)',
                'deskripsi'    => 'Akun kredit pengeluaran keripik curah / barang dalam proses (WIP) yang dikemas',
                'tipe_nilai'   => 'COA',
                'nilai_coa_id' => $coas['1151'] ?? null, // 1151 Persediaan WIP & FG
                'nilai_text'   => null,
                'urutan'       => 2,
            ],
            [
                'grup'         => 'HPP_FG',
                'kunci'        => 'HPP_FG_AKUN_KEMASAN',
                'label'        => 'Akun Persediaan Bahan Kemasan & Penolong (Kredit)',
                'deskripsi'    => 'Akun kredit pemakaian plastik, karton box, lakban, dan bumbu sachet kemas',
                'tipe_nilai'   => 'COA',
                'nilai_coa_id' => $coas['1150'] ?? null, // 1150 Persediaan Bahan Baku & Penolong
                'nilai_text'   => null,
                'urutan'       => 3,
            ],
            [
                'grup'         => 'HPP_FG',
                'kunci'        => 'HPP_FG_AKUN_TENAGA_KERJA',
                'label'        => 'Akun Hutang Gaji / BTKL Kemas (Kredit)',
                'deskripsi'    => 'Akun kredit kewajiban upah tenaga kerja langsung / borongan personil pengemasan',
                'tipe_nilai'   => 'COA',
                'nilai_coa_id' => $coas['2102'] ?? null, // 2102 Hutang gaji
                'nilai_text'   => null,
                'urutan'       => 4,
            ],
            [
                'grup'         => 'HPP_FG',
                'kunci'        => 'HPP_FG_AKUN_OVERHEAD',
                'label'        => 'Akun Biaya Lain / Overhead Kemas (Kredit)',
                'deskripsi'    => 'Akun kredit alokasi biaya penunjang / overhead operasional pengemasan',
                'tipe_nilai'   => 'COA',
                'nilai_coa_id' => $coas['2100'] ?? null, // 2100 Hutang usaha
                'nilai_text'   => null,
                'urutan'       => 5,
            ],
            [
                'grup'         => 'HPP_FG',
                'kunci'        => 'HPP_FG_TEMPLATE_KET',
                'label'        => 'Template Keterangan Jurnal HPP FG',
                'deskripsi'    => 'Format keterangan seragam seluruh baris jurnal HPP Pengemasan. Variabel: {tgl}, {barang_jadi}, {hasil_karton}, {hasil_pcs}, {batch_no}',
                'tipe_nilai'   => 'TEXT',
                'nilai_coa_id' => null,
                'nilai_text'   => 'Biaya Pengemasan & HPP FG ({tgl}) - {barang_jadi} ({hasil_karton} karton / {hasil_pcs} pcs)',
                'urutan'       => 6,
            ],
        ];

        foreach ($fgSettings as $item) {
            $existing = DB::table('mst_pengaturan_jurnal')->where('kunci', $item['kunci'])->first();
            if (!$existing) {
                DB::table('mst_pengaturan_jurnal')->insert(array_merge($item, [
                    'created_by' => 'SYSTEM_MIGRATION',
                    'updated_by' => 'SYSTEM_MIGRATION',
                    'deleted_st' => false,
                    'active_st'  => true,
                    'version_no' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('mst_pengaturan_jurnal')->where('grup', 'HPP_FG')->delete();
    }
};
