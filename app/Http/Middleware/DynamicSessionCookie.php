<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Cookie;

class DynamicSessionCookie
{
    public function handle(Request $request, Closure $next)
    {
        // Example logic based on route prefix
        if ($request->is('admin/*')) {
            config(['session.cookie' => 'admin_session']);
        } else {
            config(['session.cookie' => 'user_session']);
        }

        // PayU surl/furl is a cross-site POST. With SameSite=Lax the browser does NOT
        // send our session cookie. Starting a normal session would queue a NEW session
        // cookie and replace the user's real login.
        $isPayUCallback = $request->is('payu/success', 'payu/failure');
        if ($isPayUCallback) {
            config(['session.driver' => 'array']);
        }

        $response = $next($request);

        if ($isPayUCallback) {
            $cookieName = (string) config('session.cookie');
            // IMPORTANT: do NOT use withoutCookie()/Cookie::forget() — that expires and
            // deletes the user's real session cookie in the browser (causes logout).
            // Only strip any Set-Cookie for the session name from THIS response.
            $this->removeSetCookieByName($response, $cookieName);
        }

        return $response;
    }

    private function removeSetCookieByName($response, string $cookieName): void
    {
        $kept = [];
        foreach ($response->headers->getCookies() as $cookie) {
            if ($cookie instanceof Cookie && $cookie->getName() === $cookieName) {
                continue;
            }
            $kept[] = $cookie;
        }

        $response->headers->remove('Set-Cookie');
        foreach ($kept as $cookie) {
            $response->headers->setCookie($cookie);
        }
    }
}
