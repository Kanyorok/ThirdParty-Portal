<?php

namespace App\Http\Controllers\API\Enums;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ThirdPartyTypesEnumController extends Controller
{
    public function index(): JsonResponse
    {
        // Fetch ThirdPartyTypes; value = Code, label = Description (or Name)
        // Original code joined with CategoryMaster, but model says 'Description' is in t_ThirdPartyTypes itself.
        // Let's rely on the model 'Code' and 'Description' if possible, or keep the join if 'Name' is preferred.
        // The join was: ->select('tpt.TypeId', 'cm.Name')
        // I will change it to return Code.
        $rows = DB::table('t_ThirdPartyTypes as tpt')
            ->select('tpt.Code', 'tpt.Description')
            ->orderBy('tpt.Description')
            ->get();

        $data = $rows->map(fn($r) => [
            'value' => $r->Code,
            'label' => $r->Description,
        ]);

        return response()->json($data);
    }
}
