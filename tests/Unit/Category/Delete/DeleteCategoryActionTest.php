<?php

declare(strict_types=1);

namespace Tests\Unit\Category\Delete;

use App\Actions\Category\DeleteCategoryAction;
use App\Exceptions\CategoryHasBooksException;
use App\Exceptions\CategoryNotFoundException;
use App\Models\Book\Book;
use App\Models\Category\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class DeleteCategoryActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_delete_category_without_books_successfully(): void
    {
        $category = Category::create(['name' => 'Música']);

        $action = new DeleteCategoryAction();
        $action->execute($category->id);

        $this->assertDatabaseMissing('categories', [
            'id' => $category->id,
        ]);
    }

    public function test_throws_category_not_found_exception_when_id_is_zero_or_negative(): void
    {
        $this->expectException(CategoryNotFoundException::class);

        $action = new DeleteCategoryAction();
        $action->execute(0);
    }

    public function test_throws_category_not_found_exception_when_category_does_not_exist(): void
    {
        $this->expectException(CategoryNotFoundException::class);

        $action = new DeleteCategoryAction();
        $action->execute(99999);
    }

    public function test_throws_category_has_books_exception_when_category_has_associated_books(): void
    {
        $category = Category::create(['name' => 'Literatura Clásica']);

        Book::create([
            'title' => 'La Ilíada',
            'author' => 'Homero',
            'published_year' => 800,
            'category_id' => $category->id,
        ]);

        $this->expectException(CategoryHasBooksException::class);

        $action = new DeleteCategoryAction();
        $action->execute($category->id);

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
        ]);
    }
}
