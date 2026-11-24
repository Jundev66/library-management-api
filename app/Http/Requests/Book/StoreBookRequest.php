<?php

declare(strict_types=1);

namespace App\Http\Requests\Book;

use App\DTOs\Book\CreateBookDTO;
use Illuminate\Foundation\Http\FormRequest;

final class StoreBookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $maxYear = (int) date('Y') + 1;

        return [
            'title' => ['required', 'string', 'min:3', 'max:255'],
            'author' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'published_year' => ['required', 'integer', 'min:1000', "max:{$maxYear}"],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
        ];
    }

    public function toDTO(): CreateBookDTO
    {
        return new CreateBookDTO(
            title: (string) $this->validated('title'),
            author: (string) $this->validated('author'),
            description: $this->validated('description') ? (string) $this->validated('description') : null,
            publishedYear: (int) $this->validated('published_year'),
            categoryId: (int) $this->validated('category_id'),
        );
    }
}
