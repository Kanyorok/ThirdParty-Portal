<?php

namespace App\Http\Controllers\API\Enums;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ThirdPartyTypesEnumController extends Controller
{
    public function index(): JsonResponse
    {
        // Fetch distinct categories referenced in t_ThirdPartyTypes.Type
        $rows = DB::table('t_ThirdPartyTypes as tpt')
            ->join('t_CategoryMaster as cm', 'cm.Id', '=', 'tpt.Type')
            ->select('cm.Id', 'cm.Name')
            ->distinct()
            ->get();

        // Map to expected short codes S/T from name heuristics
        $data = $rows->map(function ($row) {
            $upper = strtoupper($row->Name);
            $value = match (true) {
                str_starts_with($upper, 'SUP') => 'S',
                str_starts_with($upper, 'TEN') => 'T',
                default => substr($upper, 0, 1),
            };
            return [
                'value' => $value,
                'label' => $row->Name,
            ];
        })
        ->unique('value')
        ->values();

        // Ensure ordering S then T if both exist
        $order = ['S' => 1, 'T' => 2];
        $sorted = $data->sortBy(fn($i) => $order[$i['value']] ?? 99)->values();

        return response()->json($sorted);
    }
}