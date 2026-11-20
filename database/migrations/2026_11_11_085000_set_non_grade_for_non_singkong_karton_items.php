<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\MasterData\MstBarang;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Ambil daftar barang_id yang bukan Singkong dan bukan Karton (Bahan Non-Grade)
        $allBarang = MstBarang::all();
        $nonGradeBarangIds = $allBarang->filter(function ($b) {
            return !$b->hasGrade();
        })->pluck('barang_id')->toArray();

        if (empty($nonGradeBarangIds)) {
            return;
        }

        // 2. Update dat_stok_batch: ubah grade_cd dari 'A' ke 'NON-GRADE' untuk barang non-grade
        DB::table('dat_stok_batch')
            ->whereIn('barang_id', $nonGradeBarangIds)
            ->whereIn('grade_cd', ['A', 'a'])
            ->update(['grade_cd' => 'NON-GRADE']);

        // 3. Update dat_stok_ledger: ubah grade_cd dari 'A' ke 'NON-GRADE' untuk barang non-grade
        DB::table('dat_stok_ledger')
            ->whereIn('barang_id', $nonGradeBarangIds)
            ->whereIn('grade_cd', ['A', 'a'])
            ->update(['grade_cd' => 'NON-GRADE']);

        // 4. Update dat_terima_dtl: ubah grade_cd dari 'A' ke 'NON-GRADE' untuk barang non-grade
        DB::table('dat_terima_dtl')
            ->whereIn('barang_id', $nonGradeBarangIds)
            ->whereIn('grade_cd', ['A', 'a'])
            ->update(['grade_cd' => 'NON-GRADE']);

        // 5. Update dat_qc_inbound_dtl: ubah grade_cd dari 'A' ke 'NON-GRADE' untuk barang non-grade
        DB::table('dat_qc_inbound_dtl')
            ->whereIn('barang_id', $nonGradeBarangIds)
            ->whereIn('grade_cd', ['A', 'a'])
            ->update(['grade_cd' => 'NON-GRADE']);

        // 6. Update dat_pakai_dtl: ubah grade_cd dari 'A' ke 'NON-GRADE' untuk barang non-grade
        DB::table('dat_pakai_dtl')
            ->whereIn('barang_id', $nonGradeBarangIds)
            ->whereIn('grade_cd', ['A', 'a'])
            ->update(['grade_cd' => 'NON-GRADE']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $allBarang = MstBarang::all();
        $nonGradeBarangIds = $allBarang->filter(function ($b) {
            return !$b->hasGrade();
        })->pluck('barang_id')->toArray();

        if (empty($nonGradeBarangIds)) {
            return;
        }

        DB::table('dat_stok_batch')
            ->whereIn('barang_id', $nonGradeBarangIds)
            ->where('grade_cd', 'NON-GRADE')
            ->update(['grade_cd' => 'A']);

        DB::table('dat_stok_ledger')
            ->whereIn('barang_id', $nonGradeBarangIds)
            ->where('grade_cd', 'NON-GRADE')
            ->update(['grade_cd' => 'A']);

        DB::table('dat_terima_dtl')
            ->whereIn('barang_id', $nonGradeBarangIds)
            ->where('grade_cd', 'NON-GRADE')
            ->update(['grade_cd' => 'A']);

        DB::table('dat_qc_inbound_dtl')
            ->whereIn('barang_id', $nonGradeBarangIds)
            ->where('grade_cd', 'NON-GRADE')
            ->update(['grade_cd' => 'A']);

        DB::table('dat_pakai_dtl')
            ->whereIn('barang_id', $nonGradeBarangIds)
            ->where('grade_cd', 'NON-GRADE')
            ->update(['grade_cd' => 'A']);
    }
};

