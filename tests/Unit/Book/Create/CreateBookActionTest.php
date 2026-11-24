<?php

declare(strict_types=1);

namespace Tests\Unit\Book\Create;

use App\Actions\Book\CreateBookAction;
use App\DTOs\Book\CreateBookDTO;
use App\Exceptions\CategoryNotFoundException;
use App\Models\Category\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CreateBookActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_book_successfully(): void
    {
        $category = Category::create(['name' => 'Ficción']);

        $dto = new CreateBookDTO(
            title: 'Rayuela',
            author: 'Julio Cortázar',
            description: 'Contranovela.',
            publishedYear: 1963,
            categoryId: $category->id
        );

        $action = new CreateBookAction();
        $book = $action->execute($dto);

        $this->assertSame('Rayuela', $book->title);
        $this->assertSame('Julio Cortázar', $book->author);
        $this->assertSame('Contranovela.', $book->description);
        $this->assertSame(1963, $book->published_year);
        $this->assertSame($category->id, $book->category_id);
        $this->assertTrue($book->relationLoaded('category'));
        $this->assertSame('Ficción', $book->category->name);

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => 'Rayuela',
            'author' => 'Julio Cortázar',
            'published_year' => 1963,
            'category_id' => $category->id,
        ]);
    }

    public function test_throws_category_not_found_exception_when_category_id_does_not_exist(): void
    {
        $dto = new CreateBookDTO(
            title: 'Libro Huérfano',
            author: 'Autor Anónimo',
            description: null,
            publishedYear: 2020,
            categoryId: 99999
        );

        $this->expectException(CategoryNotFoundException::class);

        $action = new CreateBookAction();
        $action->execute($dto);
    }
}
