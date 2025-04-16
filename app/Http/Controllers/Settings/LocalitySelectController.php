<?php

namespace App\Http\Controllers\Settings;

use App\Enums\LocalityTypeEnum;
use App\Http\Controllers\Controller;
use App\Models\Locality;
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
        if ($request->has('q') && in_array($request->type, LocalityTypeEnum::values(), true)) {
            $search = $request->q;
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
        }

        return response()->json($data);
    }
}
