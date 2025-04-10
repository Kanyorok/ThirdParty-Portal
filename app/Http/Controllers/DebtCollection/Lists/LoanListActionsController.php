<?php

namespace App\Http\Controllers\DebtCollection\Lists;

use App\Enums\MarketingListEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\DebtCollection\LoanQueryRequest;
use App\Models\BR\DebtProduct;
use App\Models\MarketingList;
use App\Services\Marketing\ListService;
use App\Services\UserService;
use App\Traits\Controller\LoansTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;

class LoanListActionsController extends Controller
{
    use LoansTrait;

    /**
     * Handle the incoming request.
     */
    public function __invoke(LoanQueryRequest $request, MarketingList $list): JsonResponse
    {
        if ($request->isMethod('get')) {
            $this->authorize('view', $list);
            $dated = $request->getDated();
            if ($dated instanceof Carbon) {
                $query = $request->applyFilters((new UserService($request->user()))->hideUsers(DebtProduct::query()->where('processDate', $dated)));
                if ($request->q === 'current') {
                    $query->whereIn('AccountID',
                        $list->parties()->where('t_MarketingListParties.Party', DebtProduct::getPrimaryKey())->lock('WITH(NOLOCK)')->select('t_MarketingListParties.PartyID')
                    );
                } else {
                    $query->whereNotIn('AccountID',
                        $list->parties()->where('t_MarketingListParties.Party', DebtProduct::getPrimaryKey())->lock('WITH(NOLOCK)')->select('t_MarketingListParties.PartyID')
                    );
                }
                $query->lock('WITH(NOLOCK)')->select('*');

            } else {
                $query = collect();
            }
            return $this->getLoans($query);
        }

        if ($list->Type === MarketingListEnum::Dynamic->value) {
            return $this->errored('maybe dynamic cannot be changed');
        }

        if ($request->isMethod('put')) {
            $this->authorize('update', $list);
            $service = new ListService($list);
            if ($service->isProcessing()) {
                return $this->errored('list is processing');
            }
            if ($list->Source !== DebtProduct::getPrimaryKey()) {
                return $this->errored('list does not support loans.');
            }
            if ($request->has('loans')) {
                $loanIDs = array_map('trim', explode(',', $request->input('loans')));
                ($request->q === 'current')
                    ? $service->removeLoans($loanIDs, $request->user())
                    : $service->addLoans($loanIDs, $request->user());
            }
            return $this->succeeded('processed successfully');
        }

        return $this->errored('invalid action at this moment');
    }
}
