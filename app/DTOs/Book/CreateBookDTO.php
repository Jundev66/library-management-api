<?php

declare(strict_types=1);

namespace App\DTOs\Book;

use InvalidArgumentException;

final readonly class CreateBookDTO
{
    public string $title;
    public string $author;
    public ?string $description;
    public int $publishedYear;
    public int $categoryId;

    public function __construct(
        string $title,
        string $author,
        ?string $description,
        int $publishedYear,
        int $categoryId,
    ) {
        $trimmedTitle = trim($title);
        $trimmedAuthor = trim($author);

        if (mb_strlen($trimmedTitle) < 3) {
            throw new InvalidArgumentException('Title must have at least 3 characters.');
        }

        if ('' === $trimmedAuthor) {
            throw new InvalidArgumentException('Author cannot be empty.');
        }

        if ($publishedYear < 1000 || $publishedYear > ((int) date('Y') + 1)) {
            throw new InvalidArgumentException('Invalid published year.');
        }

        if ($categoryId <= 0) {
            throw new InvalidArgumentException('Category ID must be a positive integer.');
        }

        $this->title = $trimmedTitle;
        $this->author = $trimmedAuthor;
        $this->description = null !== $description ? trim($description) : null;
        $this->publishedYear = $publishedYear;
        $this->categoryId = $categoryId;
    }

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'author' => $this->author,
            'description' => $this->description,
            'published_year' => $this->publishedYear,
            'category_id' => $this->categoryId,
        ];
    }
}
