<?php

namespace App\Http\Controllers\Settings\Codes;

use App\Http\Controllers\Controller;
use App\Models\Core\Country;
use App\Models\Core\Locality;
use App\Services\LocalityService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LocalitySelectController extends Controller
{
    public function __construct()
    {
        $this->middleware('ajax');
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $data = [];
        // Preferred: filter by numeric CountryId directly from t_Localities.CountryId
        if ($request->filled('countryId')) {
            $search = (string) $request->get('q', '');
            $countryId = (int) $request->get('countryId');
            $data = Locality::query()
                ->where('CountryId', $countryId)
                ->with(['in'])
                ->when($search !== '', function (Builder $query) use ($search) {
                    $query->where('Name', 'LIKE', "%$search%")
                        ->orWhereHas('in', function (Builder $q) use ($search) {
                            $q->where('Name', 'LIKE', "%$search%");
                        });
                })
                ->lock('WITH(NOLOCK)')
                ->select(['ID', 'Name', 'LocalityID'])
                ->limit(20)
                ->get()
                ->map(function ($locality) {
                    return (new LocalityService($locality))->getLocation(true);
                })
                ->toArray();
        } elseif ($request->has('country') && $request->has('q')) {
            $search = $request->q;
            $country = Country::query()->where('CountryCode', $request->country)->first();
            if ($country instanceof Country) {
                $data = $country->localities()->whereNotNull('t_Localities.LocalityID')->with(['in'])
                    ->where(function (Builder $query) use ($search) {
                        $query->where('Name', 'LIKE', "%$search%")
                            ->orwhereHas('in', function (Builder $query) use ($search) {
                                $query->where('Name', 'LIKE', "%$search%");
                            });
                    })->lock('WITH(NOLOCK)')->select(['ID', "Name", 'LocalityID'])->limit(20)->get()
                    ->map(function ($locality) {
                        return (new LocalityService($locality))->getLocation(true);
                        /*if ($locality->in instanceof Locality){
                            return [
                                'ID'   => $locality->ID,
                                'Name' => $locality->Name . ' - ' . $locality->in?->Name,
                            ];
                        }

                        return [
                            'ID'   => $locality->ID,
                            'Name' => $locality->Name,
                        ];*/
                    })->toArray();
            }
        }
        /* if ($request->has('q') && in_array($request->type, LocalityTypeEnum::values(), true)) {

             if (LocalityTypeEnum::from($request->type)->hasParent()) {
                 $data = Locality::with(['in'])->where('LocationType', $request->type)
                     ->where(function (Builder $query) use ($search) {
                         $query->where('Name', 'LIKE', "%$search%")
                             ->orwhereHas('in', function (Builder $query) use ($search) {
                                 $query->where('Name', 'LIKE', "%$search%");
                             });
                     })->lock('WITH(NOLOCK)')->select(['ID', "Name", 'LocalityID'])->limit(20)->get()
                     ->map(function ($locality) {
                         return [
                                 'ID'   => $locality->ID,
                                 'Name' => $locality->Name . ' - ' . $locality->in?->Name,
                                ];
                     })->toArray();
             } else {
                 $data = Locality::query()->where('LocationType', $request->type)
                     ->where('Name', 'LIKE', "%$search%")
                     ->lock('WITH(NOLOCK)')->select(['ID', "Name"])->limit(20)->get(['ID', "Name"]);
             }
         }*/

        return response()->json($data);
    }
}
