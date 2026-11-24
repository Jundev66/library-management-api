<?php

declare(strict_types=1);

namespace Tests\Feature\Book\List;

use App\Models\Book\Book;
use App\Models\Category\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class ListBooksTest extends TestCase
{
    use RefreshDatabase;

    private Category $fiction;
    private Category $science;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fiction = Category::create(['name' => 'Ficción']);
        $this->science = Category::create(['name' => 'Ciencia']);
    }

    public function test_can_list_all_books(): void
    {
        Book::create([
            'title' => 'Libro Uno',
            'author' => 'Autor Uno',
            'published_year' => 2020,
            'category_id' => $this->fiction->id,
        ]);

        Book::create([
            'title' => 'Libro Dos',
            'author' => 'Autor Dos',
            'published_year' => 2021,
            'category_id' => $this->science->id,
        ]);

        $response = $this->getJson('/api/books');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'author',
                        'description',
                        'published_year',
                        'category_id',
                        'category' => [
                            'id',
                            'name',
                        ],
                    ],
                ],
            ]);
    }

    public function test_can_filter_books_by_category(): void
    {
        Book::create([
            'title' => 'Libro Ficcion',
            'author' => 'Autor Ficcion',
            'published_year' => 2020,
            'category_id' => $this->fiction->id,
        ]);

        Book::create([
            'title' => 'Libro Ciencia',
            'author' => 'Autor Ciencia',
            'published_year' => 2021,
            'category_id' => $this->science->id,
        ]);

        $response = $this->getJson("/api/books?category={$this->fiction->id}");

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Libro Ficcion')
            ->assertJsonPath('data.0.category.name', 'Ficción');
    }

    public function test_filtering_by_non_existing_category_returns_empty_list(): void
    {
        Book::create([
            'title' => 'Libro Uno',
            'author' => 'Autor Uno',
            'published_year' => 2020,
            'category_id' => $this->fiction->id,
        ]);

        $response = $this->getJson('/api/books?category=99999');

        $response->assertStatus(200)
            ->assertJsonCount(0, 'data');
    }

    public function test_invalid_category_filter_parameter_fails_validation(): void
    {
        $response = $this->getJson('/api/books?category=not-an-integer');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['category']);
    }

    public function test_anti_n_plus_one_eager_loading_query_count(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            Book::create([
                'title' => "Libro Numero {$i}",
                'author' => "Autor {$i}",
                'published_year' => 2000 + $i,
                'category_id' => ($i % 2 === 0) ? $this->fiction->id : $this->science->id,
            ]);
        }

        DB::enableQueryLog();

        $response = $this->getJson('/api/books');

        $response->assertStatus(200)
            ->assertJsonCount(10, 'data');

        $queries = DB::getQueryLog();

        $this->assertCount(2, $queries);
    }
}
