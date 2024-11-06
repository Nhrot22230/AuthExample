<?php

use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

Route::get("/", function (): JsonResponse {
    return new JsonResponse([
        'message' => 'Welcome to the API',
        'version' => '1.0.0',
    ]);
});

