<?php

namespace App\Http\Controllers\API\ThirdParty;

use App\Http\Controllers\Controller;
use App\Models\ThirdParty\ThirdPartyCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ThirdPartyCategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $thirdPartyCategories = ThirdPartyCategory::all();

        return response()->json($thirdPartyCategories);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'ThirdPartyId' => 'required|integer|exists:t_ThirdParties,Id',
            'CategoryID' => 'required|integer|exists:t_CodeDetails,Id',
        ]);

        $thirdPartyCategory = ThirdPartyCategory::create([
            'ThirdPartyId' => $request->ThirdPartyId,
            'CategoryID' => $request->CategoryID,
            'CreatedBy' => Auth::id(),
            'CreatedOn' => now(),
        ]);

        return response()->json($thirdPartyCategory, 201);
    }

    public function show(ThirdPartyCategory $thirdPartyCategory): JsonResponse
    {
        return response()->json($thirdPartyCategory);
    }

    public function update(Request $request, ThirdPartyCategory $thirdPartyCategory): JsonResponse
    {
        $request->validate([
            'ThirdPartyId' => 'sometimes|required|integer|exists:t_ThirdParties,Id',
            'CategoryID' => 'sometimes|required|integer|exists:t_CodeDetails,Id',
        ]);

        $thirdPartyCategory->update([
            'ThirdPartyId' => $request->input('ThirdPartyId', $thirdPartyCategory->ThirdPartyId),
            'CategoryID' => $request->input('CategoryID', $thirdPartyCategory->CategoryID),
            'ModifiedBy' => Auth::id(),
            'ModifiedOn' => now(),
        ]);

        return response()->json($thirdPartyCategory);
    }

    public function destroy(ThirdPartyCategory $thirdPartyCategory): JsonResponse
    {
        $thirdPartyCategory->update([
            'DeletedBy' => Auth::id(),
            'DeletedOn' => now(),
        ]);
        $thirdPartyCategory->delete();

        return response()->json(null, 204);
    }
}
