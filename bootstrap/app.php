<?php

use App\Http\Middleware\EnsureUserIsStaff;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withCommands([
        //
    ])
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'staff' => EnsureUserIsStaff::class,
        ]);
        $middleware->validateCsrfTokens(except: [
            'admin/editorjs/*',
        ]);
        // Behind Cloudflare -> Coolify/Traefik. Trust only those hops so X-Forwarded-* can't be spoofed.
        // Cloudflare ranges: https://www.cloudflare.com/ips (rarely change).
        $middleware->trustProxies(at: [
            '127.0.0.1', '::1', '10.0.0.0/8', '172.16.0.0/12', '192.168.0.0/16', 'fc00::/7',
            '173.245.48.0/20', '103.21.244.0/22', '103.22.200.0/22', '103.31.4.0/22',
            '141.101.64.0/18', '108.162.192.0/18', '190.93.240.0/20', '188.114.96.0/20',
            '197.234.240.0/22', '198.41.128.0/17', '162.158.0.0/15', '104.16.0.0/13',
            '104.24.0.0/14', '172.64.0.0/13', '131.0.72.0/22',
            '2400:cb00::/32', '2606:4700::/32', '2803:f800::/32', '2405:b500::/32',
            '2405:8100::/32', '2a06:98c0::/29', '2c0f:f248::/32',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->is('livewire/*/upload-file') || $request->is('livewire/upload-file')) {
                $payload = [
                    'message' => $e->getMessage(),
                    'class' => get_class($e),
                    'file' => $e->getFile().':'.$e->getLine(),
                ];
                if (config('app.debug')) {
                    $payload['trace'] = explode("\n", $e->getTraceAsString());
                }

                return response()->json($payload, 500);
            }

            return null;
        });
    })->create();
