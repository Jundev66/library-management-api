<?php

declare(strict_types=1);

namespace App\Actions\Book;

use App\DTOs\Book\CreateBookDTO;
use App\Exceptions\CategoryNotFoundException;
use App\Models\Book\Book;
use App\Models\Category\Category;
use Illuminate\Support\Facades\DB;

final class CreateBookAction
{
    public function execute(CreateBookDTO $dto): Book
    {
        return DB::transaction(function () use ($dto): Book {
            if (! Category::where('id', $dto->categoryId)->exists()) {
                throw new CategoryNotFoundException($dto->categoryId);
            }

            $book = Book::create($dto->toArray());

            return $book->load('category');
        });
    }
}
