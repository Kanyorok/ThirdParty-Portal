<?php

namespace App\Http\Controllers\ThirdParty;

use App\Http\Controllers\Controller;
use App\Models\Core\Country;
use App\Models\Core\Approval\CodeDetail;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class MetadataController extends Controller
{
    public function getCountries(): JsonResponse
    {
        $countries = Country::select('Id as id', 'Name as name', 'CountryCode')
            ->whereNull('DeletedOn')
            ->orderBy('Name')
            ->get()
            ->map(function ($country) {
                return [
                    'id' => $country->id,
                    'name' => $country->name,
                    'flag' => $this->getFlagEmoji($country->CountryCode)
                ];
            });

        return response()->json(['status' => 'success', 'data' => $countries]);
    }

    public function getBusinessTypes(): JsonResponse
    {
        $types = CodeDetail::where('CodeID', 'BusinessType')
            ->select('ID as id', 'Description as name')
            ->whereNull('DeletedOn')
            ->orderBy('Description')
            ->get();

        return response()->json(['status' => 'success', 'data' => $types]);
    }

    private function getFlagEmoji(string $countryCode): string
    {
        if (strlen($countryCode) !== 2) return "🌐";

        $code = strtoupper($countryCode);
        return mb_convert_encoding('&#' . (127397 + ord($code[0])) . ';', 'UTF-8', 'HTML-ENTITIES') .
            mb_convert_encoding('&#' . (127397 + ord($code[1])) . ';', 'UTF-8', 'HTML-ENTITIES');
    }

    public function getSupplierCategories()
    {
        $categories = DB::table('t_SupplierCategories')
            ->where('IsActive', 1)
            ->whereNull('DeletedOn')
            ->select(
                'SupplierCategoryID as id',
                'CategoryName as name'
            )
            ->orderBy('CategoryName', 'asc')
            ->get();

        return response()->json(['success' => true, 'data' => $categories]);
    }
}
