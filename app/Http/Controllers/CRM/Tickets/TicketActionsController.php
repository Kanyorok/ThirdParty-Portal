<?php

namespace App\Http\Controllers\CRM\Tickets;

use App\Enums\TicketPriorityEnum;
use App\Enums\TicketStatusEnum;
use App\Exceptions\ErroredException;
use App\Http\Controllers\Controller;
use App\Http\Requests\DMS\UploadDocumentRequest;
use App\Http\Requests\Ticket\NewTicketRequest;
use App\Models\CRM\Ticket;
use App\Services\DMS\ImageService;
use App\Traits\Controller\ActivitiesTrait;
use App\Traits\Controller\TicketsTrait;
use App\Traits\Controller\WorkflowTrait;
use ErrorException;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class TicketActionsController extends Controller
{
    use TicketsTrait;
    use WorkflowTrait;
    use ActivitiesTrait;

    public function __construct()
    {
        $this->middleware('ajax');
    }

    /**
     * @throws Exception|AuthorizationException
     */
    public function activity(Ticket $ticket): JsonResponse
    {
        $this->authorize('view', $ticket);

        return $this->activities($ticket->userActivities(), ['causer']);
    }

    /**
     * @throws Exception|AuthorizationException
     */
    public function workflow(Ticket $ticket): JsonResponse
    {
        $this->authorize('view', $ticket);

        return $this->workflows($ticket->workflows());
    }

    /**
     * @throws ValidationException
     * @throws AuthorizationException
     */
    public function assignee(Request $request, Ticket $ticket): JsonResponse
    {
        $this->authorize('update', $ticket);
        $assignee = (new NewTicketRequest())->getAssignee($request->validate(['ticket_user' => ['required', 'string']])['ticket_user']);

        try {
            DB::transaction(function () use ($assignee, $ticket) {
                $this->service($ticket)->assign($assignee);
            });
        } catch (ErroredException $e) {
            return $e->toJson();
        } catch (Exception|Throwable $e) {
            Log::error('Error update ticket assignee ' . $e->getMessage());
            return $this->errored('unexpected error, try again later');
        }

        return $this->succeeded('ticket assignee changed', route('tickets.show', [$ticket->TicketID]));
    }

    /**
     * @throws ValidationException
     * @throws AuthorizationException
     */
   public function priority(Request $request, Ticket $ticket): JsonResponse
{
    $this->authorize('update', $ticket);

    $request->validate([
        'ticket_priority' => ['required', Rule::enum(TicketPriorityEnum::class)],
    ]);

    try {
        $priority = TicketPriorityEnum::from($request->get('ticket_priority'));
    } catch (ValueError $e) {
        throw ValidationException::withMessages([
            'ticket_priority' => 'Invalid priority value.',
        ]);
    }

    if ($ticket->Status !== TicketStatusEnum::Active) {
        return $this->errored('Ticket is not active');
    }

    try {
        DB::transaction(function () use ($priority, $ticket) {
            $this->service($ticket)->priority($priority);
        });
    } catch (ErroredException $e) {
        return $e->toJson();
    } catch (Exception|Throwable $e) {
        Log::error('Error updating ticket priority: ' . $e->getMessage());
        return $this->errored('Unexpected error, try again later');
    }

    return $this->succeeded('Ticket priority changed', route('tickets.show', [$ticket->TicketID]));
}


    /**
     * @throws AuthorizationException
     */
    public function resolved(Request $request, Ticket $ticket): JsonResponse
    {
        $this->authorize('update', $ticket);
        $request->validate(['resolve_comment' => ['nullable', 'string', 'max:250']]);

        $comment = $request->resolve_comment ?? null;
        $actor = $request->user();
        if ($ticket->Status->value !== TicketStatusEnum::Active->value) {
            return $this->errored('ticket is not active');
        }

        try {
            DB::transaction(function () use ($comment, $actor, $ticket) {
                $service = $this->service($ticket)->resolve($actor);
                if (is_string($comment)) {
                    $service->comment($comment, $actor);
                }
            });
        } catch (ErroredException $e) {
            return $e->toJson();
        } catch (Exception|Throwable $e) {
            Log::error('Error update ticket priority ' . $e->getMessage());
            return $this->errored('unexpected error, try again later');
        }

        return $this->succeeded('ticket resolved.', route('tickets.show', [$ticket->TicketID]));
    }


    /**
     * @throws AuthorizationException
     */
    public function upload(UploadDocumentRequest $request, Ticket $ticket): JsonResponse
    {
        $this->authorize('update', $ticket);

        try {
            $document = DB::transaction(function () use ($request, $ticket) {
                return $this->service($ticket)->document($request->file('file'), $request->user());
            });
        } catch (ErroredException $e) {
            return $e->toJson();
        } catch (Exception|Throwable $e) {
            Log::error('Error upload ticket document : ' . $e->getMessage());
            return $this->errored('unexpected error, try again later');
        }

        return $this->succeeded('document uploaded successfully', data: [
            'html' => (new ImageService($document))->summaryList(),
        ]);
    }
}
