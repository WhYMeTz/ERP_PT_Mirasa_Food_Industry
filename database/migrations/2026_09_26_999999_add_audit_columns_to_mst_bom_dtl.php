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
        if (!Schema::hasTable('mst_bom_dtl')) {
            return;
        }

        Schema::table('mst_bom_dtl', function (Blueprint $table) {
            if (!Schema::hasColumn('mst_bom_dtl', 'created_by')) {
                $table->string('created_by', 100)->nullable();
            }
            if (!Schema::hasColumn('mst_bom_dtl', 'updated_by')) {
                $table->string('updated_by', 100)->nullable();
            }
            if (!Schema::hasColumn('mst_bom_dtl', 'deleted_at')) {
                $table->timestamp('deleted_at')->nullable();
            }
            if (!Schema::hasColumn('mst_bom_dtl', 'deleted_by')) {
                $table->string('deleted_by', 100)->nullable();
            }
            if (!Schema::hasColumn('mst_bom_dtl', 'deleted_st')) {
                $table->boolean('deleted_st')->default(false);
            }
            if (!Schema::hasColumn('mst_bom_dtl', 'active_st')) {
                $table->boolean('active_st')->default(true);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('mst_bom_dtl')) {
            return;
        }

        $columns = array_filter([
            'created_by',
            'updated_by',
            'deleted_at',
            'deleted_by',
            'deleted_st',
            'active_st',
        ], fn (string $column): bool => Schema::hasColumn('mst_bom_dtl', $column));

        if ($columns === []) {
            return;
        }

        Schema::table('mst_bom_dtl', function (Blueprint $table) use ($columns) {
            $table->dropColumn($columns);
        });
    }
};
