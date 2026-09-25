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
        Schema::table('dat_po_hdr', function (Blueprint $table) {
            $table->date('tgl_estimasi_datang')->nullable()->after('po_tgl');
            $table->timestamp('closed_at')->nullable()->after('catatan_txt');
            $table->string('closed_by', 100)->nullable()->after('closed_at');
            $table->text('closed_reason')->nullable()->after('closed_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dat_po_hdr', function (Blueprint $table) {
            $table->dropColumn(['tgl_estimasi_datang', 'closed_at', 'closed_by', 'closed_reason']);
        });
    }
};
