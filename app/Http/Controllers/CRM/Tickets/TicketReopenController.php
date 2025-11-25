<?php

namespace App\Http\Controllers\CRM\Tickets;

use App\Exceptions\ErroredException;
use App\Http\Controllers\Controller;
use App\Models\CRM\Ticket;
use App\Services\CRM\TicketService;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class TicketReopenController extends Controller
{
    public function __construct()
    {
        $this->middleware('ajax');
    }

    /**
     * Update the specified resource in storage.
     * @throws AuthorizationException
     */
    public function update(Request $request, Ticket $ticket): JsonResponse
    {
        //$this->authorize('approve', $ticket); todo tests
        $actor = $request->user();

        try {
            DB::transaction(static function () use ($ticket, $actor) {
                (new TicketService($ticket))->workflowApprove($actor);
            });
        } catch (ErroredException $e) {
            return $e->toJson();
        } catch (Exception|Throwable $e) {
            Log::error('Error approve ticket reopen failed: ' . $e->getMessage());
            return $this->errored('unexpected error, try again later');
        }

        return $this->succeeded('ticket reopen.', route('tickets.show', [$ticket->TicketID]));
    }

    /**
     * Remove the specified resource from storage.
     * @throws AuthorizationException
     */
    public function destroy(Request $request, Ticket $ticket): JsonResponse
    {
        $this->authorize('approve', $ticket);
        $actor = $request->user();
        $data = $request->validate([
                                    'ticket_reject_reason' => [
                                                               'required',
                                                               'string',
                                                               'min:15',
                                                               'max:2000',
                                                              ],
                                   ]);

        try {
            DB::transaction(static function () use ($ticket, $actor, $data) {
                (new TicketService($ticket))->workflowReject($actor, $data['ticket_reject_reason']);
            });
        } catch (ErroredException $e) {
            return $e->toJson();
        } catch (Exception $e) {
            Log::error('Error reject ticket reopen failed: ' . $e->getMessage());
            return $this->errored('unexpected error, try again later');
        }

        return $this->succeeded('ticket reopen rejected', route('tickets.show', [$ticket->TicketID]));
    }
}
