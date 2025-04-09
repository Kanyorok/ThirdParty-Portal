<?php

namespace App\Http\Controllers\DebtCollection;

use App\Http\Controllers\Controller;
use App\Http\Resources\Base\ActivityCollection;
use App\Models\Activity;
use App\Models\BR\DebtProduct;
use App\Models\Call;
use App\Models\CrmEmail;
use App\Models\CrmSMS;
use App\Models\Task;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LoanActivityController extends Controller
{
    public function __construct()
    {
        $this->middleware('ajax');
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, string $product_id): ActivityCollection|JsonResponse
    {
        $product = DebtProduct::query()->where('AccountID', $product_id)->oldest('processDate')->first();
        if (!$product instanceof DebtProduct) {
            return $this->errored('Product not found, maybe closed.');
        }
        $this->authorize('view', $product);

        return new ActivityCollection(Activity::query()
            ->where(function (Builder $builder) use ($product) {
                $builder->where(function (Builder $query) use ($product) {
                    $query->where('ActivityType', CrmSMS::getPrimaryKey())
                        ->whereIn('ActivityTypeID', CrmSMS::query()->where('SourceID', $product->AccountID)->where('t_SMS.Source', DebtProduct::getPrimaryKey())->select('t_SMS.Id'));
                })->orWhere(function (Builder $query) use ($product) {
                    $query->where('ActivityType', CrmEmail::getPrimaryKey())
                        ->whereIn('ActivityTypeID', CrmEmail::query()->where('t_CRMEmails.SourceID', $product->AccountID)->where('t_CRMEmails.Source', DebtProduct::getPrimaryKey())->select('t_CRMEmails.EmailID'));
                })->orWhere(function (Builder $query) use ($product) {
                    $query->where('ActivityType', Call::getPrimaryKey())
                        ->whereIn('ActivityTypeID', Call::query()->where('t_Calls.SourceID', $product->AccountID)->where('t_Calls.Source', DebtProduct::getPrimaryKey())->select('t_Calls.CallID'));
                })->orWhere(function (Builder $query) use ($product) {
                    $query->where('ActivityType', Task::getPrimaryKey())
                        ->whereIn('ActivityTypeID', Task::query()->where('t_Tasks.SourceID', $product->AccountID)->where('t_Tasks.Source', DebtProduct::getPrimaryKey())->select('t_Tasks.TaskID'));
                });
            })->orWhere(function (Builder $query) use ($product) {
                $query->where('ActivityType', DebtProduct::getPrimaryKey())->where('ActivityTypeID', $product->AccountID);
            })->latest('t_PartyActivities.CreatedOn')->simplePaginate(4));
    }
}
