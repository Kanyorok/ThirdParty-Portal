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
        // Cache countries for 1 hour since they don't change frequently
        $countries = Cache::remember('countries_list', 3600, function () {
            return Country::active()
                ->ordered()
                ->with('currency')
                ->get();
        });

        return CountryResource::collection($countries);
    }
}