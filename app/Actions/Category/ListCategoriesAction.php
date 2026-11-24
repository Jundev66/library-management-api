<?php

declare(strict_types=1);

namespace App\Actions\Category;

use App\Models\Category\Category;
use Illuminate\Database\Eloquent\Collection;

final class ListCategoriesAction
{
    public function execute(): Collection
    {
        return Category::orderBy('name', 'asc')->get();
    }
}
