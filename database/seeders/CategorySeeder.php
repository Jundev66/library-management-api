<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category\Category;
use Illuminate\Database\Seeder;

final class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Ficción',
            'Ciencia Ficción',
            'Historia',
            'Tecnología',
            'Filosofía',
        ];

        foreach ($categories as $name) {
            Category::firstOrCreate(['name' => $name]);
        }
    }
}
