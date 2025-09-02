<?php

namespace App\Http\Controllers\API\Enums;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ThirdPartyTypesEnumController extends Controller
{
    public function index(): JsonResponse
    {
        // Fetch ThirdPartyTypes with related category names; value = TypeId, label = CategoryMaster.Name
        $rows = DB::table('t_ThirdPartyTypes as tpt')
            ->join('t_CategoryMaster as cm', 'cm.Id', '=', 'tpt.Type')
            ->select('tpt.TypeId', 'cm.Name')
            ->orderBy('cm.Name')
            ->get();

        $data = $rows->map(fn($r) => [
            'value' => $r->TypeId,
            'label' => $r->Name,
        ]);

        return response()->json($data);
    }
}