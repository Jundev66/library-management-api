<?php

declare(strict_types=1);

namespace Tests\Feature\Category\Delete;

use App\Models\Book\Book;
use App\Models\Category\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class DeleteCategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_delete_category_without_associated_books(): void
    {
        $category = Category::create(['name' => 'Vacía']);

        $response = $this->deleteJson("/api/categories/{$category->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('categories', [
            'id' => $category->id,
        ]);
    }

    public function test_cannot_delete_category_with_associated_books_returns_409(): void
    {
        $category = Category::create(['name' => 'Con Libros']);

        Book::create([
            'title' => 'Libro de Prueba',
            'author' => 'Autor de Prueba',
            'published_year' => 2024,
            'category_id' => $category->id,
        ]);

        $response = $this->deleteJson("/api/categories/{$category->id}");

        $response->assertStatus(409)
            ->assertJson([
                'message' => 'Cannot delete category because it has associated books.',
            ]);

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
        ]);
    }

    public function test_cannot_delete_non_existing_category_returns_404(): void
    {
        $response = $this->deleteJson('/api/categories/99999');

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Resource not found.',
            ]);
    }
}
