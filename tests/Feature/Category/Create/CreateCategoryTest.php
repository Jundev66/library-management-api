<?php

declare(strict_types=1);

namespace Tests\Feature\Category\Create;

use App\Models\Category\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CreateCategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_a_category_with_valid_name(): void
    {
        $response = $this->postJson('/api/categories', [
            'name' => 'Ciencia',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Ciencia')
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'created_at',
                    'updated_at',
                ],
            ]);

        $this->assertDatabaseHas('categories', [
            'name' => 'Ciencia',
        ]);
    }

    public function test_cannot_create_category_with_duplicate_name(): void
    {
        Category::create(['name' => 'Novela']);

        $response = $this->postJson('/api/categories', [
            'name' => 'Novela',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_cannot_create_category_with_empty_name(): void
    {
        $response = $this->postJson('/api/categories', [
            'name' => '',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }
}
