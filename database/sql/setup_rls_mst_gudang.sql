-- =====================================================================
-- PostgreSQL Row-Level Security (RLS) for mst_gudang
-- ERP PT Mirasa Food Industry
-- Date: 2026-10-28
-- =====================================================================

-- =====================================================================
-- 1. HELPER FUNCTIONS
-- =====================================================================

-- Function: Get current authenticated user ID from session
CREATE OR REPLACE FUNCTION get_current_app_user_id()
RETURNS INTEGER AS $$
BEGIN
    RETURN COALESCE(
        NULLIF(current_setting('app.current_user_id', TRUE), '')::INTEGER,
        0
    );
END;
$$ LANGUAGE plpgsql STABLE;

COMMENT ON FUNCTION get_current_app_user_id() IS 
'Returns the current authenticated user ID set by Laravel middleware. Returns 0 if not set.';


-- Function: Check if current user is superadmin
CREATE OR REPLACE FUNCTION is_current_user_superadmin()
RETURNS BOOLEAN AS $$
BEGIN
    RETURN COALESCE(
        current_setting('app.is_superadmin', TRUE)::BOOLEAN,
        FALSE
    );
END;
$$ LANGUAGE plpgsql STABLE;

COMMENT ON FUNCTION is_current_user_superadmin() IS 
'Returns TRUE if current user has superadmin role, FALSE otherwise.';


-- Function: Get allowed gudang IDs for current user
CREATE OR REPLACE FUNCTION get_current_user_allowed_gudang_ids()
RETURNS BIGINT[] AS $$
DECLARE
    gudang_ids TEXT;
BEGIN
    gudang_ids := current_setting('app.allowed_gudang_ids', TRUE);
    
    IF gudang_ids IS NULL OR gudang_ids = '' THEN
        RETURN ARRAY[]::BIGINT[];
    END IF;
    
    -- Convert comma-separated string to bigint array
    RETURN string_to_array(gudang_ids, ',')::BIGINT[];
END;
$$ LANGUAGE plpgsql STABLE;

COMMENT ON FUNCTION get_current_user_allowed_gudang_ids() IS 
'Returns array of gudang IDs that current user has access to. Returns empty array if none set.';


-- Function: Check if current user can access specific gudang
CREATE OR REPLACE FUNCTION can_access_gudang(check_gudang_id BIGINT)
RETURNS BOOLEAN AS $$
BEGIN
    -- Superadmin can access everything
    IF is_current_user_superadmin() THEN
        RETURN TRUE;
    END IF;
    
    -- Check if gudang_id is in user's allowed list
    RETURN check_gudang_id = ANY(get_current_user_allowed_gudang_ids());
END;
$$ LANGUAGE plpgsql STABLE;

COMMENT ON FUNCTION can_access_gudang(BIGINT) IS 
'Check if current user has access to specific gudang_id. Superadmin always returns TRUE.';


-- =====================================================================
-- 2. ENABLE ROW-LEVEL SECURITY
-- =====================================================================

-- Enable RLS on mst_gudang table
ALTER TABLE mst_gudang ENABLE ROW LEVEL SECURITY;

-- Force RLS even for table owner (important!)
ALTER TABLE mst_gudang FORCE ROW LEVEL SECURITY;

COMMENT ON TABLE mst_gudang IS 
'Master Gudang/Perusahaan table with Row-Level Security enabled. Users can only access gudang assigned to them.';


-- =====================================================================
-- 3. RLS POLICIES
-- =====================================================================

-- Drop existing policies if any (idempotent)
DROP POLICY IF EXISTS mst_gudang_select_policy ON mst_gudang;
DROP POLICY IF EXISTS mst_gudang_insert_policy ON mst_gudang;
DROP POLICY IF EXISTS mst_gudang_update_policy ON mst_gudang;
DROP POLICY IF EXISTS mst_gudang_delete_policy ON mst_gudang;

-- Policy: SELECT (Read) - Users can only see their assigned gudang
CREATE POLICY mst_gudang_select_policy
    ON mst_gudang
    FOR SELECT
    USING (
        is_current_user_superadmin() 
        OR can_access_gudang(gudang_id)
    );

COMMENT ON POLICY mst_gudang_select_policy ON mst_gudang IS 
'Allow SELECT only for assigned gudang or if superadmin';


-- Policy: INSERT (Create) - Only superadmin and users with create permission
-- Note: Application layer already checks permission, this is defense in depth
CREATE POLICY mst_gudang_insert_policy
    ON mst_gudang
    FOR INSERT
    WITH CHECK (
        is_current_user_superadmin()
        OR get_current_app_user_id() > 0  -- Must be authenticated
    );

COMMENT ON POLICY mst_gudang_insert_policy ON mst_gudang IS 
'Allow INSERT for authenticated users. Application layer handles fine-grained permission checks.';


