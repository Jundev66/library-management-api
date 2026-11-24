<?php

declare(strict_types=1);

namespace Tests\Feature\Book\Create;

use App\Models\Category\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CreateBookTest extends TestCase
{
    use RefreshDatabase;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->category = Category::create(['name' => 'Ficción']);
    }

    public function test_can_create_a_book_with_valid_data(): void
    {
        $payload = [
            'title' => 'El Aleph',
            'author' => 'Jorge Luis Borges',
            'description' => 'Colección de cuentos de literatura fantástica.',
            'published_year' => 1949,
            'category_id' => $this->category->id,
        ];

        $response = $this->postJson('/api/books', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.title', 'El Aleph')
            ->assertJsonPath('data.author', 'Jorge Luis Borges')
            ->assertJsonPath('data.published_year', 1949)
            ->assertJsonPath('data.category.id', $this->category->id);

        $this->assertDatabaseHas('books', [
            'title' => 'El Aleph',
            'author' => 'Jorge Luis Borges',
            'published_year' => 1949,
            'category_id' => $this->category->id,
        ]);
    }

    public function test_cannot_create_book_with_title_shorter_than_3_characters(): void
    {
        $payload = [
            'title' => 'Ab',
            'author' => 'Autor',
            'published_year' => 2020,
            'category_id' => $this->category->id,
        ];

        $response = $this->postJson('/api/books', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    public function test_cannot_create_book_with_invalid_category_id(): void
    {
        $payload = [
            'title' => 'Libro Sin Categoria',
            'author' => 'Autor',
            'published_year' => 2020,
            'category_id' => 99999,
        ];

        $response = $this->postJson('/api/books', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['category_id']);
    }

    public function test_cannot_create_book_missing_required_fields(): void
    {
        $response = $this->postJson('/api/books', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'author', 'published_year', 'category_id']);
    }
}
