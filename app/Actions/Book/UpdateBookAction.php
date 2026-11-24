<?php

declare(strict_types=1);

namespace App\Actions\Book;

use App\DTOs\Book\UpdateBookDTO;
use App\Exceptions\BookNotFoundException;
use App\Exceptions\CategoryNotFoundException;
use App\Models\Book\Book;
use App\Models\Category\Category;
use Illuminate\Support\Facades\DB;

final class UpdateBookAction
{
    public function execute(int $id, UpdateBookDTO $dto): Book
    {
        if ($id <= 0) {
            throw new BookNotFoundException($id);
        }

        return DB::transaction(function () use ($id, $dto): Book {
            $book = Book::where('id', $id)->lockForUpdate()->first();

            if (null === $book) {
                throw new BookNotFoundException($id);
            }

            if (! Category::where('id', $dto->categoryId)->exists()) {
                throw new CategoryNotFoundException($dto->categoryId);
            }

            $book->update($dto->toArray());

            return $book->load('category');
        });
    }
}
