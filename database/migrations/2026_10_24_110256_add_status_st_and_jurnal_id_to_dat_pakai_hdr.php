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
        Schema::table('dat_pakai_hdr', function (Blueprint $table) {
            if (!Schema::hasColumn('dat_pakai_hdr', 'status_st')) {
                $table->string('status_st', 20)->default('DRAFT')->after('catatan_txt');
            }
            if (!Schema::hasColumn('dat_pakai_hdr', 'posted_at')) {
                $table->timestamp('posted_at')->nullable()->after('status_st');
            }
            if (!Schema::hasColumn('dat_pakai_hdr', 'posted_by')) {
                $table->string('posted_by', 100)->nullable()->after('posted_at');
            }
            if (!Schema::hasColumn('dat_pakai_hdr', 'jurnal_id')) {
                $table->foreignId('jurnal_id')->nullable()->after('posted_by')
                    ->constrained('dat_jurnal_hdr', 'jurnal_id')
                    ->nullOnDelete();
            }
        });

        // Tandai data historis yang sudah ada sebagai POSTED agar tidak ganda
        \Illuminate\Support\Facades\DB::table('dat_pakai_hdr')
            ->where('status_st', 'DRAFT')
            ->update([
                'status_st' => 'POSTED',
                'posted_at' => now(),
                'posted_by' => 'INITIAL_SYSTEM',
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dat_pakai_hdr', function (Blueprint $table) {
            if (Schema::hasColumn('dat_pakai_hdr', 'jurnal_id')) {
                $table->dropForeign(['jurnal_id']);
                $table->dropColumn('jurnal_id');
            }
            if (Schema::hasColumn('dat_pakai_hdr', 'posted_by')) {
                $table->dropColumn('posted_by');
            }
            if (Schema::hasColumn('dat_pakai_hdr', 'posted_at')) {
                $table->dropColumn('posted_at');
            }
            if (Schema::hasColumn('dat_pakai_hdr', 'status_st')) {
                $table->dropColumn('status_st');
            }
        });
    }
};