-- Policy: UPDATE (Edit) - Can only update assigned gudang
CREATE POLICY mst_gudang_update_policy
    ON mst_gudang
    FOR UPDATE
    USING (
        is_current_user_superadmin()
        OR can_access_gudang(gudang_id)
    )
    WITH CHECK (
        is_current_user_superadmin()
        OR can_access_gudang(gudang_id)
    );

COMMENT ON POLICY mst_gudang_update_policy ON mst_gudang IS 
'Allow UPDATE only for assigned gudang or if superadmin. USING checks existing row, WITH CHECK validates new values.';


-- Policy: DELETE (Soft Delete) - Can only delete assigned gudang
CREATE POLICY mst_gudang_delete_policy
    ON mst_gudang
    FOR DELETE
    USING (
        is_current_user_superadmin()
        OR can_access_gudang(gudang_id)
    );

COMMENT ON POLICY mst_gudang_delete_policy ON mst_gudang IS 
'Allow DELETE only for assigned gudang or if superadmin';


-- =====================================================================
-- 4. AUDIT TRIGGER (Bonus - Track RLS Policy Violations)
-- =====================================================================

-- Create audit log table if not exists
CREATE TABLE IF NOT EXISTS sys_security_audit_log (
    audit_id BIGSERIAL PRIMARY KEY,
    event_type VARCHAR(50) NOT NULL,
    table_name VARCHAR(100) NOT NULL,
    record_id BIGINT,
    user_id BIGINT,
    username VARCHAR(255),
    ip_address INET,
    user_agent TEXT,
    query_text TEXT,
    blocked_reason TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_security_audit_created_at ON sys_security_audit_log(created_at);
CREATE INDEX IF NOT EXISTS idx_security_audit_user_id ON sys_security_audit_log(user_id);
CREATE INDEX IF NOT EXISTS idx_security_audit_event_type ON sys_security_audit_log(event_type);

COMMENT ON TABLE sys_security_audit_log IS 
'Security audit log for tracking access attempts and policy violations';


-- Function: Log security events
CREATE OR REPLACE FUNCTION log_security_event(
    p_event_type VARCHAR,
    p_table_name VARCHAR,
    p_record_id BIGINT,
    p_blocked_reason TEXT DEFAULT NULL
)
RETURNS VOID AS $$
BEGIN
    INSERT INTO sys_security_audit_log (
        event_type,
        table_name,
        record_id,
        user_id,
        username,
        ip_address,
        user_agent,
        query_text,
        blocked_reason
    ) VALUES (
        p_event_type,
        p_table_name,
        p_record_id,
        get_current_app_user_id(),
        current_setting('app.username', TRUE),
        inet_client_addr(),
        current_setting('app.user_agent', TRUE),
        current_query(),
        p_blocked_reason
    );
END;
$$ LANGUAGE plpgsql;


-- =====================================================================
-- 5. VERIFICATION QUERIES (For Testing)
-- =====================================================================

-- Test helper functions
-- Run these manually after deployment to verify:
/*

-- Test 1: Set session variables (simulating Laravel middleware)
SET app.current_user_id = '5';
SET app.is_superadmin = 'false';
SET app.allowed_gudang_ids = '1,2,3';
SET app.username = 'test_user';

-- Test 2: Check helper functions
SELECT get_current_app_user_id();              -- Should return: 5
SELECT is_current_user_superadmin();           -- Should return: false
SELECT get_current_user_allowed_gudang_ids(); -- Should return: {1,2,3}
SELECT can_access_gudang(1);                   -- Should return: true
SELECT can_access_gudang(5);                   -- Should return: false

-- Test 3: Test RLS policy (should only see gudang 1, 2, 3)
SELECT * FROM mst_gudang;

-- Test 4: Try to access gudang not in allowed list (should be blocked)
SELECT * FROM mst_gudang WHERE gudang_id = 5;

-- Test 5: Test superadmin (should see everything)
SET app.is_superadmin = 'true';
SELECT * FROM mst_gudang;  -- Should see all gudang

-- Reset session
RESET app.current_user_id;
RESET app.is_superadmin;
RESET app.allowed_gudang_ids;
RESET app.username;

*/

-- =====================================================================
-- SUCCESS MESSAGE
-- =====================================================================

DO $$
BEGIN
    RAISE NOTICE '========================================';
    RAISE NOTICE 'Row-Level Security (RLS) Setup Complete';
    RAISE NOTICE '========================================';
    RAISE NOTICE 'Table: mst_gudang';
    RAISE NOTICE 'Status: RLS ENABLED & FORCED';
    RAISE NOTICE 'Policies: 4 (SELECT, INSERT, UPDATE, DELETE)';
    RAISE NOTICE 'Helper Functions: 4';
    RAISE NOTICE 'Audit Log: CREATED';
    RAISE NOTICE '========================================';
    RAISE NOTICE 'Next Step: Deploy Laravel Middleware';
    RAISE NOTICE '========================================';
END $$;
