<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sys_user_gudang', function (Blueprint $table) {
            $table->id('user_gudang_id');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('gudang_id')->constrained('mst_gudang', 'gudang_id')->cascadeOnDelete();
            $table->boolean('is_primary')->default(false);

            // Audit Trail 8 Kolom
            $table->string('created_by', 100)->nullable();
            $table->string('updated_by', 100)->nullable();
            $table->string('deleted_by', 100)->nullable();
            $table->boolean('deleted_st')->default(false);
            $table->boolean('active_st')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['user_id', 'gudang_id'], 'uq_user_gudang');
        });

        // Migrasi data pengguna yang sebelumnya sudah memiliki gudang_id
        $users = DB::table('users')->whereNotNull('gudang_id')->get();
        foreach ($users as $u) {
            DB::table('sys_user_gudang')->insert([
                'user_id'    => $u->id,
                'gudang_id'  => $u->gudang_id,
                'is_primary' => true,
                'created_by' => 'MIGRATION',
                'active_st'  => true,
                'deleted_st' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sys_user_gudang');
    }
};
