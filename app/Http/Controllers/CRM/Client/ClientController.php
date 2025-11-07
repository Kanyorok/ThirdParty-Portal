<?php

namespace App\Http\Controllers\CRM\Client;

use App\Enums\CallStatusEnum;
use App\Enums\MarketingListEnum;
use App\Enums\MeetingStatusEnum;
use App\Enums\ScheduleStatusEnum;
use App\Enums\ScheduleUserStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Auth\User;
use App\Models\BR\Account;
use App\Models\BR\Client;
use App\Models\BR\Product;
use App\Models\Communication\Call;
use App\Models\CRM\Meeting;
use App\Models\CRM\Schedule;
use App\Services\BR\ClientService;
use App\Services\HRM\UserService;
use App\Services\StaticListsService;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;
use Yajra\DataTables\DataTables;

class ClientController extends Controller
{
    public function __construct()
    {
        $this->middleware('ajax')->only(['summary']);
        $this->authorizeResource(Client::class);
    }

    /**
     * Display a listing of the resource.
     * @throws Exception
     */
    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            $member_no = $request->get('member_no');
            $member_no = (is_string($member_no)) ? str_replace(['*', '%'], ['', ''], $member_no) : '';
            $phone = $request->get('phone');
            $phone = (is_string($phone)) ? str_replace(['*', '%'], ['', ''], $phone) : '';
            $name = $request->get('name');
            $name = (is_string($name)) ? str_replace(['*', '%'], ['', ''], $name) : '';
            $idNumber = $request->get('id_number');
            $idNumber = (is_string($idNumber)) ? str_replace(['*', '%'], ['', ''], $idNumber) : '';

            $query = Client::query();


