<?php

namespace App\Http\Controllers\ThirdParty\API;

use App\Http\Controllers\Controller;
use App\Models\Core\Approval\CodeDetail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LookupController extends Controller
{
    public function __invoke(Request $request, string $codeId): JsonResponse
    {
        $options = CodeDetail::query()
            ->where('CodeID', $codeId)
            ->where('IsActive', 1)
            ->orderBy('DisplayOrder')
            ->get(['Value', 'Description']);

        return response()->json([
            'status' => 'success',
            'data' => $options
        ]);
    }

    public function bulk(Request $request): JsonResponse
    {
        $codes = array_filter(explode(',', $request->query('codes', '')));

        if (empty($codes)) {
            return response()->json(['status' => 'success', 'data' => []]);
        }

        $data = CodeDetail::query()
            ->whereIn('CodeID', $codes)
            ->where('IsActive', 1)
            ->orderBy('DisplayOrder')
            ->get(['CodeID', 'Value', 'Description'])
            ->groupBy('CodeID')
            ->map(fn($group) => $group->map(fn($item) => [
                'Value' => $item->Value,
                'Description' => $item->Description
            ]));

        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }
}
