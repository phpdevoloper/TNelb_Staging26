<?php

namespace App\Http\Middleware;

use App\Services\ApplicationOwnerResolver;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Applicant PDF/receipt routes: only the applicant who owns the application may open it.
 * Use after `auth`:  ->middleware(['auth', 'owns.application'])
 * The application id is read from the first of these route parameters that exists.
 * Anything not found or not owned answers 404 (does not reveal whether the id exists).
 */
class EnsureApplicationOwner
{
    private const ID_PARAMETERS = ['application_id', 'login_id', 'loginId'];

    public function handle(Request $request, Closure $next)
    {
        $applicationId = '';
        foreach (self::ID_PARAMETERS as $name) {
            $value = $request->route($name);
            if (is_string($value) && trim($value) !== '') {
                $applicationId = trim($value);
                break;
            }
        }

        $sessionLoginId = trim((string) (Auth::user()->login_id ?? ''));

        if ($applicationId === '' || $sessionLoginId === ''
            || ! app(ApplicationOwnerResolver::class)->isOwnedBy($applicationId, $sessionLoginId)) {
            abort(404);
        }

        return $next($request);
    }
}
