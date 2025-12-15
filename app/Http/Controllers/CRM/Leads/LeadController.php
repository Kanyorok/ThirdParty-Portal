<?php

namespace App\Http\Controllers\CRM\Leads;

use App\Enums\CallStatusEnum;
use App\Enums\Core\PermissionEnum;
use App\Enums\LeadStatusEnum;
use App\Enums\LeadTypeEnum;
use App\Enums\MarketingListEnum;
use App\Enums\MeetingStatusEnum;
use App\Enums\ScheduleStatusEnum;
use App\Enums\ScheduleUserStatusEnum;
use App\Exceptions\ErroredException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Lead\NewLeadRequest;
use App\Models\Auth\Team;
use App\Models\Auth\User;
use App\Models\Communication\Call;
use App\Models\Communication\EmailConversation;
use App\Models\Core\Country;
use App\Models\Core\Locality;
use App\Models\CRM\Contact;
use App\Models\CRM\Lead;
use App\Models\CRM\Meeting;
use App\Models\CRM\Schedule;
use App\Services\CRMEmailService;
use App\Services\LeadService;
use App\Services\LocalityService;
use App\Services\StaticListsService;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class LeadController extends Controller
{
    public function __construct()
    {
        $this->middleware('ajax')->except(['index', 'show']);
    }

    /**
     * Display a listing of the resource.
     * @throws Exception
     */
    public function index(Request $request): View|JsonResponse
    {
        $this->authorize('viewAny', Lead::class);
        $actor = $request->user();
        if ($request->ajax()) {
            $query = Lead::query();

            //check roles.
            if (!$actor->can([PermissionEnum::LeadUpdate->value, PermissionEnum::LeadDelete->value])) {
                $query->where(function (Builder $query) use ($actor) {
                    //check users.
                    $query->where('t_Leads.RelationshipManagerID', $actor->Id)
                        ->orWhereHas('watchers', function (Builder $query) use ($actor) {
                            $query->where(function (Builder $query) use ($actor) {
                                $query->where('t_LeadUsers.PartyID', $actor->Id)->where('t_LeadUsers.Party', User::getPrimaryKey());
                            })->orWhere(function (Builder $query) use ($actor) {
                                $query->where('t_LeadUsers.Party', Team::getPrimaryKey())->whereIn('t_LeadUsers.PartyID', $actor->teams()->pluck('id')->toArray());
                            });
                        });
                });
            }
            if ($request->q === 'won') {
                $query->where('t_Leads.Status', LeadStatusEnum::Won->value)->whereNull(['ArchivedBy', 'ArchivedOn'])->withTrashed();
            } else {
                $query->where('t_Leads.Status', '!=', LeadStatusEnum::Won->value);
            }
            return LeadService::dt($query, ['location', 'photo', 'industry']);
        }

        $StaticLists = StaticListsService::getList([StaticListsService::Industries, StaticListsService::MarketingModes, StaticListsService::CustomerType]);

        return view('crm.leads.index')
            ->with('Industries', $StaticLists->where('CodeID', StaticListsService::Industries))
            ->with('CustomerTypes', $StaticLists->where('CodeID', StaticListsService::CustomerType))
            ->with('MarketingModes', $StaticLists->where('CodeID', StaticListsService::MarketingModes));
    }

    public function create(Request $request): JsonResponse|View
    {
        $this->authorize('create', Lead::class);
        $type = $request->type;
        if (in_array($type, [LeadTypeEnum::Individual->name, LeadTypeEnum::Company->name], true)) {
            $email = '';
            $emailConversation = null;
            if ($request->has('conversation')) {
                $emailConversation = EmailConversation::query()->where('Id', $request->conversation)->first();
                if ($emailConversation instanceof EmailConversation) {
                    $email = (new CRMEmailService($emailConversation->email))->getParty();
                }
            }

            $contact = null;
            if ($request->has('contact')) {
                $contact = Contact::query()->where('ContactID', $request->contact)->where('PartyID', '0')->first();
                if ($contact instanceof Contact) {
                    $email = $contact->Email;
                }
            }

            $StaticLists = StaticListsService::getList([StaticListsService::Industries, StaticListsService::MarketingModes, StaticListsService::CustomerType]);

            $view = ($request->type === LeadTypeEnum::Individual->name) ? 'crm.leads.create-individual' : 'crm.leads.create-corporate';

            return view($view, compact('contact', 'email'))
                ->with('Countries', Country::query()->select(['Name', 'CountryCode', 'Id', 'PhoneCode', 'Flag'])->whereHas('localities')->orderBy('t_Countries.Name')->get())
                ->with('conversation', $emailConversation instanceof EmailConversation ? $emailConversation->Id : 0)
                ->with('Industries', $StaticLists->where('CodeID', StaticListsService::Industries))
                ->with('CustomerTypes', $StaticLists->where('CodeID', StaticListsService::CustomerType))
                ->with('MarketingModes', $StaticLists->where('CodeID', StaticListsService::MarketingModes));
        }

        return $this->errored('unknown lead type');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(NewLeadRequest $request): JsonResponse
    {
        $this->authorize('create', Lead::class);
        $location = $request->getLocation();
        $Industry = $request->getIndustry();
        $CustomerType = $request->getCustomerType();
        $Source = $request->getSource();
        $assignee = $request->getAssignee();
        $contacted = $request->getLastContacted() ?? now();
        $gender = $request->getGender();
        $phone = $request->getPhoneNumber();

        $emailConversation = null;
        if ($request->has('conversation')) {
            $emailConversation = EmailConversation::query()->where('Id', $request->conversation)->first();
        }
        $contact = null;
        if ($request->has('contact')) {
            $contact = Contact::query()->where('ContactID', $request->contact)->where('PartyID', '0')->first();
        }

        try {
            $lead = DB::transaction(static function () use ($request, $gender, $contact, $contacted, $CustomerType, $Source, $Industry, $location, $assignee, $emailConversation, $phone) {
                if ($request->validated('Type') === LeadTypeEnum::Company->value) {
                    $service = LeadService::company($request->validated('Name'), $request->validated('Email') ?? '', $phone, $request->validated('Website') ?? '', $contacted, $assignee, $request->user(), $location, $Industry, $Source, $CustomerType, $request->validated('Notes') ?? '');
                } elseif ($request->validated('Type') === LeadTypeEnum::Individual->value) {
                    $service = LeadService::individual($request->validated('Name'), $request->validated('Surname'), $request->validated('Email') ?? '', $phone ?? '', $request->validated('JobTitle') ?? '', $contacted, $gender, $assignee, $request->user(), $location, $Industry, $Source, $CustomerType, $request->validated('Notes') ?? '');
                } else {
                    throw new ErroredException();
                }

                $image = $request->getImage();
                if ($image instanceof UploadedFile) {
                    $service->lead->setImage($image, $request->user(), 'ImageId');
                }

                if ($emailConversation instanceof EmailConversation) {
                    $emailConversation->update([
                        'Party' => Lead::getPrimaryKey(),
                        'PartyID' => $service->lead->LeadID,
                    ]);
                    $emailConversation->emails()->update([
                        'Party' => Lead::getPrimaryKey(),
                        'PartyID' => $service->lead->LeadID,
                    ]);
                }

                if ($contact instanceof Contact) {
                    $contact->crmmails()->update([
                        'Party' => Lead::getPrimaryKey(),
                        'PartyID' => $service->lead->LeadID,
                    ]);

                    $contact->crmsms()->update([
                        'Party' => Lead::getPrimaryKey(),
                        'PartyID' => $service->lead->LeadID,
                    ]);

                    $contact->calls()->update([
                        'Party' => Lead::getPrimaryKey(),
                        'PartyID' => $service->lead->LeadID,
                    ]);

                    $contact->update([
                        'Party' => Lead::getPrimaryKey(),
                        'PartyID' => $service->lead->LeadID,
                    ]);
                    $contact->delete();
                }

                return $service->lead;
            });
        } catch (ErroredException $e) {
            return $e->toJson();
        } catch (Throwable|Exception $e) {
            Log::error('Error adding lead ' . $e->getMessage());
            return $this->errored('unexpected error adding lead, try again latter');
        }

        return $this->succeeded('lead created', route('leads.show', $lead->LeadID));
    }

    public function update(NewLeadRequest $request, Lead $lead): JsonResponse
    {
        try {
            $request->save($request->user(), $lead);
        } catch (Exception|Throwable$e) {
            Log::error('Error updating lead ' . $e->getMessage());
            return $this->errored('unexpected error, try again latter');
        }

        return $this->succeeded('updated successfully', route('leads.show', $lead->LeadID));
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $lead_id): RedirectResponse|View
    {
        $lead = Lead::where('LeadID', $lead_id)->withTrashed()->with(['country', 'location', 'creator'])->first();
        if (!$lead instanceof Lead) {
            return redirect()->back()->with('fail', 'invalid lead.');
        }

        $this->authorize('view', $lead);
        /*if ($lead->trashed()) {
            return redirect()->back()->with('fail', 'lead already won');
        }*/
        $won = ($lead->Status->value === LeadStatusEnum::Won->value);

        $schedule = null;
        $call = null;
        $meeting = null;
        if (!$won) {
            if (is_numeric($request->call)) {
                $call = Call::query()->where('CallStatusID', CallStatusEnum::SuccessOngoing->value)->where('CallID', $request->get('call'))
                    ->where('PartyID', $lead->LeadID)->where('Party', Lead::getPrimaryKey())
                    ->whereBetween('StartOn', [Carbon::now()->startOfDay(), Carbon::now()->endOfDay()])
                    ->lock('WITH(NOLOCK)')->first();
                if ($call instanceof Call) {
                    activity()->causedBy($request->user())->performedOn($call)->event('start')->log('started a call with lead ' . $lead->LeadID);
                    $schedule = ($call?->schedule instanceof Schedule) ? $call->schedule : null;
                } else {
                    $call = null;
                }
            } elseif (is_numeric($request->meet)) {
                $user = $request->user();
                $meeting = Meeting::query()->where('StatusID', MeetingStatusEnum::Ongoing)->where('MeetingID', $request->get('meet'))
                    ->whereHas('meetingUsers', function (Builder $query) use ($user) {
                        $query->where('UserID', $user->Id);
                    })->whereHas('meetingLeads', function (Builder $query) use ($lead) {
                        $query->where('LeadId', $lead->LeadID);
                    })->whereBetween('StartOn', [Carbon::now()->startOfDay(), Carbon::now()->endOfDay()])->lock('WITH(NOLOCK)')->first();
                if ($meeting instanceof Meeting) {
                    activity()->causedBy($request->user())->performedOn($meeting)->event('start')->log('joined a meeting with lead ' . $lead->LeadID);
                } else {
                    $meeting = null;
                }
            } elseif (is_numeric($request->schedule)) {
                $user = $request->user();
                $schedule = Schedule::query()->where('t_Schedule.ScheduleID', $request->schedule)->whereBetween('t_Schedule.StartOn', [Carbon::now()->startOfDay(), Carbon::now()->endOfDay()])
                    ->whereHas('scheduleLeads', function (Builder $query) use ($lead) {
                        $query->where('LeadId', $lead->LeadID);
                    })->whereHas('scheduleUsers', function (Builder $query) use ($user) {
                        $query->where('UserID', $user->Id);
                        $query->where('ScheduleUserStatus', ScheduleUserStatusEnum::Accepted);
                    })->where('t_Schedule.ScheduleStatusID', '!=', ScheduleStatusEnum::Success)->lock('WITH(NOLOCK)')->first();
                if ($schedule instanceof Schedule) {
                    activity()->causedBy($request->user())->performedOn($schedule)->event('view')->log('View lead for a schedule.');
                } else {
                    $schedule = null;
                }
            }
        }

        $StaticLists = StaticListsService::getList([StaticListsService::Industries, StaticListsService::MarketingModes, StaticListsService::CustomerType, StaticListsService::LeadLossReason, StaticListsService::TicketCategories]);
        return view('crm.leads.show', compact('lead', 'schedule', 'call', 'meeting'))
            ->with('MarketingListMember', $lead->marketingLists()->where('Type', MarketingListEnum::Static->value)->select(['slug', 'Label'])->get())
            ->with('Countries', Country::query()->select(['Name', 'CountryCode', 'Id', 'PhoneCode', 'Flag'])->whereHas('localities')->orderBy('t_Countries.Name')->get())
            ->with('location', ($lead->location instanceof Locality) ? (new LocalityService($lead->location))->getLocation() : '')
            ->with('Industries', $StaticLists->where('CodeID', StaticListsService::Industries))
            ->with('CustomerTypes', $StaticLists->where('CodeID', StaticListsService::CustomerType))
            ->with('TicketCategories', $StaticLists->where('CodeID', StaticListsService::TicketCategories))
            ->with('MarketingModes', $StaticLists->where('CodeID', StaticListsService::MarketingModes))
            ->with('LossReasons', $StaticLists->where('CodeID', StaticListsService::LeadLossReason))
            ->with('won', $won);
    }

    public function edit(Request $request, Lead $lead): View
    {
        return view('crm.leads.edit', compact('lead'));/*
            ->with('branches', Branch::query()->select(['OurBranchID as value', 'BranchName as name'])->get())
            ->with('memberClasses', SystemCodeDetail::query()->where('ID', 'MemberClassID')->select(['SubCodeID as value', 'Description as name'])->get())
            ->with('countries', DB::connection('brcbs')->table('t_Country')->select(['CountryID', 'CountryName'])->get())*/
    }

    public function summary($lead_id): View|JsonResponse
    {
        $lead = Lead::where('LeadID', $lead_id)->withTrashed()->first();
        abort_unless($lead instanceof Lead, 404);
        /* if ($lead->trashed()) {
             return $this->errored('lead already won');
         }*/
        $activities = $lead->activities()->latest('ActivityID')->limit(5)->get();
        return view(
            'crm.leads.summary',
            compact('lead', 'activities')
        );
    }
}
