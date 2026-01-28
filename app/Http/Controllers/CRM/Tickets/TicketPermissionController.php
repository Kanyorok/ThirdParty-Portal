<?php

namespace App\Http\Controllers\CRM\Tickets;

use App\Exceptions\ErroredException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Base\SharePartyRequest;
use App\Models\Core\SpecialPermission;
use App\Models\CRM\Ticket;
use App\Services\CRM\TicketService;
use App\Traits\Controller\SpecialPermissionTrait;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class TicketPermissionController extends Controller
{
    use SpecialPermissionTrait;

    public function __construct()
    {
        $this->middleware('ajax');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, Ticket $ticket): JsonResponse
    {
        $this->authorize('view', $ticket);

        return $this->permissions($ticket->permissions(), $request->user()->can('delete', $ticket));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SharePartyRequest $request, Ticket $ticket): JsonResponse
    {
        $this->authorize('share', $ticket);
        $assignee = $request->getParty();
        $permission = $request->getRole();

        try {
            return DB::transaction(function () use ($ticket, $assignee, $permission, $request) {
                (new TicketService($ticket))->addWatcher($assignee, $permission, $request->user());

                return $this->succeeded('ticket watcher added');
            });
        } catch (ErroredException $e) {
            return $e->toJson();
        } catch (Throwable $e) {
            Log::error("Error adding ticket watcher: " . $e->getMessage());

            return $this->errored('unexpected error, try again later');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Ticket $ticket, int $permission_id): JsonResponse
    {
        $this->authorize('share', $ticket);
        $specialPermission = $ticket->permissions()->where('Id', $permission_id)->first();
        if (! $specialPermission instanceof SpecialPermission) {
            return $this->errored('watcher not found');
        }

        try {
            return DB::transaction(function () use ($specialPermission, $ticket, $request) {
                (new TicketService($ticket))->deleteWatcher($specialPermission, $request->user());

                return $this->succeeded('ticket watchers updated');
            });
        } catch (ErroredException $e) {
            return $e->toJson();
        } catch (Throwable $e) {
            Log::error("Error remove ticket watcher: " . $e->getMessage());

            return $this->errored('unexpected error, try again later');
        }
    }

    protected function _trashRoute(SpecialPermission $permission): string
    {
        return route('ticket-watchers.destroy', [$permission->model->TicketID, $permission->Id]);
    }
}
