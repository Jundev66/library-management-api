<?php

declare(strict_types=1);

namespace Tests\Unit\Book\List;

use App\Actions\Book\ListBooksAction;
use App\Models\Book\Book;
use App\Models\Book\Filters\BookFilter;
use App\Models\Category\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

final class ListBooksActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_all_books_with_eager_loaded_category(): void
    {
        $catA = Category::create(['name' => 'Filosofía']);
        $catB = Category::create(['name' => 'Tecnología']);

        $bookA = Book::create([
            'title' => 'Ética',
            'author' => 'Baruch Spinoza',
            'published_year' => 1677,
            'category_id' => $catA->id,
        ]);

        $bookB = Book::create([
            'title' => 'Clean Code',
            'author' => 'Robert C. Martin',
            'published_year' => 2008,
            'category_id' => $catB->id,
        ]);

        $action = new ListBooksAction();
        $books = $action->execute(new BookFilter(new Request()));

        $this->assertCount(2, $books);
        $this->assertTrue($books->first()->relationLoaded('category'));
        $this->assertSame($bookB->id, $books->first()->id);
    }

    public function test_can_filter_books_by_category(): void
    {
        $catA = Category::create(['name' => 'Filosofía']);
        $catB = Category::create(['name' => 'Tecnología']);

        Book::create([
            'title' => 'Ética',
            'author' => 'Baruch Spinoza',
            'published_year' => 1677,
            'category_id' => $catA->id,
        ]);

        Book::create([
            'title' => 'Clean Code',
            'author' => 'Robert C. Martin',
            'published_year' => 2008,
            'category_id' => $catB->id,
        ]);

        $action = new ListBooksAction();
        $filter = new BookFilter(new Request(['category' => (string) $catA->id]));
        $books = $action->execute($filter);

        $this->assertCount(1, $books);
        $this->assertSame('Ética', $books->first()->title);
        $this->assertSame('Filosofía', $books->first()->category->name);
    }

    public function test_returns_empty_collection_for_non_existent_category(): void
    {
        $catA = Category::create(['name' => 'Filosofía']);

        Book::create([
            'title' => 'Ética',
            'author' => 'Baruch Spinoza',
            'published_year' => 1677,
            'category_id' => $catA->id,
        ]);

        $action = new ListBooksAction();
        $filter = new BookFilter(new Request(['category' => '99999']));
        $books = $action->execute($filter);

        $this->assertCount(0, $books);
    }
}