            if (empty($member_no) && empty($phone) && empty($name) && empty($idNumber)) {
                $recent = Activity::query()->where('event', 'view')->where('subject_type', Client::getPrimaryKey())
                    ->where('causer_type', User::getPrimaryKey())->where('causer_id', $request->user()->Id)
                    ->latest()->distinct()->limit(10)->select(['subject_id', 'created_at'])->get('subject_id')->pluck('subject_id')->unique()->toArray();
                if (count($recent) > 0) {
                    $query->whereIn('ClientID', $recent);
                } else {
                    $query->whereNull('ClientID');
                }
            } else {
                if (!empty($member_no)) {
                    $query->where('ClientID', '=', $member_no);
                }

                if (!empty($name)) {
                    $query->where('Name', 'like', "%$name%");
                }

                if (!empty($phone)) {
                    $query->where(function (Builder $q) use ($phone) {
                        $q->where('Phone1', '=', $phone)
                            ->orWhere('Phone2', '=', $phone)
                            ->orWhere('Mobile', '=', $phone);
                    });
                }

                if (!empty($idNumber)) {
                    $query->where(function (Builder $q) use ($idNumber) {
                        $q->whereHas('individual', function (Builder $query) use ($idNumber) {
                            $query->where('PassportNo', '=', $idNumber);
                        })->orWhereHas('corporate', function (Builder $query) use ($idNumber) {
                            $query->where('CertificateNo', '=', $idNumber);
                        });
                    });
                }
            }
            return ClientService::dt((new UserService($request->user()))->hideUsers($query), ['type', 'status']);
        }

        return view('crm.clients.index');
    }

    /**
     * Display the specified resource
     * @throws Exception
     */
    public function show(Request $request, Client $client): View|JsonResponse
    {
        if ($request->ajax()) {
            return Datatables::of($client->accounts()->lock('WITH(NOLOCK)')->with(['product', 'status'])->select('*'))->addIndexColumn()
                ->editColumn('ClearBalance', function (Account $account) {
                    return '<span style="cursor: pointer;" class="clear-balance" data-bal="' . number_format($account->ClearBalance, 4) . '">**********</span>';
                })->editColumn('AccountID', function (Account $account) {
                    return '<a href="#"  data-click_url="' . route('accounts.summary', $account->AccountID) . '" data-summary_title="account summary" class="click-summary-data">' . $account->AccountID . '</a>';
                })->editColumn('product.Description', function (Account $account) {
                    if ($account->product instanceof Product) {
                        return $account->product->Description;
                    }
                    return '';
                })->editColumn('LastCreditTrxDate', function (Account $account) {
                    if (!$account->LastDebitTrxDate instanceof Carbon) {
                        if ($account->LastCreditTrxDate instanceof Carbon) {
                            return $account->LastCreditTrxDate;
                        }
                        return '';
                    }
                    if (!$account->LastCreditTrxDate instanceof Carbon) {
                        return $account->LastDebitTrxDate;
                    }
                    if ($account->LastDebitTrxDate->gte($account->LastCreditTrxDate)) {
                        return $account->LastDebitTrxDate;
                    }
                    return $account->LastCreditTrxDate;
                })/*->setRowClass('mouse_pointer user-select-none client-row-data')->setRowData([
                    'data-click-url' => function(Account $account) {
                        return route('accounts.show', $client->ClientID);
                    },
                    'data-double-url' => function(Client $client) {
                        return route('clients.show', $client->ClientID);

                ]) }*/ ->rawColumns(['ClearBalance', 'AccountID'])->make();
        }
        $schedule = null;
        $call = null;
        $meeting = null;

        if (is_numeric($request->call)) {
            $call = Call::query()->where('CallStatusID', CallStatusEnum::SuccessOngoing->value)->where('CallID', $request->get('call'))
                ->where('PartyID', $client->ClientID)->where('Party', Client::getPrimaryKey())
                ->whereBetween('StartOn', [Carbon::now()->startOfDay(), Carbon::now()->endOfDay()])
                ->lock('WITH(NOLOCK)')->first();
            if ($call instanceof Call) {
                activity()->causedBy($request->user())->performedOn($call)->event('start')->log('started a call.');
                $schedule = ($call?->schedule instanceof Schedule) ? $call->schedule : null;
            } else {
                $call = null;
            }
        } elseif (is_numeric($request->meet)) {
            $user = $request->user();
            $meeting = Meeting::query()->where('StatusID', MeetingStatusEnum::Ongoing)->where('MeetingID', $request->get('meet'))
                ->whereHas('meetingUsers', function (Builder $query) use ($user) {
                    $query->where('UserID', $user->Id);
                })->whereHas('meetingClients', function (Builder $query) use ($client) {
                    $query->where('ClientID', $client->ClientID);
                })->whereBetween('StartOn', [Carbon::now()->startOfDay(), Carbon::now()->endOfDay()])->lock('WITH(NOLOCK)')->first();
            if ($meeting instanceof Meeting) {
                activity()->causedBy($request->user())->performedOn($meeting)->event('start')->log('joined a meeting.');
            } else {
                $meeting = null;
            }
        } elseif (is_numeric($request->schedule)) {
            $user = $request->user();
            $schedule = Schedule::query()->where('t_Schedule.ScheduleID', $request->schedule)->whereBetween('t_Schedule.StartOn', [Carbon::now()->startOfDay(), Carbon::now()->endOfDay()])
                ->whereHas('scheduleClients', function (Builder $query) use ($client) {
                    $query->where('ClientID', $client->ClientID);
                })->whereHas('scheduleUsers', function (Builder $query) use ($user) {
                    $query->where('UserID', $user->Id);
                    $query->where('ScheduleUserStatus', ScheduleUserStatusEnum::Accepted);
                })->where('t_Schedule.ScheduleStatusID', '!=', ScheduleStatusEnum::Success)->lock('WITH(NOLOCK)')->first();
            if ($schedule instanceof Schedule) {
                activity()->causedBy($request->user())->performedOn($schedule)->event('view')->log('View client for a schedule.');
            } else {
                $schedule = null;
            }
        }

        $type = match ($client->ClientTypeID) {
            'E', 'I', 'G', 'M' => 'I',
            'CH', 'C', 'JNT' => 'C',
            default => '?'
        };
        $introducer = $client->introducer->first();

        $lock = Cache::lock('view-client-' . $client->ClientID, 100);
        if ($lock->get()) {
            activity()->causedBy($request->user())->performedOn($client)->event('view')->log('viewed client ' . $client->ClientID . ' details.');
        }

        return view('crm.clients.show', compact('client', 'schedule', 'call', 'type', 'introducer', 'meeting'))
            ->with('TicketCategories', StaticListsService::getList(StaticListsService::TicketCategories))
            ->with('MarketingListMember', $client->marketingLists()->where('Type', MarketingListEnum::Static->value)->select(['slug', 'Label'])->whereNull('t_MarketingListParties.DeletedOn')->get());
    }


    public function summary(Request $request, Client $client): View
    {
        $this->authorize('summary', $client);
        $activities = $client->activities()->latest('ActivityID')->limit(5)->get();
        activity()->causedBy($request->user())->performedOn($client)->event('summary')->log('viewed client details.');
        return view(
            'crm.clients.summary',
            compact('client', 'activities')
        );
    }
}
