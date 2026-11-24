<?php

declare(strict_types=1);

namespace App\Models\Category;

use App\Models\Book\Book;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Category extends Model
{
    use HasFactory;

    protected $table = 'categories';

    protected $fillable = [
        'name',
    ];

    protected function casts(): array
    {
        return [
            'name' => 'string',
        ];
    }

    public function books(): HasMany
    {
        return $this->hasMany(Book::class, 'category_id');
    }
}
