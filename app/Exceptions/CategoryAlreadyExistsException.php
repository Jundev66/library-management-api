<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

final class CategoryAlreadyExistsException extends Exception
{
    public function __construct(string $name)
    {
        parent::__construct("Category '{$name}' already exists.");
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'message' => 'The given data was invalid.',
            'errors' => [
                'name' => ['The category name has already been taken.'],
            ],
        ], 422);
    }
}
