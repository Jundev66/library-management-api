<?php

declare(strict_types=1);

namespace App\Actions\Category;

use App\DTOs\Category\CreateCategoryDTO;
use App\Exceptions\CategoryAlreadyExistsException;
use App\Models\Category\Category;
use Illuminate\Support\Facades\DB;

final class CreateCategoryAction
{
    public function execute(CreateCategoryDTO $dto): Category
    {
        return DB::transaction(function () use ($dto): Category {
            if (Category::where('name', $dto->name)->exists()) {
                throw new CategoryAlreadyExistsException($dto->name);
            }

            return Category::create([
                'name' => $dto->name,
            ]);
        });
    }
}
