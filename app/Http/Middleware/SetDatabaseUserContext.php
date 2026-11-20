<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware: Set Database User Context for PostgreSQL Row-Level Security
 * 
 * Sets PostgreSQL session variables before each request to enable RLS policies:
 * - app.current_user_id: Laravel user ID
 * - app.is_superadmin: boolean
 * - app.allowed_gudang_ids: comma-separated gudang IDs
 * - app.username: username for audit
 * - app.user_agent: HTTP User-Agent for audit
 */
class SetDatabaseUserContext
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only set context if user is authenticated
        if (Auth::check()) {
            $user = Auth::user();
            
            try {
                // Get allowed gudang IDs for this user
                $allowedGudangIds = $user->getAllowedGudangIds();
                $gudangIdsString = empty($allowedGudangIds) 
                    ? '' 
                    : implode(',', $allowedGudangIds);
                
                // Set PostgreSQL session variables for RLS
                $this->setSessionVariable('app.current_user_id', (string) $user->id);
                $this->setSessionVariable('app.is_superadmin', $user->isSuperAdmin() ? 'true' : 'false');
                $this->setSessionVariable('app.allowed_gudang_ids', $gudangIdsString);
                $this->setSessionVariable('app.username', $user->username ?? $user->email ?? 'unknown');
                $this->setSessionVariable('app.user_agent', $request->userAgent() ?? 'unknown');
                
                // Log context set (only in debug mode)
                if (config('app.debug')) {
                    Log::debug('Database user context set', [
                        'user_id' => $user->id,
                        'is_superadmin' => $user->isSuperAdmin(),
                        'allowed_gudang_ids' => $gudangIdsString,
                    ]);
                }
                
            } catch (\Exception $e) {
                // Log error but don't block request
                Log::error('Failed to set database user context', [
                    'error' => $e->getMessage(),
                    'user_id' => $user->id ?? null,
                ]);
            }
        } else {
            // Guest user - set minimal context
            try {
                $this->setSessionVariable('app.current_user_id', '0');
                $this->setSessionVariable('app.is_superadmin', 'false');
                $this->setSessionVariable('app.allowed_gudang_ids', '');
                $this->setSessionVariable('app.username', 'guest');
                $this->setSessionVariable('app.user_agent', $request->userAgent() ?? 'unknown');
            } catch (\Exception $e) {
                Log::error('Failed to set guest database context', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $next($request);
    }

    /**
     * Set a PostgreSQL session variable using SET LOCAL
     * 
     * SET LOCAL is transaction-scoped, so it's automatically reset after the request
     *
     * @param string $name
     * @param string $value
     * @return void
     */
    protected function setSessionVariable(string $name, string $value): void
    {
        // Escape single quotes in value to prevent SQL injection
        $escapedValue = str_replace("'", "''", $value);
        
        // Use SET LOCAL for transaction-scoped variables
        // This automatically resets after the transaction/request ends
        DB::statement("SET LOCAL {$name} = '{$escapedValue}'");
    }

    /**
     * Terminate method - Optional cleanup
     * 
     * Called after the response has been sent to the browser.
     * PostgreSQL session variables are automatically reset after transaction,
     * but we can log or perform cleanup here if needed.
     *
     * @param \Illuminate\Http\Request  $request
     * @param \Symfony\Component\HttpFoundation\Response  $response
     * @return void
     */
    public function terminate(Request $request, Response $response): void
    {
        // Optional: Log access patterns for security analysis
        if (Auth::check() && config('app.debug')) {
            Log::debug('Request completed with user context', [
                'user_id' => Auth::id(),
                'status' => $response->getStatusCode(),
                'route' => $request->route()?->getName(),
            ]);
        }
    }
}
