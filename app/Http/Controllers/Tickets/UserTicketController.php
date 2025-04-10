<?php

namespace App\Http\Controllers\Tickets;

use App\Enums\TicketPriorityEnum;
use App\Enums\TicketStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Traits\Controller\TicketsTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserTicketController extends Controller
{
    use TicketsTrait;

    /**
     * Handle the incoming request.
     * @throws Exception
     */
    public function __invoke(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            $query = Ticket::query();
            if (is_string($request->get('_status')) && $request->get('_status') !== 'all') {
                try {
                    $status = TicketStatusEnum::fromValue($request->get('_status'));
                    $query->where('t_Tickets.Status', $status->value);
                } catch (Exception) {
                }
            }
            if (is_string($request->get('_priority')) && $request->get('_priority') !== 'all') {
                try {
                    $status = TicketPriorityEnum::fromValue($request->get('_priority'));
                    $query->where('t_Tickets.Priority', $status->value);
                } catch (Exception) {
                }
            }

            return $this->tickets($query->where('UserID', $request->user()->Id), ['party', 'category']);
        }

        return view('tickets.user');
    }
}
