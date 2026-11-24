<?php

declare(strict_types=1);

namespace App\DTOs\Category;

use InvalidArgumentException;

final readonly class CreateCategoryDTO
{
    public string $name;

    public function __construct(string $name)
    {
        $trimmedName = trim($name);

        if ('' === $trimmedName) {
            throw new InvalidArgumentException('Category name cannot be empty.');
        }

        $this->name = $trimmedName;
    }
}
