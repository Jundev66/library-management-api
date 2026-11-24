<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Book\CreateBookAction;
use App\Actions\Book\DeleteBookAction;
use App\Actions\Book\ListBooksAction;
use App\Actions\Book\UpdateBookAction;
use App\Exceptions\BookNotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Book\ListBooksRequest;
use App\Http\Requests\Book\StoreBookRequest;
use App\Http\Requests\Book\UpdateBookRequest;
use App\Http\Resources\BookResource;
use App\Models\Book\Filters\BookFilter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

final class BookController extends Controller
{
    public function index(ListBooksRequest $request, BookFilter $filter, ListBooksAction $action): AnonymousResourceCollection
    {
        $books = $action->execute($filter);

        return BookResource::collection($books);
    }

    public function store(StoreBookRequest $request, CreateBookAction $action): JsonResponse
    {
        $book = $action->execute($request->toDTO());

        return (new BookResource($book))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function update(UpdateBookRequest $request, string|int $id, UpdateBookAction $action): BookResource
    {
        $numericId = filter_var($id, FILTER_VALIDATE_INT);

        if (false === $numericId || $numericId <= 0) {
            throw new BookNotFoundException(0);
        }

        $book = $action->execute($numericId, $request->toDTO());

        return new BookResource($book);
    }

    public function destroy(string|int $id, DeleteBookAction $action): Response
    {
        $numericId = filter_var($id, FILTER_VALIDATE_INT);

        if (false === $numericId || $numericId <= 0) {
            throw new BookNotFoundException(0);
        }

        $action->execute($numericId);

        return response()->noContent();
    }
}
