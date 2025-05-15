<?php

namespace App\Http\Controllers\CRM\DebtCollection;

use App\Events\DebtCollection\BulkNotificationEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\DebtCollection\LoanQueryRequest;
use App\Models\BR\Branch;
use App\Models\BR\DebtProduct;
use App\Models\BR\Product;
use App\Models\BR\UserCodeDetail;
use App\Models\Communication\BulkNotification;
use App\Services\BR\LoanService;
use App\Services\HRM\UserService;
use App\Services\SMSService;
use App\Traits\Controller\BulkNotificationTrait;
use App\Traits\Controller\LoansTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoanNotificationController extends Controller
{
    use BulkNotificationTrait;
    use LoansTrait;

    public function __construct()
    {
        $this->middleware('ajax')->only(['messages', 'store']);
    }
    /**
     * Display a listing of the resource.
     * @throws Exception
     */
    public function index(Request $request): View|JsonResponse
    {
        $this->authorize('viewAny', BulkNotification::class);
        if ($request->ajax()) {
            return $this->notifications(LoanService::MODULE);
        }

        return view('crm.debt-collection.notifications.index');
    }

    /**
     * Show the form for creating a new resource.
     * @throws Exception
     */
    public function create(LoanQueryRequest $request): View|JsonResponse
    {
        $this->authorize('create', BulkNotification::class);
        if ($request->ajax()) {
            $dated = $request->getDated();
            if ($dated instanceof Carbon) {
                $query = $request->applyFilters(
                    (new UserService($request->user()))->hideUsers(DebtProduct::query()->where('processDate', $dated))
                )->lock('WITH(NOLOCK)')->select('*');
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

        return view('crm.debt-collection.notifications.create')
            ->with('dated', $dated)
            ->with('branches', Branch::all(['OurBranchID', 'BranchName']))
            ->with('Products', Product::where('ProductTypeID', 'LN')->get(['ProductID', 'Description']))
            ->with('LoanSubClasses', UserCodeDetail::query()->where('ID', 'LoanSubClassID')->orderBy('DisplayOrder')->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(LoanQueryRequest $request): JsonResponse
    {
        $this->authorize('create', BulkNotification::class);
        $dated = $request->getDated();
        if (!$dated instanceof Carbon) {
            throw ValidationException::withMessages(['Label' => 'request date may be invalid']);
        }

        $loans = $request->applyFilters(
            (new UserService($request->user()))->hideUsers(DebtProduct::query()->where('processDate', $dated))
        )->lock('WITH(NOLOCK)')->count();
        if ($loans === 0) {
            throw ValidationException::withMessages(['Label' => 'there are no loans in the list to send.']);
        }

        $actor = $request->user();
        $values = $request->getValues();
        //create bulk sms and send one.
        try {
            $Bulk = DB::transaction(static function () use ($dated, $loans, $actor, $request, $values) {
                $Bulk = BulkNotification::create([
                                                  'Label'      => $request->validated('Label'),
                                                  'Module'     => LoanService::MODULE,
                                                  'Content'    => $request->validated('Content'),
                                                  'Total'      => $loans,
                                                  'Extra'      => array_merge($values, ['processDate' => $dated->format('Y-m-d')]),
                                                  'CreatedBy'  => $actor->Id,
                                                  'ModifiedBy' => $actor->Id,
                                                 ]);

                //run event to start work.
                event(new BulkNotificationEvent($Bulk, $actor, $values, $dated));
                return $Bulk;
            });
        } catch (Exception $e) {
            Log::error('Error sending loan bulk notification : ' . $e->getMessage());
            return $this->errored('unexpected error, try again later');
        }

        return $this->succeeded($loans . ' notifications to be sent', route('debt-notification.show', $Bulk->BulkNotificationID));
    }

    /**
     * Display the specified resource.
     */
    public function show($bulkNotificationID): RedirectResponse|View
    {
        $bulkNotification = BulkNotification::query()->where('BulkNotificationID', $bulkNotificationID)->first();
        if (!$bulkNotification instanceof BulkNotification) {
            return redirect()->back()->with('fail', 'loan not found, maybe closed.');
        }
        $this->authorize('view', $bulkNotification);

        return view('crm.debt-collection.notifications.show', compact('bulkNotification'))
            ->with('hasProgress', (is_null($bulkNotification->CompleteOn)));
    }

    /**
     * Use for Progress bar
     */
    public function edit($bulkNotificationID): JsonResponse
    {
        $bulkNotification = BulkNotification::query()->where('BulkNotificationID', $bulkNotificationID)->first();
        if (!$bulkNotification instanceof BulkNotification) {
            return $this->errored('Notification not found, maybe closed');
        }
        $this->authorize('view', $bulkNotification);
        $total = $bulkNotification->Total;

        $done = $bulkNotification->sms()->count();

        return $this->succeeded('ok', data: [
                                             'progress'    => (int) ($total > 0) ? (($done / $total) * 100) : 100,
                                             'done'        => $done,
                                             'total'       => (int) $total,
                                             'description' => 'Sending Messages (' . number_format($done) . ' / ' . number_format($total) . ')',
                                            ]);
    }

    /**
     * Update the specified resource in storage.
     * @throws Exception
     */
    public function messages($bulkNotificationID): JsonResponse
    {
        $bulkNotification = BulkNotification::query()->where('BulkNotificationID', $bulkNotificationID)->first();
        if (!$bulkNotification instanceof BulkNotification) {
            return $this->errored('Notification not found, maybe closed');
        }
        $this->authorize('view', $bulkNotification);

        return SMSService::dt($bulkNotification->sms(), ['source']);
    }
}
