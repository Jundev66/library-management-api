<?php

declare(strict_types=1);

namespace Tests\Unit\Category\Create;

use App\Actions\Category\CreateCategoryAction;
use App\DTOs\Category\CreateCategoryDTO;
use App\Exceptions\CategoryAlreadyExistsException;
use App\Models\Category\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CreateCategoryActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_category_successfully(): void
    {
        $dto = new CreateCategoryDTO(name: 'Mitología');

        $action = new CreateCategoryAction();
        $category = $action->execute($dto);

        $this->assertSame('Mitología', $category->name);
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Mitología',
        ]);
    }

    public function test_throws_category_already_exists_exception_on_duplicate_name(): void
    {
        Category::create(['name' => 'Mitología']);

        $dto = new CreateCategoryDTO(name: 'Mitología');

        $this->expectException(CategoryAlreadyExistsException::class);

        $action = new CreateCategoryAction();
        $action->execute($dto);
    }
}
