<?php

namespace App\Http\Controllers\CRM\DebtCollection;

use App\Exceptions\ErroredException;
use App\Http\Controllers\Controller;
use App\Http\Requests\DebtCollection\LoanAssignmentRequest;
use App\Models\BR\DebtProduct;
use App\Models\CRM\DebtRecovery\LoanAssignment;
use App\Services\DebtCollection\LoanService;
use App\Services\PartyService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;
use Yajra\DataTables\DataTables;

class LoanAssignmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('ajax');
    }

    /**
     * Check Assignment History
     */
    public function index(string $product_id): JsonResponse
    {
        $loan = DebtProduct::query()->where('AccountID', $product_id)->latest('processDate')->first();
        if (! $loan instanceof DebtProduct) {
            return $this->errored('Loan not found, maybe closed.');
        }
        $this->authorize('view', $loan);

        try {
            return Datatables::of($loan->assignment()->with(['user', 'creator']))->addIndexColumn()
                ->editColumn('user', function (LoanAssignment $assignment) {
                    return (new PartyService($assignment->user))->getDTRow();
                })->editColumn('EndOn', function (LoanAssignment $assignment) {
                    if ($assignment->EndOn instanceof Carbon) {
                        return $assignment->EndOn?->format('F d, Y h:i A');
                    }

                    return 'Current';
                })->editColumn('StartOn', function (LoanAssignment $assignment) {
                    return ' <details><summary>' . $assignment->StartOn?->format('F d, Y h:i A') . '</summary>
                              <p>' . $assignment->creator?->Name . ' (' . $assignment->creator?->UserID . ')</p></details>';
                })->setRowClass(function (LoanAssignment $assignment) {
                    return (is_null($assignment->EndOn)) ? 'fw-bold' : '';
                })->rawColumns(['StartOn', 'user'])->make();
        } catch (Exception $e) {
            Log::error('Error getting assignment history failed: ');
            Log::error($e);
        }

        return $this->errored('unexpected error, try again later');
    }

    /**
     * Reassign
     */
    public function store(LoanAssignmentRequest $request, string $product_id): JsonResponse
    {
        $loan = DebtProduct::query()->where('AccountID', $product_id)->latest('processDate')->first();
        if (! $loan instanceof DebtProduct) {
            return $this->errored('Loan not found, maybe closed.');
        }
        $this->authorize('assign', $loan);
        $assignee = $request->getAssignee();
        $actor = $request->user();

        try {
            DB::transaction(static function () use ($actor, $loan, $assignee) {
                (new LoanService($loan))->assign($assignee, $actor);
            });
        } catch (ErroredException $e) {
            return $e->toJson();
        } catch (Throwable | Exception $e) {
            Log::error('Error  re assigned loan failed: ' . $e->getMessage());

            return $this->errored('unexpected error, try again later');
        }

        return $this->succeeded('loan assigned successfully, redirecting', route('debt-collection.show', $loan->AccountID));
    }
}
