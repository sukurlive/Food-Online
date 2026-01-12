<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Role & permission
        $middleware->alias([
            'authenticate'       => \App\Http\Middleware\Authenticate::class,
            'extend.token'       => \App\Http\Middleware\ExtendTokenExpiration::class,
            'role'               => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission'         => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Unauthenticated (API)
        // 1. JIKA BELUM LOGIN (Semua Role) -> 401
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'status'  => 'Gagal',
                    'message' => 'Tidak terautentikasi.'
                ], 401);
            }
        });

        // 2. JIKA SUDAH LOGIN TAPI ROLE SALAH -> 403
        $exceptions->render(function (\Spatie\Permission\Exceptions\UnauthorizedException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'status'  => 'Gagal',
                    'message' => 'Anda tidak diijinkan mengakses.'
                ], 403);
            }
        });

        // Penanganan Error Global (400)
        $exceptions->render(function (\Exception $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'status'  => 'Gagal',
                    'message' => $e->getMessage(),
                ], 400);
            }
        });

        // Data Tidak Ditemukan (404) 
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status'  => 'Gagal',
                    'message' => 'Data atau Endpoint tidak ditemukan.',
                ], 404);
            }
        });
    })->create();
