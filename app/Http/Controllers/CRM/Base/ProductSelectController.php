<?php

namespace App\Http\Controllers\CRM\Base;

use App\Http\Controllers\Controller;
use App\Models\BR\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductSelectController extends Controller
{
    public function __construct()
    {
        $this->middleware('ajax');
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $data = [];
        if ($request->has('q')) {
            $search = $request->q;
            $data = Product::query()
                ->where(function ($query) use ($search) {
                    $query->where('ProductID', 'LIKE', "%$search%")
                        ->orWhere('Description', 'LIKE', "%$search%");
                })->lock('WITH(NOLOCK)')->select(['ProductID', "Description"])->get();
        }

        return response()->json($data);
    }
}
