<?php

declare(strict_types=1);

namespace Tests\Unit\Category\List;

use App\Actions\Category\ListCategoriesAction;
use App\Models\Category\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ListCategoriesActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_all_categories_ordered_by_name(): void
    {
        Category::create(['name' => 'Zoológico']);
        Category::create(['name' => 'Astronomía']);
        Category::create(['name' => 'Botánica']);

        $action = new ListCategoriesAction();
        $categories = $action->execute();

        $this->assertCount(3, $categories);
        $this->assertSame('Astronomía', $categories->get(0)->name);
        $this->assertSame('Botánica', $categories->get(1)->name);
        $this->assertSame('Zoológico', $categories->get(2)->name);
    }
}
