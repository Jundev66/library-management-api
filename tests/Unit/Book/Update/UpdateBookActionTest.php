<?php

declare(strict_types=1);

namespace Tests\Unit\Book\Update;

use App\Actions\Book\UpdateBookAction;
use App\DTOs\Book\UpdateBookDTO;
use App\Exceptions\BookNotFoundException;
use App\Exceptions\CategoryNotFoundException;
use App\Models\Book\Book;
use App\Models\Category\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class UpdateBookActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_update_book_successfully(): void
    {
        $initialCategory = Category::create(['name' => 'Ficción']);
        $newCategory = Category::create(['name' => 'Ciencia']);

        $book = Book::create([
            'title' => 'Ficciones',
            'author' => 'J.L. Borges',
            'description' => 'Original',
            'published_year' => 1944,
            'category_id' => $initialCategory->id,
        ]);

        $dto = new UpdateBookDTO(
            title: 'Ficciones Edición Especial',
            author: 'Jorge Luis Borges',
            description: 'Edición crítica anotada',
            publishedYear: 1956,
            categoryId: $newCategory->id
        );

        $action = new UpdateBookAction();
        $updatedBook = $action->execute($book->id, $dto);

        $this->assertSame('Ficciones Edición Especial', $updatedBook->title);
        $this->assertSame('Jorge Luis Borges', $updatedBook->author);
        $this->assertSame('Edición crítica anotada', $updatedBook->description);
        $this->assertSame(1956, $updatedBook->published_year);
        $this->assertSame($newCategory->id, $updatedBook->category_id);
        $this->assertTrue($updatedBook->relationLoaded('category'));
        $this->assertSame('Ciencia', $updatedBook->category->name);

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => 'Ficciones Edición Especial',
            'author' => 'Jorge Luis Borges',
            'published_year' => 1956,
            'category_id' => $newCategory->id,
        ]);
    }

    public function test_throws_book_not_found_exception_when_id_is_zero_or_negative(): void
    {
        $category = Category::create(['name' => 'Ficción']);

        $dto = new UpdateBookDTO(
            title: 'Libro Invalido',
            author: 'Autor',
            description: null,
            publishedYear: 2020,
            categoryId: $category->id
        );

        $this->expectException(BookNotFoundException::class);

        $action = new UpdateBookAction();
        $action->execute(0, $dto);
    }

    public function test_throws_book_not_found_exception_when_book_does_not_exist(): void
    {
        $category = Category::create(['name' => 'Ficción']);

        $dto = new UpdateBookDTO(
            title: 'Libro Inexistente',
            author: 'Autor',
            description: null,
            publishedYear: 2020,
            categoryId: $category->id
        );

        $this->expectException(BookNotFoundException::class);

        $action = new UpdateBookAction();
        $action->execute(99999, $dto);
    }

    public function test_throws_category_not_found_exception_when_new_category_does_not_exist(): void
    {
        $category = Category::create(['name' => 'Ficción']);

        $book = Book::create([
            'title' => 'Libro Inicial',
            'author' => 'Autor',
            'published_year' => 2020,
            'category_id' => $category->id,
        ]);

        $dto = new UpdateBookDTO(
            title: 'Libro Actualizado',
            author: 'Autor',
            description: null,
            publishedYear: 2020,
            categoryId: 99999
        );

        $this->expectException(CategoryNotFoundException::class);

        $action = new UpdateBookAction();
        $action->execute($book->id, $dto);
    }
}
