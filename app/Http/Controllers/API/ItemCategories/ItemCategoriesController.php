<?php

namespace App\Http\Controllers\API\ItemCategories;

use App\Http\Controllers\Controller;
use App\Models\Inventory\ItemCategories;
use Illuminate\Http\JsonResponse;

class ItemCategoriesController extends Controller
{
    /**
     * Return a list of item categories (top-level).
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        // Fetch only top-level categories with optional parent relationship
        $categories = ItemCategories::whereNull('ParentId')
            ->with('parent') // Optional: include parent if needed
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categories,
        ], 200);
    }
}
