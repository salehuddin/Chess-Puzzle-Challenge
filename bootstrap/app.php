<?php

use App\Http\Middleware\EnsureUserIsStaff;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

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
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Throwable $e, \Illuminate\Http\Request $request) {
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

            \Log::error('Unhandled exception: '.$e->getMessage(), [
                'url' => $request->fullUrl(),
                'class' => get_class($e),
                'file' => $e->getFile().':'.$e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return null;
        });
    })->create();
