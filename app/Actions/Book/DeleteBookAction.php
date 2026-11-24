<?php

declare(strict_types=1);

namespace App\Actions\Book;

use App\Exceptions\BookNotFoundException;
use App\Models\Book\Book;
use Illuminate\Support\Facades\DB;

final class DeleteBookAction
{
    public function execute(int $id): void
    {
        if ($id <= 0) {
            throw new BookNotFoundException($id);
        }

        DB::transaction(function () use ($id): void {
            $book = Book::where('id', $id)->lockForUpdate()->first();

            if (null === $book) {
                throw new BookNotFoundException($id);
            }

            $book->delete();
        });
    }
}
