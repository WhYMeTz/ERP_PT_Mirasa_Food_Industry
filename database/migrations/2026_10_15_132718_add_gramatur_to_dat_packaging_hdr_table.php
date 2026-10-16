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
        Schema::table('dat_packaging_hdr', function (Blueprint $table) {
            if (!Schema::hasColumn('dat_packaging_hdr', 'gramatur_gr')) {
                $table->decimal('gramatur_gr', 10, 4)->default(0)->after('isi_per_karton_pcs')->comment('Gramatur Berat Bersih per Bungkus (Gram)');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dat_packaging_hdr', function (Blueprint $table) {
            if (Schema::hasColumn('dat_packaging_hdr', 'gramatur_gr')) {
                $table->dropColumn('gramatur_gr');
            }
        });
    }
};
