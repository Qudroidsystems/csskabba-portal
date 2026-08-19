<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Auth;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'cbt/submit',
            'webhook/*',
            'payment/callback',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Handle 419 TokenMismatchException - Redirect to login with toast
        $exceptions->render(function (TokenMismatchException $e, $request) {
            // Store the URL they were trying to access
            $intendedUrl = $request->fullUrl();

            // For AJAX/API requests
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Session expired. Please login again.',
                    'redirect' => route('login', [], false)
                ], 419);
            }

            // Log out the user if they were logged in
            if (Auth::check()) {
                Auth::logout();
            }

            // Clear and regenerate session
            $request->session()->flush();
            $request->session()->regenerate();

            // Redirect to login with flash data
            return redirect()->route('login')
                ->with('session_expired', true)
                ->with('error', 'Your session has expired. Please login again.')
                ->with('intended', $intendedUrl);
        });
    })->create();