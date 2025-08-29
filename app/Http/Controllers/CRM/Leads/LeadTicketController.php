<?php

namespace App\Http\Controllers\CRM\Leads;

use App\Enums\Core\ExtensionsEnum;
use App\Enums\Core\RoleEnum;
use App\Enums\TicketStatusEnum;
use App\Exceptions\ErroredException;
use App\Helpers\SystemHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Ticket\NewTicketRequest;
use App\Models\Auth\Team;
use App\Models\Auth\User;
use App\Models\Communication\Email;
use App\Models\Communication\EmailConversation;
use App\Models\CRM\Lead;
use App\Models\CRM\Ticket;
use App\Models\DMS\Image;
use App\Traits\Controller\TicketsTrait;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

class LeadTicketController extends Controller
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
    public function index(Lead $lead): JsonResponse
    {
        return $this->tickets($lead->tickets()->where('t_Tickets.Status', TicketStatusEnum::Active));
    }

    /**
     * Store a newly created resource in storage.
     * @throws ValidationException
     */
    public function store(NewTicketRequest $request, Lead $lead): JsonResponse
    {
        $category = $request->getCategory();
        $source = $request->getSource();
        $priority = $request->getPriority();
        $start = $request->getStart();
        $end = ($start instanceof Carbon) ? $request->getEnd($start) : null;
        $assignee = $request->getAssignee();
        $exists = $lead->tickets()->where('t_Tickets.CategoryID', $category->ID)->where('t_Tickets.Status', TicketStatusEnum::Active->value)->first();
        if ($exists instanceof Ticket) {
            return $this->errored('Ticket <a href="' . route('tickets.show', [$exists->TicketID]) . '" class="fw-bold text-white">' . $exists->TicketID . '</a> of the same category already exists.');
        }
        $owner = $request->user();
        $watchers = $request->getWatchers();
        $emailConversation = null;
        if ($request->has('conversation')) {
            $emailConversation = EmailConversation::query()->where('Id', $request->conversation)->first();
        }

        try {
            $service = DB::transaction(function () use ($lead, $owner, $request, $category, $source, $priority, $start, $end, $assignee, $watchers, $emailConversation) {
                $service = ($emailConversation instanceof EmailConversation)
                    ? $this->save($lead, $category, $request->validated('ticket_title'), $request->validated('ticket_description'), $owner, Email::getPrimaryKey(), $priority, $start, $end, SourceID: $emailConversation->email->EmailID)
                    : $this->save($lead, $category, $request->validated('ticket_title'), $request->validated('ticket_description'), $owner, $source, $priority, $start, $end);

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

                if (($emailConversation instanceof EmailConversation)) {//attach documents in email to ticket
                    foreach ($emailConversation->email->attachments as $attachment) {
                        if ($attachment instanceof Image) {//todo fix on migration.
                            try {
                                $extension = ExtensionsEnum::fromMimeType($attachment->MIMEType);
                            } catch (ErroredException) {
                                continue;
                            }
                            $service->documentContent($attachment->Image, $extension, $attachment->Name, SystemHelper::user());
                        }
                    }
                }
                return $service;
            });
        } catch (ErroredException $e) {
            return $e->toJson();
        } catch (Throwable | Exception $e) {
            Log::error('Error creating ticket ' . $e->getMessage());
            Log::error($e);
            return $this->errored('unexpected error creating ticket, try again later');
        }

        return $this->succeeded('ticket created', route('tickets.show', [$service->ticket->TicketID]));
    }
}
