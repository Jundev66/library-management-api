<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('books')) {
            Schema::create('books', function (Blueprint $table): void {
                $table->id();
                $table->string('title')->index();
                $table->string('author')->index();
                $table->text('description')->nullable();
                $table->integer('published_year')->index();
                $table->foreignId('category_id')->constrained('categories')->restrictOnDelete();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('books')) {
            Schema::dropIfExists('books');
        }
    }
};
