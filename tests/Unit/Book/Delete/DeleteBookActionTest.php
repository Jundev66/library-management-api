<?php

declare(strict_types=1);

namespace Tests\Unit\Book\Delete;

use App\Actions\Book\DeleteBookAction;
use App\Exceptions\BookNotFoundException;
use App\Models\Book\Book;
use App\Models\Category\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class DeleteBookActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_delete_book_successfully(): void
    {
        $category = Category::create(['name' => 'Historia']);

        $book = Book::create([
            'title' => 'Historia Universal',
            'author' => 'Autor Histórico',
            'published_year' => 1999,
            'category_id' => $category->id,
        ]);

        $action = new DeleteBookAction();
        $action->execute($book->id);

        $this->assertDatabaseMissing('books', [
            'id' => $book->id,
        ]);
    }

    public function test_throws_book_not_found_exception_when_id_is_zero_or_negative(): void
    {
        $this->expectException(BookNotFoundException::class);

        $action = new DeleteBookAction();
        $action->execute(-5);
    }

    public function test_throws_book_not_found_exception_when_book_does_not_exist(): void
    {
        $this->expectException(BookNotFoundException::class);

        $action = new DeleteBookAction();
        $action->execute(99999);
    }
}
