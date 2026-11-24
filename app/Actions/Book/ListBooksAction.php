<?php

declare(strict_types=1);

namespace App\Actions\Book;

use App\Models\Book\Book;
use App\Models\Book\Filters\BookFilter;
use Illuminate\Database\Eloquent\Collection;

final class ListBooksAction
{
    public function execute(BookFilter $filter): Collection
    {
        return Book::with('category')
            ->filter($filter)
            ->orderBy('id', 'desc')
            ->get();
    }
}
