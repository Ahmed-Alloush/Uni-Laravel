<?php
namespace App\Http\Controllers;

use App\Http\Requests\Category\CreateCategoryRequest ;
use App\Http\Requests\Category\UpdateCategoryRequest ;
use App\Models\Category;

use Illuminate\Http\JsonResponse;
use Illuminate\Database\QueryException;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories.
     */
    public function index(): JsonResponse
    {
        try {
            $categories = Category::all();

            return response()->json([
                'success' => true,
                'data' => $categories,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch categories.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created category in storage.
     */
    public function store(CreateCategoryRequest $request): JsonResponse
    {
        try {
            $category = Category::create($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Category created successfully.',
                'data' => $category,
            ], 201);
        } catch (QueryException $e) {
            // Handle database-related errors (e.g., unique constraint violations)
            return response()->json([
                'success' => false,
                'message' => 'Failed to create category due to database error.',
                'error' => $e->getMessage(),
            ], 400);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while creating the category.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified category.
     */
    public function show( $id): JsonResponse
    {
        try {
            $category = Category::findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => $category,
            ], 200);
        }
     catch (ModelNotFoundException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Category not found',
        ], 404);
    }
        catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch the category.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified category in storage.
     */
    public function update(UpdateCategoryRequest $request, Category $category): JsonResponse
    {
        try {
            $category->updateOrFail($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Category updated successfully.',
                'data' => $category,
            ], 200);
        }
        catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found',
            ], 404);
        }
        catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update category due to database error.',
                'error' => $e->getMessage(),
            ], 400);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while updating the category.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified category from storage.
     */
    public function destroy(Category $category): JsonResponse
    {
        try {
            $category->deleteOrFail();
            return response()->json([
                'success' => true,
                'message' => 'Category deleted successfully.',
            ], 200);
        }
        catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete category due to database constraints (e.g., foreign key issue).',
                'error' => $e->getMessage(),
            ], 400);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while deleting the category.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}