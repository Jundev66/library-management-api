<?php

declare(strict_types=1);

namespace App\Http\Requests\Book;

use Illuminate\Foundation\Http\FormRequest;

final class ListBooksRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
