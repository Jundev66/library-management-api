<?php

declare(strict_types=1);

namespace Tests\Feature\Book\Delete;

use App\Models\Book\Book;
use App\Models\Category\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class DeleteBookTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_delete_an_existing_book(): void
    {
        $category = Category::create(['name' => 'Ficción']);

        $book = Book::create([
            'title' => 'Libro Para Eliminar',
            'author' => 'Autor',
            'published_year' => 2020,
            'category_id' => $category->id,
        ]);

        $response = $this->deleteJson("/api/books/{$book->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('books', [
            'id' => $book->id,
        ]);
    }

    public function test_cannot_delete_non_existing_book_returns_404(): void
    {
        $response = $this->deleteJson('/api/books/99999');

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Resource not found.',
            ]);
    }
}
