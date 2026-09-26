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
        Schema::table('mst_bom_dtl', function (Blueprint $table) {
            $table->string('created_by', 100)->nullable();
            $table->string('updated_by', 100)->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->string('deleted_by', 100)->nullable();
            $table->boolean('deleted_st')->default(false);
            $table->boolean('active_st')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mst_bom_dtl', function (Blueprint $table) {
            $table->dropColumn([
                'created_by',
                'updated_by',
                'deleted_at',
                'deleted_by',
                'deleted_st',
                'active_st',
            ]);
        });
    }
};
