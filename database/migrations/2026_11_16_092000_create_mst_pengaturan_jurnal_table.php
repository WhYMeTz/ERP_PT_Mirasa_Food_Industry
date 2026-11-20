<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mst_pengaturan_jurnal', function (Blueprint $table) {
            $table->id('pengaturan_id');
            $table->string('grup', 50)->index();
            $table->string('kunci', 100)->unique();
            $table->string('label', 255);
            $table->text('deskripsi')->nullable();
            $table->string('tipe_nilai', 20)->default('COA'); // 'COA' atau 'TEXT'
            $table->foreignId('nilai_coa_id')->nullable()->constrained('mst_coa', 'coa_id')->nullOnDelete();
            $table->text('nilai_text')->nullable();
            $table->integer('urutan')->default(1);

            // Audit Trail
            $table->string('created_by', 100)->nullable();
            $table->string('updated_by', 100)->nullable();
            $table->string('deleted_by', 100)->nullable();
            $table->boolean('deleted_st')->default(false);
            $table->boolean('active_st')->default(true);
            $table->integer('version_no')->default(1);
            $table->timestamps();
        });

        // Seed data awal pemetaan akun otomatis
        $coas = DB::table('mst_coa')->pluck('coa_id', 'kode_akun');

        $initials = [
            // --- GRUP HPP PRODUKSI WIP ---
            [
                'grup'         => 'HPP_WIP',
                'kunci'        => 'HPP_WIP_AKUN_WIP',
                'label'        => 'Akun Persediaan WIP & FG (Debet)',
                'deskripsi'    => 'Akun penampung nilai kapitalisasi HPP Keripik Curah / WIP hasil produksi penggorengan',
                'tipe_nilai'   => 'COA',
                'nilai_coa_id' => $coas['1151'] ?? null,
                'nilai_text'   => null,
                'urutan'       => 1,
            ],
            [
                'grup'         => 'HPP_WIP',
                'kunci'        => 'HPP_WIP_AKUN_BAHAN',
                'label'        => 'Akun Persediaan Bahan Baku (Kredit)',
                'deskripsi'    => 'Akun kredit pengeluaran bahan baku & penolong (Singkong, Minyak, Bumbu)',
                'tipe_nilai'   => 'COA',
                'nilai_coa_id' => $coas['1150'] ?? null,
                'nilai_text'   => null,
                'urutan'       => 2,
            ],
            [
                'grup'         => 'HPP_WIP',
                'kunci'        => 'HPP_WIP_AKUN_GAS',
                'label'        => 'Akun Akrual Tagihan Gas CNG (Kredit)',
                'deskripsi'    => 'Akun kredit kewajiban/akrual pemakaian gas alam CNG harian pabrik',
                'tipe_nilai'   => 'COA',
                'nilai_coa_id' => $coas['2100'] ?? null,
                'nilai_text'   => null,
                'urutan'       => 3,
            ],
            [
                'grup'         => 'HPP_WIP',
                'kunci'        => 'HPP_WIP_AKUN_TENAGA_KERJA',
                'label'        => 'Akun Hutang Gaji / BTKL (Kredit)',
                'deskripsi'    => 'Akun kredit kewajiban upah tenaga kerja langsung / borongan harian produksi',
                'tipe_nilai'   => 'COA',
                'nilai_coa_id' => $coas['2102'] ?? null,
                'nilai_text'   => null,
                'urutan'       => 4,
            ],
            [
                'grup'         => 'HPP_WIP',
                'kunci'        => 'HPP_WIP_AKUN_OVERHEAD',
                'label'        => 'Akun Overhead Pabrik / FOH (Kredit)',
                'deskripsi'    => 'Akun kredit alokasi beban overhead pabrik harian jika ada',
                'tipe_nilai'   => 'COA',
                'nilai_coa_id' => $coas['2100'] ?? null,
                'nilai_text'   => null,
                'urutan'       => 5,
            ],
            [
                'grup'         => 'HPP_WIP',
                'kunci'        => 'HPP_WIP_TEMPLATE_KET',
                'label'        => 'Template Keterangan Jurnal HPP WIP',
                'deskripsi'    => 'Format keterangan seragam seluruh baris jurnal. Variabel tersedia: {tgl}, {shift}, {total_kg}, {lini}',
                'tipe_nilai'   => 'TEXT',
                'nilai_coa_id' => null,
                'nilai_text'   => 'Biaya Produksi & HPP WIP ({tgl}) - Shift {shift} ({total_kg} kg WIP)',
                'urutan'       => 6,
            ],

            // --- GRUP PEMBELIAN / HUTANG USAHA ---
            [
                'grup'         => 'PEMBELIAN',
                'kunci'        => 'PEMBELIAN_AKUN_PERSEDIAAN',
                'label'        => 'Akun Persediaan Bahan Baku (Debet)',
                'deskripsi'    => 'Akun penerimaan stok bahan baku dan penolong yang dibeli dari supplier',
                'tipe_nilai'   => 'COA',
                'nilai_coa_id' => $coas['1150'] ?? null,
                'nilai_text'   => null,
                'urutan'       => 1,
            ],
            [
                'grup'         => 'PEMBELIAN',
                'kunci'        => 'PEMBELIAN_AKUN_HUTANG',
                'label'        => 'Akun Hutang Usaha Supplier (Kredit)',
                'deskripsi'    => 'Akun kewajiban pembayaran tagihan pembelian supplier',
                'tipe_nilai'   => 'COA',
                'nilai_coa_id' => $coas['2100'] ?? null,
                'nilai_text'   => null,
                'urutan'       => 2,
            ],
            [
                'grup'         => 'PEMBELIAN',
                'kunci'        => 'PEMBELIAN_AKUN_PPN_MASUKAN',
                'label'        => 'Akun PPN Masukan (Debet)',
                'deskripsi'    => 'Akun pajak masukan 11% dari faktur pembelian supplier',
                'tipe_nilai'   => 'COA',
                'nilai_coa_id' => $coas['1130'] ?? null,
                'nilai_text'   => null,
                'urutan'       => 3,
            ],
            [
                'grup'         => 'PEMBELIAN',
                'kunci'        => 'PEMBELIAN_AKUN_PPH_22',
                'label'        => 'Akun Potongan PPh 22 (Kredit)',
                'deskripsi'    => 'Akun potongan pajak PPh pasal 22 pembelian',
                'tipe_nilai'   => 'COA',
                'nilai_coa_id' => $coas['2106'] ?? null,
                'nilai_text'   => null,
                'urutan'       => 4,
            ],
            [
                'grup'         => 'PEMBELIAN',
                'kunci'        => 'PEMBELIAN_AKUN_PPH_23',
                'label'        => 'Akun Potongan PPh 23 (Kredit)',
                'deskripsi'    => 'Akun potongan pajak PPh pasal 23 pembelian jasa/sewa',
                'tipe_nilai'   => 'COA',
                'nilai_coa_id' => $coas['2105'] ?? null,
                'nilai_text'   => null,
                'urutan'       => 5,
            ],
            [
                'grup'         => 'PEMBELIAN',
                'kunci'        => 'PEMBELIAN_TEMPLATE_KET',
                'label'        => 'Template Keterangan Jurnal Pembelian',
                'deskripsi'    => 'Format keterangan baris jurnal pembelian. Variabel tersedia: {supplier}, {qty}, {satuan}, {nama_barang}, {harga}, {no_faktur}',
                'tipe_nilai'   => 'TEXT',
                'nilai_coa_id' => null,
                'nilai_text'   => '{supplier} - {qty} {satuan} {nama_barang} @{harga}',
                'urutan'       => 6,
            ],
        ];

        foreach ($initials as $item) {
            DB::table('mst_pengaturan_jurnal')->insert(array_merge($item, [
                'created_by' => 'SYSTEM',
                'updated_by' => 'SYSTEM',
                'active_st'  => true,
                'deleted_st' => false,
                'version_no' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mst_pengaturan_jurnal');
    }
};
