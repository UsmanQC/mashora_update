<?php

use App\Http\Middleware\SetLocaleFromSession;
use Filament\Facades\Filament;
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
        $middleware->web(append: [
            SetLocaleFromSession::class,
        ]);

        $middleware->redirectGuestsTo(fn (): string => '/');

        $middleware->redirectUsersTo(function (Request $request): string {
            if (
                $request->is('doctor/login')
                || $request->is('doctor/register')
                || str_starts_with($request->path(), 'doctor/password-reset')
            ) {
                return Filament::getPanel('doctor')->getUrl() ?? url('/doctor');
            }

            return '/';
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
