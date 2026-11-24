<?php

declare(strict_types=1);

namespace App\Models\Book;

use App\Models\Book\Filters\BookFilter;
use App\Models\Category\Category;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Book extends Model
{
    use HasFactory;

    protected $table = 'books';

    protected $fillable = [
        'title',
        'author',
        'description',
        'published_year',
        'category_id',
    ];

    protected function casts(): array
    {
        return [
            'title' => 'string',
            'author' => 'string',
            'description' => 'string',
            'published_year' => 'integer',
            'category_id' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function scopeFilter(Builder $query, BookFilter $filter): Builder
    {
        return $filter->apply($query);
    }
}
