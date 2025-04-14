<?php

namespace App\Http\Controllers\CRM\Board;

use App\Http\Controllers\Controller;
use App\Http\Requests\Base\MessageRequest;
use App\Models\Board;
use App\Services\BoardService;
use App\Services\SMSService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class BoardMessageController extends Controller
{
    public function __construct()
    {
        $this->middleware('ajax');
    }


    /**
     * Display a listing of the resource.
     * @throws Exception
     */
    public function index(Board $boardMember): JsonResponse
    {
        return SMSService::dt($boardMember->crmsms(), ['source']);
    }

    /**
     * Store a newly created resource in storage.
     * @throws ValidationException
     */
    public function store(MessageRequest $request, $boardMember_Id): JsonResponse
    {
        $boardMember = Board::query()->where('BoardMemberID', $boardMember_Id)->first();
        if (!$boardMember instanceof Board) {
            return $this->errored('board member may be invalid reload page.');
        }

        $request->getBoardMemberPhone($boardMember);
        $actor = $request->user();
        try {
            DB::transaction(static function () use ($boardMember, $actor, $request) {
                (new BoardService($boardMember))->sendMessage($request->validated('message_content'), $actor);
                activity()->causedBy($actor)->performedOn($boardMember)->event('sent message')->log('sent direct message to board member ' . $boardMember->BoardMemberID . '.');
            });
        } catch (\Throwable | Exception $e) {
            Log::error('Error sending sms to board member ' . $e->getMessage());
            return $this->errored('unexpected error, try again later');
        }

        return $this->succeeded('sending message');
    }
}
