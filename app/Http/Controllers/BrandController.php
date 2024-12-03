<?php


namespace App\Http\Controllers;

use App\Http\Requests\Brand\CreateBrandRequest;
use App\Http\Requests\Brand\UpdateBrandRequest ;
use App\Models\Brand;

use Illuminate\Http\JsonResponse;
use Illuminate\Database\QueryException;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class BrandController extends Controller
{
    /**
     * Display a listing of brands.
     */
    public function index(): JsonResponse
    {
        try {
            $brands = Brand::all();

            return response()->json([
                'success' => true,
                'data' => $brands,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch brands.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created brand in storage.
     */
    public function store(CreateBrandRequest $request): JsonResponse
    {
        try {

            // return response()->json([
            //     'success' => true,
            //     'message' => 'this is a dummy message create',
               
            //     'validatited' => $request->validated(),
            // ], 200);


            $brand = Brand::create($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Brand created successfully.',
                'data' => $brand,
            ], 201);
        } catch (QueryException $e) {
            // Handle database-related errors (e.g., unique constraint violations)
            return response()->json([
                'success' => false,
                'message' => 'Failed to create brand due to database error.',
                'error' => $e->getMessage(),
            ], 400);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while creating the brand.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified brand.
     */
    public function show( $id): JsonResponse
    {
        try {
            $category = Brand::findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => $category,
            ], 200);
        }
     catch (ModelNotFoundException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Brand not found',
        ], 404);
    }
        catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch the Brand.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified brand in storage.
     */
    public function update(UpdateBrandRequest $request, Brand $brand): JsonResponse
    {
        try {
            // return response()->json([
            //     'success' => true,
            //     'message' => 'this is a dummy message update',
            //     'data' => $brand,
            //     'validatited' => $request->validated(),
            // ], 200);

           

            $brand->update($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Brand updated successfully.',
                'data' => $brand,
            ], 200);
        }
        catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Brand not found',
            ], 404);
        }
        catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update brand due to database error.',
                'error' => $e->getMessage(),
            ], 400);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while updating the brand.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified brand from storage.
     */
    public function destroy(Brand $brand): JsonResponse
    {
        try {
            $brand->deleteOrFail();
            return response()->json([
                'success' => true,
                'message' => 'Brand deleted successfully.',
            ], 200);
        }
        catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete brand due to database constraints (e.g., foreign key issue).',
                'error' => $e->getMessage(),
            ], 400);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while deleting the brand.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}



