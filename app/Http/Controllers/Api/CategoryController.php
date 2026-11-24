<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Category\CreateCategoryAction;
use App\Actions\Category\DeleteCategoryAction;
use App\Actions\Category\ListCategoriesAction;
use App\Exceptions\CategoryNotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Resources\CategoryResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

final class CategoryController extends Controller
{
    public function index(ListCategoriesAction $action): AnonymousResourceCollection
    {
        return CategoryResource::collection($action->execute());
    }

    public function store(StoreCategoryRequest $request, CreateCategoryAction $action): JsonResponse
    {
        $category = $action->execute($request->toDTO());

        return (new CategoryResource($category))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function destroy(string|int $id, DeleteCategoryAction $action): Response
    {
        $numericId = filter_var($id, FILTER_VALIDATE_INT);

        if (false === $numericId || $numericId <= 0) {
            throw new CategoryNotFoundException(0);
        }

        $action->execute($numericId);

        return response()->noContent();
    }
}
