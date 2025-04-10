<?php

namespace App\Http\Controllers\CRM\Client;

use App\Enums\Core\RoleEnum;
use App\Enums\TicketStatusEnum;
use App\Exceptions\ErroredException;
use App\Helpers\SystemHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Ticket\NewTicketRequest;
use App\Models\BR\Client;
use App\Models\CrmEmail;
use App\Models\CRMImage;
use App\Models\EmailConversation;
use App\Models\Team;
use App\Models\Ticket;
use App\Models\User;
use App\Traits\Controller\TicketsTrait;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use TypeError;

class ClientTicketController extends Controller
{
    use TicketsTrait;

    public function __construct()
    {
        $this->middleware('ajax');
    }

    /**
     * Display a listing of the resource.
     * @throws Exception
     */
    public function index(Request $request, Client $client): JsonResponse
    {
        $query = $client->tickets();
        if ($request->get('_status') === 'all') {
            $query->whereIn('t_Tickets.Status', TicketStatusEnum::  values());
        } else {
            try {
                $status = TicketStatusEnum::fromValue($request->get('_status'));
                $query->where('t_Tickets.Status', $status->value);
            } catch (Exception|TypeError) {
                throw new ErroredException('Invalid Status filter given');
            }
        }

        return $this->tickets($query);
    }

    /**
     * Store a newly created resource in storage.
     * @throws ValidationException
     */
    public function store(NewTicketRequest $request, Client $client): JsonResponse
    {
        $category = $request->getCategory();
        $priority = $request->getPriority();
        $source = $request->getSource();
        $start = $request->getStart();
        $end = ($start instanceof Carbon) ? $request->getEnd($start) : null;//here because it throws validation avoid try catch.
        $exists = $client->tickets()->where('t_Tickets.CategoryID', $category->ID)->where('t_Tickets.Status', TicketStatusEnum::Active->value)->first();
        if ($exists instanceof Ticket) {
            return $this->errored('Ticket <a href="' . route('tickets.show', [$exists->TicketID]) . '" class="fw-bold text-white">' . $exists->TicketID . '</a> of the same category already exists.');
        }
        $watchers = $request->getWatchers();
        $assignee = $request->getAssignee();
        $owner = $request->user();
        $emailConversation = null;
        if($request->has('conversation')){
            $emailConversation = EmailConversation::query()->where('Id',$request->conversation)->first();
        }

        try {
            $service = DB::transaction(function () use ($client, $owner, $request, $category, $source, $priority, $start, $end, $assignee, $watchers, $emailConversation) {
                $service = ($emailConversation instanceof EmailConversation)
                    ? $this->save($client, $category, $request->validated('ticket_title'), $request->validated('ticket_description'), $owner, CrmEmail::getPrimaryKey(), $priority, $start, $end, SourceID: $emailConversation->email->EmailID)
                    : $this->save($client, $category, $request->validated('ticket_title'), $request->validated('ticket_description'), $owner, $source, $priority, $start, $end);

                    $service->assign($assignee);

                foreach ($watchers as $watcher) {
                    if ($watcher instanceof Team && $assignee instanceof Team && $watcher->TeamID === $assignee->TeamID) {
                        continue;
                    }

                    if ($watcher instanceof User && $assignee instanceof User && ($watcher->Id === $assignee->Id || $watcher->Id === $owner->Id)) {
                        continue;
                    }

                    $service->addWatcher($watcher, RoleEnum::Read, $owner);
                }

                if( ($emailConversation instanceof EmailConversation)){//attach documents in email to ticket
                    foreach ($emailConversation->email->attachments as  $attachment){
                        if ($attachment instanceof CRMImage){
                            $service->documentContent($attachment->Image,$attachment->MIMEType,$attachment->Name,SystemHelper::user());
                        }
                    }

                }
                return $service;
            });
        } catch (ErroredException $e) {
            return $e->toJson();
        } catch (\Throwable|Exception $e) {
            Log::error('Error creating Client ticket ' . $e->getMessage());
            return $this->errored('unexpected error creating ticket, try again later');
        }

        return $this->succeeded('ticket created', route('tickets.show', [$service->ticket->TicketID]));
    }
}
