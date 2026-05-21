<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Adds security headers to every response.
 * Protects against XSS, clickjacking, MIME sniffing, etc.
 */
class SecurityHeadersMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only add headers to HTTP responses (not binary streams)
        if (!method_exists($response, 'header')) {
            return $response;
        }

        $response->header('X-Content-Type-Options',    'nosniff');
        $response->header('X-Frame-Options',            'SAMEORIGIN');
        $response->header('Content-Security-Policy',    $this->buildCsp());
        $response->header('X-XSS-Protection',           '1; mode=block');
        $response->header('Referrer-Policy',             'strict-origin-when-cross-origin');
        $response->header('Permissions-Policy',          'camera=(), microphone=(), geolocation=()');
        if (app()->isProduction() && $request->isSecure()) {
            $response->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }
        $response->header('Cache-Control',               'no-store, no-cache, must-revalidate, max-age=0');
        $response->header('Pragma',                      'no-cache');
        $response->header('Expires',                     '0');

        return $response;
    }

    private function buildCsp(): string
    {
        $appUrl   = rtrim(config('app.url'), '/');
        $payment  = "https://checkout.stripe.com https://nowpayments.io https://*.nowpayments.io";
        $formAction = app()->isProduction()
            ? "'self' {$appUrl} {$payment}"
            : "* 'unsafe-inline'";

        return "default-src 'self' data: blob: https: http: 'unsafe-inline' 'unsafe-eval'; "
             . "connect-src 'self' https: http: ws: wss:; "
             . "frame-ancestors 'self'; "
             . "base-uri 'self'; "
             . "form-action {$formAction}";
    }
}
