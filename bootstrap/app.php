<?php

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
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);
        $middleware->web(append: [
            \App\Http\Middleware\RestrictMobileOperatorMiddleware::class,
        ]);
        $middleware->redirectGuestsTo(function (Request $request) {
            if (
                $request->is('produksi/packaging/mobile*') ||
                $request->is('mobile/*') ||
                $request->is('qc/inbound/create*') ||
                $request->is('qc/inbound/pengujian-2*') ||
                $request->is('qc/login*') ||
                ($request->is('qc/*') && $request->get('view') === 'mobile')
            ) {
                return route('mobile.login');
            }
            return route('login');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Throwable $e, Request $request) {
            $is419 = ($e instanceof \Illuminate\Session\TokenMismatchException)
                || ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpException && $e->getStatusCode() === 419);

            if ($is419) {
                if (
                    $request->is('mobile/*') ||
                    $request->is('produksi/packaging/mobile*') ||
                    $request->is('qc/inbound/create*') ||
                    $request->is('qc/inbound/pengujian-2*') ||
                    $request->is('qc/login*') ||
                    ($request->is('qc/*') && $request->get('view') === 'mobile')
                ) {
                    return redirect()->route('mobile.login')
                        ->withInput($request->except(['password', '_token']))
                        ->withErrors(['email' => 'Sesi formulir kedaluwarsa. Halaman telah disegarkan otomatis, silakan coba lagi.']);
                }

                if ($request->is('login') || $request->is('logout')) {
                    return redirect()->route('login')
                        ->withInput($request->except(['password', '_token']))
                        ->withErrors(['email' => 'Sesi login kedaluwarsa. Silakan masuk kembali.']);
                }

                return redirect()->back()
                    ->withInput($request->except(['password', '_token']))
                    ->with('error', 'Sesi Anda telah kedaluwarsa karena tidak aktif. Halaman telah disegarkan, silakan ulangi tindakan Anda.');
            }
        });

        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
