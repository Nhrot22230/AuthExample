<?php

use App\Http\Middleware\JWTMiddleware;
use App\Http\Middleware\LogMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        commands: __DIR__ . '/../routes/console.php',
        using: function () {
            Route::prefix('api')
                ->group(base_path('routes/auth/authentication.php'));

            $dir = new RecursiveDirectoryIterator(base_path('routes/api'));
            $iterator = new RecursiveIteratorIterator($dir);
            $files = new RegexIterator($iterator, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH);

            foreach ($files as $file) {
                Route::middleware(JWTMiddleware::class)
                    ->prefix('api/v1')
                    ->group($file[0]);
            }

            Route::middleware(LogMiddleware::class)
                ->group(base_path('routes/web.php'));
        }
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
        $middleware->append([
            LogMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
