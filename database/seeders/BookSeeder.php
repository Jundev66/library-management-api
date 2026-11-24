<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Book\Book;
use App\Models\Category\Category;
use Illuminate\Database\Seeder;

final class BookSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::pluck('id', 'name');

        $books = [
            [
                'title' => 'Cien años de soledad',
                'author' => 'Gabriel García Márquez',
                'description' => 'La novela cumbre del realismo mágico sobre la familia Buendía en Macondo.',
                'published_year' => 1967,
                'category_id' => $categories['Ficción'] ?? 1,
            ],
            [
                'title' => 'Dune',
                'author' => 'Frank Herbert',
                'description' => 'Épica de ciencia ficción ambientada en el desértico planeta Arrakis.',
                'published_year' => 1965,
                'category_id' => $categories['Ciencia Ficción'] ?? 2,
            ],
            [
                'title' => 'Fundación',
                'author' => 'Isaac Asimov',
                'description' => 'Historia sobre la psicohistoria y la preservación del conocimiento humano.',
                'published_year' => 1951,
                'category_id' => $categories['Ciencia Ficción'] ?? 2,
            ],
            [
                'title' => 'Sapiens: De animales a dioses',
                'author' => 'Yuval Noah Harari',
                'description' => 'Breve historia de la humanidad desde la evolución arcaica.',
                'published_year' => 2011,
                'category_id' => $categories['Historia'] ?? 3,
            ],
            [
                'title' => 'Clean Code',
                'author' => 'Robert C. Martin',
                'description' => 'Manual de estilo y desarrollo ágil para ingenieros de software.',
                'published_year' => 2008,
                'category_id' => $categories['Tecnología'] ?? 4,
            ],
            [
                'title' => 'Refactoring',
                'author' => 'Martin Fowler',
                'description' => 'Técnicas probadas para mejorar el diseño del código existente.',
                'published_year' => 1999,
                'category_id' => $categories['Tecnología'] ?? 4,
            ],
            [
                'title' => 'Meditaciones',
                'author' => 'Marco Aurelio',
                'description' => 'Reflexiones estoicas personales del emperador romano.',
                'published_year' => 180,
                'category_id' => $categories['Filosofía'] ?? 5,
            ],
        ];

        foreach ($books as $book) {
            Book::firstOrCreate(
                ['title' => $book['title']],
                $book
            );
        }
    }
}
