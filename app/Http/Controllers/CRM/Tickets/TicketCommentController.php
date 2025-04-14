<?php

namespace App\Http\Controllers\CRM\Tickets;

use App\Enums\TicketStatusEnum;
use App\Exceptions\ErroredException;
use App\Http\Controllers\Controller;
use App\Http\Resources\CommentCollection;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Ticket;
use App\Traits\Controller\TicketsTrait;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TicketCommentController extends Controller
{
    use TicketsTrait;

    public function __construct()
    {
        $this->middleware('ajax');
    }

    /**
     * Display a listing of the resource.
     * @throws AuthorizationException
     */
    public function index(Ticket $ticket): CommentCollection
    {
        $this->authorize('view', $ticket);
        return new CommentCollection($ticket->comments()->with('creator')->latest('t_Comments.Id')->paginate(10));
    }


    /**
     * Store a newly created resource in storage.
     * @throws AuthorizationException
     */
    public function store(Request $request, Ticket $ticket): JsonResponse|CommentResource
    {
        $this->authorize('view', $ticket);
        if (!in_array($ticket->Status->value, [TicketStatusEnum::Active->value, TicketStatusEnum::Approval->value], true)) {
            return $this->errored('ticket is not open.');
        }
        $request->validate([
                            'social_comment' => [
                                                 'required',
                                                 'max:5000',
                                                ],
                           ]);

        try {
            $comment = DB::transaction(function () use ($request, $ticket) {
                return $this->service($ticket)->comment($request->get('social_comment'), $request->user());
            });
        } catch (ErroredException $e) {
            return $e->toJson();
        } catch (Exception $e) {
            Log::error('Error create ticket comment ' . $e->getMessage());
            return $this->errored('unexpected error, try again later');
        }

        return $this->succeeded('comment added', data: ['data' => new CommentResource($comment)]);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Ticket $ticket, $comment_id): JsonResponse
    {
        $comment = $ticket->comments()->where('CreatedBy', $request->user()->Id)->where('t_Comments.Id', $comment_id)->first();

        if (!$comment instanceof Comment) {
            return $this->errored('comment not found');
        }

        if (
            $comment->forceFill([
                                 'DeletedOn' => now(),
                                 'DeletedBy' => $request->user()->Id,
                                ])->save()
        ) {
            return $this->succeeded('comment trashed successfully', data: ['id' => $comment_id]);
        }

        return $this->errored('unexpected error, try again later');
    }
}
