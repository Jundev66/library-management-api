<?php

declare(strict_types=1);

namespace App\Http\Requests\Category;

use App\DTOs\Category\CreateCategoryDTO;
use Illuminate\Foundation\Http\FormRequest;

final class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:categories,name'],
        ];
    }

    public function toDTO(): CreateCategoryDTO
    {
        return new CreateCategoryDTO(
            name: (string) $this->validated('name')
        );
    }
}
