<?php

namespace App\Http\Controllers\Settings\Codes;

use App\Http\Controllers\Controller;
use App\Http\Resources\CountryResource;
use App\Models\Core\Country;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;

class ApiCountryController extends Controller
{
    /**
     * Get list of countries for dropdowns
     *
     * @return AnonymousResourceCollection
     */
    public function list(): AnonymousResourceCollection
    {
        $version = (string)config('app.countries_cache_version', env('COUNTRIES_CACHE_VERSION', 'v1'));
        $cacheKey = 'countries_list_' . $version;

        $countries = Cache::remember($cacheKey, 3600, function () {
            return Country::active()
                ->ordered()
                ->with('currency')
                ->get();
        });

        return CountryResource::collection($countries);
    }

    /**
     * Get localities for a specific country
     *
     * @param string $countryCode
     * @return \Illuminate\Http\JsonResponse
     */
    public function localities(string $countryCode)
    {
        $country = Country::where('CountryCode', $countryCode)->firstOrFail();

        $localities = $country->localities()
            ->orderBy('Name')
            ->get(['ID', 'Name']);

        return response()->json([
            'data' => $localities
        ]);
    }
}
