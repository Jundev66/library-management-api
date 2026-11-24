<?php

declare(strict_types=1);

namespace App\Actions\Category;

use App\Exceptions\CategoryHasBooksException;
use App\Exceptions\CategoryNotFoundException;
use App\Models\Category\Category;
use Illuminate\Support\Facades\DB;

final class DeleteCategoryAction
{
    public function execute(int $id): void
    {
        if ($id <= 0) {
            throw new CategoryNotFoundException($id);
        }

        DB::transaction(function () use ($id): void {
            $category = Category::where('id', $id)->lockForUpdate()->first();

            if (null === $category) {
                throw new CategoryNotFoundException($id);
            }

            if ($category->books()->exists()) {
                throw new CategoryHasBooksException();
            }

            $category->delete();
        });
    }
}
