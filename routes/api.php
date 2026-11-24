<?php

declare(strict_types=1);

use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\CategoryController;
use Illuminate\Support\Facades\Route;

Route::get('/health', fn () => response()->json(['status' => 'healthy']));

$rateLimit = app()->isProduction() ? '60,1' : '1000,1';

Route::middleware(["throttle:{$rateLimit}"])->group(function (): void {
    Route::apiResource('categories', CategoryController::class)
        ->only(['index', 'store', 'destroy'])
        ->whereNumber('category');

    Route::apiResource('books', BookController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->whereNumber('book');
});
