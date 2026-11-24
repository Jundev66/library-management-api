<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

final class CategoryNotFoundException extends Exception
{
    public function __construct(int $id)
    {
        parent::__construct("Category with ID {$id} not found.");
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'message' => 'Resource not found.',
        ], 404);
    }
}
