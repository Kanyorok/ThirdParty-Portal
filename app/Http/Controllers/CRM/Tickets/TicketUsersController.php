<?php

namespace App\Http\Controllers\CRM\Tickets;

use App\Exceptions\ErroredException;
use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketUsers;
use App\Services\PartyService;
use App\Services\TicketService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\DataTables;

class TicketUsersController extends Controller
{
    public function __construct()
    {
        $this->middleware('ajax');
    }

    /**
     * @throws Exception
     */
    public function index(Request $request, Ticket $ticket): JsonResponse
    {
        $this->authorize('view', $ticket);
        return Datatables::of($ticket->watchers()->with('party')->select('*'))->addIndexColumn()
            ->addColumn('action', function (TicketUsers $tu) use ($ticket, $request) {
                if ($request->user()->can('delete', $ticket)) {
                    return '<button type="button" data-click_url="' . route('ticket-watchers.destroy', [$ticket->TicketID, $tu->Id]) . '" data-info="' . (new PartyService($tu->party))->getName() . '"
                        class="btn btn-danger btn-sm ticket-watchers-trash"><i class="fas fa-trash"></i></button>';
                }
                return '...';
            })->editColumn('party', function (TicketUsers $tu) {
                return (new PartyService($tu->party))->getDTRow();
            })->editColumn('CreatedOn', function (TicketUsers $tu) {
                return $tu->CreatedOn?->format('d M, Y H:i');
            })->editColumn('Role', function (TicketUsers $tu) {
                return $tu->Role->name;
            })->rawColumns(['action', 'party'])->make();
    }

    public function destroy(Request $request, Ticket $ticket, $ticketUser_ID): JsonResponse
    {
        $this->authorize('delete', $ticket);
        $actor = $request->user();
        $ticketUser = $ticket->watchers()->where('t_TicketUsers.Id', $ticketUser_ID)->first();
        if ($ticketUser instanceof TicketUsers) {
            try {
                DB::transaction(static function () use ($ticketUser, $actor, $ticket) {
                    (new TicketService($ticket))->deleteWatcher($ticketUser, $actor);
                });
            } catch (ErroredException $e) {
                return $e->toJson();
            } catch (Exception $e) {
                Log::error('Error remove ticket watcher failed: ' . $e->getMessage());
                return $this->errored('unexpected error, try again later');
            }
        }

        return $this->succeeded('watcher removed successfully');
    }
}
