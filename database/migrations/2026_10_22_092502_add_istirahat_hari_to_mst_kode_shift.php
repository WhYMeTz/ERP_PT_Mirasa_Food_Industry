<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mst_kode_shift', function (Blueprint $t) {
            $t->decimal('istirahat_jumat', 3, 1)->nullable();
            $t->decimal('istirahat_sabtu', 3, 1)->nullable();
        });

        // Aturan: Senin-Kamis 1 jam (bawaan), Jumat 1,5 jam, Sabtu 0 untuk shift A-F
        DB::table('mst_kode_shift')->whereIn('kode', ['A', 'B', 'C', 'D', 'E', 'F'])
            ->update(['istirahat_jumat' => 1.5, 'istirahat_sabtu' => 0]);
    }

    public function down(): void
    {
        Schema::table('mst_kode_shift', function (Blueprint $t) {
            $t->dropColumn(['istirahat_jumat', 'istirahat_sabtu']);
        });
    }
};
