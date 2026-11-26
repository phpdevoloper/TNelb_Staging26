<?php

namespace App\Http\Middleware;

use App\Helpers\RoleHelper;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Restricts a route to admin logins whose role name (Auth::guard('admin')->user()->name) is one of the
 * listed roles, e.g. ->middleware('admin.role:Super Admin') or 'admin.role:Secretary,President'.
 * Use after auth:admin.
 */
class EnsureAdminRole
{
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        $admin = Auth::guard('admin')->user();

        if (! $admin || ! RoleHelper::matches((string) ($admin->name ?? ''), ...$roles)) {
            abort(403, 'You are not allowed to access this page.');
        }

        return $next($request);
    }
}
