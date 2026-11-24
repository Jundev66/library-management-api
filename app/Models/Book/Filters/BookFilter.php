<?php

declare(strict_types=1);

namespace App\Models\Book\Filters;

use App\Models\Common\Filters\QueryFilter;
use Illuminate\Database\Eloquent\Builder;

final class BookFilter extends QueryFilter
{
    public function category(int|string|null $value): Builder
    {
        if (null === $value || '' === $value) {
            return $this->builder;
        }

        return $this->builder->where('category_id', (int) $value);
    }
}
