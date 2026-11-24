<?php

declare(strict_types=1);

namespace Tests\Feature\Book\Update;

use App\Models\Book\Book;
use App\Models\Category\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class UpdateBookTest extends TestCase
{
    use RefreshDatabase;

    private Category $categoryA;
    private Category $categoryB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->categoryA = Category::create(['name' => 'Ficción']);
        $this->categoryB = Category::create(['name' => 'Ciencia']);
    }

    public function test_can_update_an_existing_book(): void
    {
        $book = Book::create([
            'title' => 'Titulo Original',
            'author' => 'Autor Original',
            'description' => 'Descripcion Original',
            'published_year' => 2010,
            'category_id' => $this->categoryA->id,
        ]);

        $updatePayload = [
            'title' => 'Titulo Actualizado',
            'author' => 'Autor Actualizado',
            'description' => 'Nueva descripcion',
            'published_year' => 2015,
            'category_id' => $this->categoryB->id,
        ];

        $response = $this->putJson("/api/books/{$book->id}", $updatePayload);

        $response->assertStatus(200)
            ->assertJsonPath('data.title', 'Titulo Actualizado')
            ->assertJsonPath('data.author', 'Autor Actualizado')
            ->assertJsonPath('data.published_year', 2015)
            ->assertJsonPath('data.category.id', $this->categoryB->id);

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => 'Titulo Actualizado',
            'author' => 'Autor Actualizado',
            'published_year' => 2015,
            'category_id' => $this->categoryB->id,
        ]);
    }

    public function test_cannot_update_non_existing_book_returns_404(): void
    {
        $response = $this->putJson('/api/books/99999', [
            'title' => 'Cualquier Titulo',
            'author' => 'Cualquier Autor',
            'published_year' => 2020,
            'category_id' => $this->categoryA->id,
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Resource not found.',
            ]);
    }

    public function test_cannot_update_with_invalid_payload(): void
    {
        $book = Book::create([
            'title' => 'Libro Valido',
            'author' => 'Autor Valido',
            'published_year' => 2020,
            'category_id' => $this->categoryA->id,
        ]);

        $response = $this->putJson("/api/books/{$book->id}", [
            'title' => 'X',
            'author' => '',
            'published_year' => 'invalid-year',
            'category_id' => 99999,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'author', 'published_year', 'category_id']);
    }
}
