<?php

namespace App\Http\Controllers\CRM\Tickets;

use App\Enums\Core\RoleEnum;
use App\Enums\TicketPriorityEnum;
use App\Enums\TicketStatusEnum;
use App\Exceptions\ErroredException;
use App\Helpers\SystemHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Ticket\NewTicketRequest;
use App\Models\Team;
use App\Models\Ticket;
use App\Models\User;
use App\Services\StaticListsService;
use App\Services\TicketService;
use App\Traits\Controller\TicketsTrait;
use Carbon\Carbon;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TicketController extends Controller
{
    use TicketsTrait;

    public function __construct()
    {
        $this->middleware('ajax')->except(['index', 'show']);
    }

    /**
     * All tickets.
     *
     * @throws Exception
     */
    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            $actor = $request->user();
            $query = Ticket::query();
            //ownership
            if (($request->get('_user') === $actor->UserID) || (!$request->user()->can('viewAny', Ticket::class))) {
                $query->where(function (Builder $query) use ($actor) {
                    $query->where(function (Builder $query) use ($actor) {
                        $query->where('t_Tickets.Owner', User::getPrimaryKey())->where('t_Tickets.OwnerID', $actor->Id);
                    })->orWhere(function (Builder $query) use ($actor) {
                        $query->where('t_Tickets.Owner', Team::getPrimaryKey())
                            ->whereIn('t_Tickets.OwnerID', $actor->teamUser()->select('t_TeamUser.TeamId'));
                        // dd($actor->teams()->select('t_TeamUser.TeamId')->get('TeamId'));
                    })->orWhere('t_Tickets.CreatedBy', $actor->Id)->orWhere(function (Builder $query) use ($actor) {
                        $query->where('t_Tickets.Party', User::getPrimaryKey())->where('t_Tickets.PartyID', $actor->Id);
                    })->orWhereHas('watchers', function (Builder $query) use ($actor) {
// 'Party', 'PartyID'
                        $query->where(function (Builder $query) use ($actor) {
                            $query->where('t_TicketUsers.PartyID', $actor->Id)->where('t_TicketUsers.Party', User::getPrimaryKey());
                        })->orWhere(function (Builder $query) use ($actor) {
                            $query->where('t_TicketUsers.Party', Team::getPrimaryKey())->whereIn('t_TicketUsers.PartyID', $actor->teams()->select('t_Teams.TeamID'));
                        });
                    });
                });
            } elseif ($request->get('_user') === 'none') {
                $query->where(function (Builder $query) {
                    $query->where('t_Tickets.Owner', User::getPrimaryKey())->where('t_Tickets.OwnerID', SystemHelper::user()->Id);
                });
            } elseif ($request->get('_user') !== 'all') {
                return $this->errored('Invalid Ownership filter given');
            }


            if ($request->get('_status') === 'all') {
                $query->whereIn('t_Tickets.Status', TicketStatusEnum::values());
            } else {
                try {
                    $status = TicketStatusEnum::fromValue($request->get('_status'));
                    $query->where('t_Tickets.Status', $status->value);
                } catch (Exception) {
                    throw new ErroredException('Invalid Status filter given');
                }
            }

            if ($request->get('_priority') === 'all') {
                $query->whereIn('t_Tickets.Priority', TicketPriorityEnum::values());
            } else {
                try {
                    $status = TicketPriorityEnum::fromValue($request->get('_priority'));
                    $query->where('t_Tickets.Priority', $status->value);
                } catch (Exception) {
                    throw new ErroredException('Invalid Priority filter given');
                }
            }

            return $this->tickets($query, ['party', 'category']);
        }

        return view('crm.tickets.index')
            ->with('TicketCategories', StaticListsService::getList(StaticListsService::TicketCategories));
    }

    /**
     * Store a newly created resource in storage.
     * @throws ValidationException
     */
    public function store(NewTicketRequest $request): JsonResponse
    {
        $owner = $request->user();
        $category = $request->getCategory();
        $source = $request->getSource();
        $priority = $request->getPriority();
        $start = $request->getStart();
        $end = ($start instanceof Carbon) ? $request->getEnd($start) : null;//here because it throws validation avoid try catch.
        $assignee = $request->getAssignee();
        $watchers = $request->getWatchers();
        if ($assignee->Id === $owner->Id) {
            throw ValidationException::withMessages(['ticket_user' => 'you cannot assign yourself, your ticket.']);
        }
        $exists = $owner->tickets()->where('t_Tickets.CategoryID', $category->ID)->where('t_Tickets.Status', TicketStatusEnum::Active->value)->first();
        if ($exists instanceof Ticket) {
            return $this->errored('Ticket <a href="' . route('tickets.show', [$exists->TicketID]) . '" class="fw-bold text-white">' . $exists->TicketID . '</a> of the same category already exists.');
        }

        try {
            $service = DB::transaction(function () use ($owner, $request, $category, $source, $priority, $start, $end, $assignee, $watchers) {
                $service = $this->save($owner, $category, $request->validated('ticket_title'), $request->validated('ticket_description'), $owner, $source, $priority, $start, $end)
                    ->assign($assignee);
                foreach ($watchers as $watcher) {
                    if ($watcher instanceof Team && $assignee instanceof Team && $watcher->TeamID === $assignee->TeamID) {
                        continue;
                    }

                    if ($watcher instanceof User && $assignee instanceof User && ($watcher->Id === $assignee->Id || $watcher->Id === $owner->Id)) {
                        continue;
                    }

                    $service->addWatcher($watcher, RoleEnum::Read, $owner);
                }
                return $service;
            });
        } catch (ErroredException $e) {
            return $e->toJson();
        } catch (Exception $e) {
            Log::error('Error creating User ticket ' . $e->getMessage());
            return $this->errored('unexpected error creating ticket, try again later');
        }

        return $this->succeeded('ticket created', route('tickets.show', [$service->ticket->TicketID]));
    }

    /**
     * Ticket Summary
     * @throws AuthorizationException
     */
    public function edit(Ticket $ticket): View
    {
        $this->authorize('view', $ticket);
        return view('crm.tickets.summary', compact('ticket'))
            ->with('service', new TicketService($ticket))
            ->with('party', $ticket->party);
    }

    /**
     * Display the specified resource.
     * @throws AuthorizationException
     */
    public function show(Request $request, Ticket $ticket): View
    {
        $this->authorize('view', $ticket);

        return view('crm.tickets.show', compact('ticket'))
            ->with('TicketCategories', StaticListsService::getList(StaticListsService::TicketCategories))
            ->with('canApprove', (($ticket->Status->value === TicketStatusEnum::Approval->value) && ((new TicketService($ticket))->canApprove($request->user()))))
            ->with('party', $ticket->party);
    }

    /**
     * Update the specified resource in storage.
     * @throws ValidationException
     * @throws AuthorizationException
     */
    public function update(NewTicketRequest $request, Ticket $ticket): JsonResponse
    {
        $this->authorize('update', $ticket);
        $category = $request->getCategory();
        $start = $request->getStart();
        $end = ($start instanceof Carbon) ? $request->getEnd($start) : null;//here because it throws validation avoid try catch.

        try {
            DB::transaction(function () use ($request, $ticket, $category, $start, $end) {
                $this->service($ticket)->update($category, $request->validated('ticket_title'), $request->validated('ticket_description'), $request->user(), $start, $end);
            });
        } catch (ErroredException $e) {
            return $e->toJson();
        } catch (Exception $e) {
            Log::error('Error update ticket ticket ' . $e->getMessage());
            return $this->errored('unexpected error creating ticket, try again later');
        }

        return $this->succeeded('ticket updated', route('tickets.show', [$ticket->TicketID]));
    }

    /**
     * Remove the specified resource from storage.
     * @throws AuthorizationException
     */
    public function destroy(Request $request, Ticket $ticket): JsonResponse
    {
        $this->authorize('delete', $ticket);
        try {
            DB::transaction(function () use ($request, $ticket) {
                $this->service($ticket)->cancel($request->user());
            });
        } catch (ErroredException $e) {
            return $e->toJson();
        } catch (Exception $e) {
            Log::error('Error update ticket ticket ' . $e->getMessage());
            return $this->errored('unexpected error creating ticket, try again later');
        }

        return $this->succeeded('ticket canceled', route('tickets.index'));
    }

    /**
     * Remove the specified resource from storage.
     * @throws AuthorizationException
     */
    public function restore(Request $request, Ticket $ticket): JsonResponse
    {
        $this->authorize('restore', $ticket);
        $request->validate([
                            'open_reason' => [
                                              'required',
                                              'string',
                                              'min:15',
                                              'max:250',
                                             ],
                           ]);
        if (!in_array($ticket->Status->value, [TicketStatusEnum::Resolved->value, TicketStatusEnum::Cancelled->value], true)) {
            return $this->errored('ticket is not closed');
        }

        try {
            DB::transaction(function () use ($request, $ticket) {
                $this->service($ticket)->reopen($request->user(), $request->open_reason);
            });
        } catch (ErroredException $e) {
            return $e->toJson();
        } catch (Exception $e) {
            Log::error('Error re open ticket ticket ' . $e->getMessage());
            return $this->errored('unexpected error , try again later');
        }

        return $this->succeeded('awaiting approval', route('tickets.show', [$ticket->TicketID]));
    }
}
