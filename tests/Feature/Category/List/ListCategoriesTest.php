<?php

declare(strict_types=1);

namespace Tests\Feature\Category\List;

use App\Models\Category\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ListCategoriesTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_all_categories(): void
    {
        Category::create(['name' => 'Arte']);
        Category::create(['name' => 'Biografía']);

        $response = $this->getJson('/api/categories');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.name', 'Arte')
            ->assertJsonPath('data.1.name', 'Biografía');
    }
}
