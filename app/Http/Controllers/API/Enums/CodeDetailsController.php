<?php

namespace App\Http\Controllers\API\Enums;

use App\Http\Controllers\Controller;
use App\Models\Core\Approval\CodeDetail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CodeDetailsController extends Controller
{
    public function index(string $codeId): JsonResponse
    {
        $details = CodeDetail::where('CodeID', $codeId)
            ->orderBy('DisplayOrder')
            ->get(['Value', 'Description']);

        $data = $details->map(fn($d) => [
            'value' => $d->Value,
            'label' => $d->Description,
        ]);

        return response()->json($data);
    }
}
