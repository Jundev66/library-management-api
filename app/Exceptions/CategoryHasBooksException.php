<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

final class CategoryHasBooksException extends Exception
{
    public function render(): JsonResponse
    {
        return response()->json([
            'message' => 'Cannot delete category because it has associated books.',
        ], 409);
    }
}
