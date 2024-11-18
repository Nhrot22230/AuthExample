<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $uploadMaxFileSize = env('UPLOAD_MAX_FILESIZE', '15M');
        $postMaxSize = env('POST_MAX_SIZE', '15M');
        $maxExecutionTime = env('MAX_EXECUTION_TIME', '300');
        $memoryLimit = env('MEMORY_LIMIT', '256M');

        // Validaciones básicas
        if (is_numeric($maxExecutionTime) && $maxExecutionTime > 0) {
            ini_set('max_execution_time', $maxExecutionTime);
        }

        if (preg_match('/^\d+M$/', $uploadMaxFileSize)) {
            ini_set('upload_max_filesize', $uploadMaxFileSize);
        }

        if (preg_match('/^\d+M$/', $postMaxSize)) {
            ini_set('post_max_size', $postMaxSize);
        }

        if (preg_match('/^\d+M$/', $memoryLimit)) {
            ini_set('memory_limit', $memoryLimit);
        }
    }
}
