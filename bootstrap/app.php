<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Trust Heroku's load balancer so HTTPS is detected correctly.
        $middleware->trustProxies(at: '*');

        // Exclude external webhooks from CSRF — they're authenticated via HMAC signature.
        $middleware->validateCsrfTokens(except: [
            'webhooks/*',
        ]);
        // Redirect authenticated users away from guest-only pages to their dashboard.
        $middleware->redirectUsersTo(function (\Illuminate\Http\Request $request) {
            $user = $request->user();

            if (! $user) {
                return route('home');
            }

            if ($user->role === 'citizen' && ! $user->hasCompletedCitizenProfile()) {
                return route('citizen.profile');
            }

            return match ($user->role) {
                'admin' => route('admin.dashboard'),
                'office_user' => route('office.dashboard'),
                default => route('citizen.dashboard'),
            };
        });

        // Role-based access control alias.
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'citizen.profile.complete' => \App\Http\Middleware\EnsureCitizenProfileComplete::class,
        ]);

        // Security headers on all web responses.
        $middleware->web(append: [
            \App\Http\Middleware\SecurityHeadersMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Render custom error views.
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, $request) {
            $code = $e->getStatusCode();

            if (view()->exists("errors.{$code}")) {
                return response()->view("errors.{$code}", [], $code);
            }
        });
    })->create();
