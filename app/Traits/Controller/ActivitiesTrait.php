<?php

namespace App\Traits\Controller;

use App\Services\PartyService;
use Exception;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Spatie\Activitylog\Models\Activity;
use Yajra\DataTables\DataTables;

trait ActivitiesTrait
{
    public function activities(Builder|MorphMany $query, array $with = []): JsonResponse
    {
        if (! empty($with)) {
            $query->with($with);
        }

        try {
            return Datatables::of($query->lock('WITH(NOLOCK)')->select('*'))->addIndexColumn()
                ->editColumn('created_at', function (Activity $activity) {
                    return $activity->created_at?->format('M d, Y H:i');
                })->editColumn('causer.Name', function (Activity $activity) use ($with) {
                    if (in_array('causer', $with, true)) {
                        return (new PartyService($activity->causer))->getDTRow();
                    }

                    return '';
                })->rawColumns(['causer.Name'])->make();
        } catch (Exception $e) {
            return $this->errored('fetching data failed, try again later');
        }
    }
}
