<?php

namespace App\Http\Controllers\ProductDev;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductDev\ProductDevelopmentFeatureRequest;
use App\Models\ProductDevelopment;
use App\Models\ProductDevelopmentFeature;
use App\Services\ProductDevService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductDevelopmentFeatureController extends Controller
{
    public function __construct()
    {
        $this->middleware('ajax')->except(['index', 'show']);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProductDevelopmentFeatureRequest $request, string $product_id): JsonResponse
    {
        $product = ProductDevelopment::where('ProductID', $product_id)->first();
        if (!$product instanceof ProductDevelopment) {
            return $this->errored('product could be invalid', status: 404);
        }
        $this->authorize('update', $product);

        $productFeature = (new ProductDevService($product))->addFeature($request->validated('feature_title'), $request->validated('feature_content'), $request->user());

        return $this->succeeded('product feature added', data: [
            'feature' => $productFeature->toArray()
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProductDevelopmentFeatureRequest $request, string $product_id, string $feature_id): JsonResponse
    {
        $product = ProductDevelopment::where('ProductID', $product_id)->first();
        if (!$product instanceof ProductDevelopment) {
            return $this->errored('product could be invalid', status: 404);
        }
        $this->authorize('update', $product);

        $feature = $product->features()->where('Id', $feature_id)->first();
        if (!$feature instanceof ProductDevelopmentFeature) {
            return $this->errored('product feature could be invalid', status: 404);
        }

        $productFeature = (new ProductDevService($product))->updateFeature($feature, $request->validated('feature_title'), $request->validated('feature_content'), $request->user());

        return $this->succeeded('product feature updated', data: [
            'feature' => $productFeature->toArray()
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $product_id, string $feature_id): JsonResponse
    {
        $product = ProductDevelopment::where('ProductID', $product_id)->first();
        if (!$product instanceof ProductDevelopment) {
            return $this->errored('product could be invalid', status: 404);
        }

        $this->authorize('update', $product);
        $feature = $product->features()->where('Id', $feature_id)->first();
        if (!$feature instanceof ProductDevelopmentFeature) {
            return $this->errored('product feature could be invalid', status: 404);
        }

        $user = $request->user();
        $feature->forceFill([
            'DeletedOn' => now(),
            'DeletedBy' => $user->Id,
        ])->save();

        activity()->causedBy($user)->performedOn($product)->event('feature')->log('added a feature  ' . $feature->Feature . ' to Product Development ' . Str::upper($product->ProductID) . '.');

        return $this->succeeded('product feature deleted', data: [
            'feature' => $feature->toArray()
        ]);
    }
}
