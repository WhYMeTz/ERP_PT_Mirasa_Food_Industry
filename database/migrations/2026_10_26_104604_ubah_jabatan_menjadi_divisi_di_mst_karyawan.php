<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Teks lama (huruf besar) => kode Divisi / Bagian baru. Yang tidak dikenal menjadi UMUM. */
    private array $peta = [
        'UMUM' => 'UMUM', 'DRIVER' => 'DRIVER', 'SECURITY' => 'SECURITY', 'PACKING' => 'PACKING',
        'POTONG RENDEM' => 'POT.RENDEM', 'POT RENDEM' => 'POT.RENDEM', 'POT.RENDEM' => 'POT.RENDEM',
        'BATCH FRYER' => 'BATCH FRYER', 'SORTIR AC' => 'SORTIR AC', 'MAKARONI' => 'MAKARONI',
        'KEUANGAN' => 'KEUANGAN', 'QUALITYCONTROL' => 'QUALITYCONTROL', 'QUALITY CONTROL' => 'QUALITYCONTROL', 'QC' => 'QUALITYCONTROL',
    ];

    public function up(): void
    {
        Schema::table('mst_karyawan', function (Blueprint $t) {
            $t->string('jabatan_lama', 150)->nullable();   // cadangan teks jabatan sebelum diubah
        });

        DB::table('mst_karyawan')->update(['jabatan_lama' => DB::raw('jabatan_nm')]);

        foreach (DB::table('mst_karyawan')->get(['karyawan_id', 'jabatan_nm']) as $r) {
            $kunci = strtoupper(trim((string) $r->jabatan_nm));
            DB::table('mst_karyawan')->where('karyawan_id', $r->karyawan_id)
                ->update(['jabatan_nm' => $this->peta[$kunci] ?? 'UMUM']);
        }
    }

    public function down(): void
    {
        DB::table('mst_karyawan')->whereNotNull('jabatan_lama')->update(['jabatan_nm' => DB::raw('jabatan_lama')]);

        Schema::table('mst_karyawan', function (Blueprint $t) {
            $t->dropColumn('jabatan_lama');
        });
    }
};