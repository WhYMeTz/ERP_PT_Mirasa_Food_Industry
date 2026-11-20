<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if using PostgreSQL
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        // Read SQL file
        $sqlPath = database_path('sql/setup_rls_mst_gudang.sql');
        
        if (!File::exists($sqlPath)) {
            throw new \RuntimeException("RLS SQL file not found at {$sqlPath}");
        }

        $sql = File::get($sqlPath);

        // Execute SQL script
        DB::unprepared($sql);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        try {
            // Disable RLS
            DB::statement('ALTER TABLE mst_gudang DISABLE ROW LEVEL SECURITY');
            
            // Drop policies
            DB::statement('DROP POLICY IF EXISTS mst_gudang_select_policy ON mst_gudang');
            DB::statement('DROP POLICY IF EXISTS mst_gudang_insert_policy ON mst_gudang');
            DB::statement('DROP POLICY IF EXISTS mst_gudang_update_policy ON mst_gudang');
            DB::statement('DROP POLICY IF EXISTS mst_gudang_delete_policy ON mst_gudang');
            
            // Drop functions
            DB::statement('DROP FUNCTION IF EXISTS is_admin_or_superadmin() CASCADE');
            DB::statement('DROP FUNCTION IF EXISTS has_warehouse_access(BIGINT) CASCADE');
            DB::statement('DROP FUNCTION IF EXISTS log_unauthorized_warehouse_access() CASCADE');
            DB::statement('DROP FUNCTION IF EXISTS get_current_app_user_id()');
            DB::statement('DROP FUNCTION IF EXISTS is_current_user_superadmin()');
            DB::statement('DROP FUNCTION IF EXISTS get_current_user_allowed_gudang_ids()');
            DB::statement('DROP FUNCTION IF EXISTS can_access_gudang(INTEGER)');
            DB::statement('DROP FUNCTION IF EXISTS log_security_event(VARCHAR, VARCHAR, INTEGER, TEXT)');
            DB::statement('DROP TABLE IF EXISTS sys_security_audit_log');
        } catch (\Exception $e) {
            logger()->error('Failed to disable RLS: ' . $e->getMessage());
        }
    }
};
