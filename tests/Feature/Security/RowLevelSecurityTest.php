<?php

namespace Tests\Feature\Security;

use App\Models\Auth\SysUserGudang;
use App\Models\MasterData\MstGudang;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Row-Level Security (RLS) Test Suite
 * 
 * Tests PostgreSQL RLS policies on mst_gudang table
 */
class RowLevelSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Skip if not using PostgreSQL
        if (DB::getDriverName() !== 'pgsql') {
            $this->markTestSkipped('RLS tests require PostgreSQL');
        }
    }

    /** @test */
    public function test_superadmin_can_see_all_gudang()
    {
        // Arrange: Create gudang
        $gudang1 = MstGudang::factory()->create(['gudang_cd' => 'GD001', 'gudang_nm' => 'Gudang 1']);
        $gudang2 = MstGudang::factory()->create(['gudang_cd' => 'GD002', 'gudang_nm' => 'Gudang 2']);
        $gudang3 = MstGudang::factory()->create(['gudang_cd' => 'GD003', 'gudang_nm' => 'Gudang 3']);

        // Arrange: Create superadmin user
        $superadmin = User::factory()->create(['role_cd' => 'SUPERADMIN']);

        // Act: Login as superadmin
        $this->actingAs($superadmin);

        // Assert: Should see all gudang
        $gudangs = MstGudang::all();
        $this->assertCount(3, $gudangs);
        $this->assertTrue($gudangs->contains('gudang_id', $gudang1->gudang_id));
        $this->assertTrue($gudangs->contains('gudang_id', $gudang2->gudang_id));
        $this->assertTrue($gudangs->contains('gudang_id', $gudang3->gudang_id));
    }

    /** @test */
    public function test_regular_user_only_sees_assigned_gudang()
    {
        // Arrange: Create gudang
        $gudang1 = MstGudang::factory()->create(['gudang_cd' => 'GD001']);
        $gudang2 = MstGudang::factory()->create(['gudang_cd' => 'GD002']);
        $gudang3 = MstGudang::factory()->create(['gudang_cd' => 'GD003']);

        // Arrange: Create regular user with gudang 1 & 2 assigned
        $user = User::factory()->create(['role_cd' => 'ADMIN_GUDANG']);
        SysUserGudang::create([
            'user_id' => $user->id,
            'gudang_id' => $gudang1->gudang_id,
            'is_primary' => true,
            'active_st' => true,
        ]);
        SysUserGudang::create([
            'user_id' => $user->id,
            'gudang_id' => $gudang2->gudang_id,
            'is_primary' => false,
            'active_st' => true,
        ]);

        // Act: Login as regular user
        $this->actingAs($user);

        // Assert: Should only see gudang 1 & 2, NOT gudang 3
        $gudangs = MstGudang::all();
        $this->assertCount(2, $gudangs);
        $this->assertTrue($gudangs->contains('gudang_id', $gudang1->gudang_id));
        $this->assertTrue($gudangs->contains('gudang_id', $gudang2->gudang_id));
        $this->assertFalse($gudangs->contains('gudang_id', $gudang3->gudang_id));
    }

    /** @test */
    public function test_user_cannot_update_unassigned_gudang()
    {
        // Arrange: Create gudang
        $gudang1 = MstGudang::factory()->create(['gudang_cd' => 'GD001']);
        $gudang2 = MstGudang::factory()->create(['gudang_cd' => 'GD002']);

        // Arrange: User only assigned to gudang 1
        $user = User::factory()->create(['role_cd' => 'FINANCE']);
        SysUserGudang::create([
            'user_id' => $user->id,
            'gudang_id' => $gudang1->gudang_id,
            'is_primary' => true,
            'active_st' => true,
        ]);

        // Act: Login as user
        $this->actingAs($user);

        // Assert: Can update gudang 1 (assigned)
        $gudang1->update(['gudang_nm' => 'Updated Gudang 1']);
        $this->assertEquals('Updated Gudang 1', $gudang1->fresh()->gudang_nm);

        // Assert: CANNOT update gudang 2 (not assigned) - RLS blocks at DB level
        $this->expectException(\Exception::class);
        $gudang2->update(['gudang_nm' => 'Updated Gudang 2']);
    }

    /** @test */
    public function test_user_without_assignment_sees_no_gudang()
    {
        // Arrange: Create gudang
        MstGudang::factory()->create(['gudang_cd' => 'GD001']);
        MstGudang::factory()->create(['gudang_cd' => 'GD002']);

        // Arrange: Create user WITHOUT any gudang assignment
        $user = User::factory()->create(['role_cd' => 'STAFF_PRODUKSI']);

        // Act: Login as user with no assignment
        $this->actingAs($user);

        // Assert: Should see ZERO gudang
        $gudangs = MstGudang::all();
        $this->assertCount(0, $gudangs);
    }

    /** @test */
    public function test_guest_user_cannot_access_gudang()
    {
        // Arrange: Create gudang
        MstGudang::factory()->create(['gudang_cd' => 'GD001']);

        // Act: No authentication (guest)
        Auth::logout();

        // Assert: Should see ZERO gudang (RLS blocks unauthenticated access)
        $gudangs = MstGudang::all();
        $this->assertCount(0, $gudangs);
    }

    /** @test */
    public function test_rls_policies_exist_in_database()
    {
        // Query PostgreSQL system catalog to verify policies exist
        $policies = DB::select("
            SELECT polname, polcmd
            FROM pg_policy
            WHERE polrelid = 'mst_gudang'::regclass
            ORDER BY polname
        ");

        // Assert: Should have 4 policies (SELECT, INSERT, UPDATE, DELETE)
        $this->assertCount(4, $policies);
        
        $policyNames = collect($policies)->pluck('polname')->toArray();
        $this->assertContains('mst_gudang_select_policy', $policyNames);
        $this->assertContains('mst_gudang_insert_policy', $policyNames);
        $this->assertContains('mst_gudang_update_policy', $policyNames);
        $this->assertContains('mst_gudang_delete_policy', $policyNames);
    }

    /** @test */
    public function test_helper_functions_exist()
    {
        // Test helper functions exist and work
        $result = DB::select("
            SELECT 
                proname as function_name
            FROM pg_proc
            WHERE proname IN (
                'get_current_app_user_id',
                'is_current_user_superadmin',
                'get_current_user_allowed_gudang_ids',
                'can_access_gudang'
            )
        ");

        // Assert: All 4 helper functions should exist
        $this->assertCount(4, $result);
    }

    /** @test */
    public function test_session_variables_are_set_correctly()
    {
        // Arrange: Create user with assigned gudang
        $gudang1 = MstGudang::factory()->create();
        $user = User::factory()->create(['role_cd' => 'ADMIN_GUDANG']);
        SysUserGudang::create([
            'user_id' => $user->id,
            'gudang_id' => $gudang1->gudang_id,
            'is_primary' => true,
            'active_st' => true,
        ]);

        // Act: Login (middleware sets session variables)
        $this->actingAs($user);
        
        // Trigger a query to ensure middleware has run
        MstGudang::all();

        // Assert: Check session variables are set
        $userId = DB::selectOne("SELECT get_current_app_user_id() as id")->id;
        $isSuperadmin = DB::selectOne("SELECT is_current_user_superadmin() as is_super")->is_super;
        
        $this->assertEquals($user->id, $userId);
        $this->assertFalse($isSuperadmin);
    }

    /** @test */
    public function test_audit_log_table_exists()
    {
        // Query to check if audit log table exists
        $exists = DB::selectOne("
            SELECT EXISTS (
                SELECT FROM information_schema.tables 
                WHERE table_name = 'sys_security_audit_log'
            ) as exists
        ")->exists;

        $this->assertTrue($exists, 'Audit log table should exist');
    }
}
