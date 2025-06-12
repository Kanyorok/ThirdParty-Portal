<?php

namespace App\Http\Controllers\CRM\DebtCollection;

use App\Helpers\StringHelper;
use App\Http\Controllers\Controller;
use App\Models\BR\Client;
use App\Models\BR\DebtProduct;
use App\Models\BR\UserCodeDetail;
use App\Models\CRM\DebtRecovery\LoanAssignment;
use App\Services\HRM\UserService;
use App\Traits\Controller\LoansTrait;
use DateTimeZone;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class LoansController extends Controller
{
    use LoansTrait;

    /**
     * Display a listing of the resource.
     * @throws Exception
     */
    public function index(Request $request): View|JsonResponse
    {
        $this->authorize('viewAny', DebtProduct::class);
        if ($request->ajax()) {
            if ($request->has('dated') && StringHelper::isInteger($request->dated) && (int)$request->dated > 0) {
                try {
                    $dated = Carbon::createFromFormat('U', $request->dated);
                    $dated?->setTimezone(new DateTimeZone(config('app.timezone')));
                } catch (Exception) {
                    $dated = null;
                }

                if ($dated instanceof Carbon) {
                    $actor = $request->user();

                    $member_no = $request->get('member_no');
                    $member_no = (is_string($member_no)) ? str_replace(['*', '%'], ['', ''], $member_no) : '';
                    $query = (new UserService($request->user()))->hideUsers(DebtProduct::query()->where('processDate', $dated));

                    //assignments.
                    if (($request->get('assignee') === $actor->UserID) || (!$request->user()->can('assign', DebtProduct::class))) {
                        $query->whereIn('AccountID', $actor->loansAssigned()->whereNull('t_LoanAssignments.EndOn')->select('t_LoanAssignments.AccountID'));
                    } elseif ($request->get('assignee') === 'none') {
                        $query->whereNotIn('AccountID', LoanAssignment::query()->whereNull('t_LoanAssignments.EndOn')->select('t_LoanAssignments.AccountID'));
                    } elseif ($request->get('assignee') !== 'all') {
                        return $this->errored('Invalid Ownership filter given');
                    }

                    if ($request->has('arrears') && StringHelper::isInteger($request->arrears)) {
                        $query->where('ArrearsDays', '>', abs($request->arrears));
                    } else {
                        $query->where('ArrearsDays', '>', 1);
                    }

                    if ($request->has('status')) {
                        $status = UserCodeDetail::query()->where('ID', 'LoanSubClassID')->where('SubCodeID', $request->status)->first();
                        if ($status instanceof UserCodeDetail) {
                            $query->where('Classification', $status->Description);
                        }
                    }

                    if (!empty($member_no)) {
                        $query->where('ClientID', '=', $member_no);
                    }

                    if (is_numeric($request->balance)) {
                        $query->where('OutstandingBalance', '<', (abs($request->balance) * -1));
                    }

                    $query->lock('WITH(NOLOCK)')->select('*');
                } else {
                    $query = collect();
                }
            } else {
                $query = collect();
            }
            return $this->getLoans($query);
        }

        try {
            $dated = Carbon::parse(DebtProduct::query()->max('processdate'));
        } catch (Exception $exception) {
            $dated = null;
        }
        return view('crm.debt-collection.index')
            ->with('LoanSubClasses', UserCodeDetail::query()->where('ID', 'LoanSubClassID')->orderBy('DisplayOrder')->get())
            ->with('dated', $dated);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $loan_id): RedirectResponse|View
    {
        $loan = DebtProduct::query()->where('AccountID', $loan_id)->latest('processDate')->withCount(['guarantors', 'collaterals'])->first();
        if (!$loan instanceof DebtProduct) {
            return redirect()->back()->with('fail', 'loan not found, maybe closed.');
        }
        $this->authorize('view', $loan);

        $assignment = $loan->assignment()->whereNull('EndOn')->with('user')->first();
        if (!$assignment instanceof LoanAssignment) {
            $assignment = null;
        }
        //dd($loan->branch);
        return view('crm.debt-collection.show', compact('loan'))
            ->with('client', Client::query()->where('ClientID', $loan->ClientID)->with('type')->first(['ClientTypeID', 'Name', 'ClientID', 'PhotoID', 'Mobile', 'Phone1', 'Phone2', 'Email']))
            ->with('assignment', $assignment);
    }
}
