<?php

namespace App\Http\Controllers\CRM\Email;

use App\Exceptions\ErroredException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Base\SharePartyRequest;
use App\Models\EmailConversation;
use App\Models\EmailConversationUser;
use App\Services\Email\EmailConversationService;
use App\Services\PartyService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;
use Yajra\DataTables\DataTables;

class EmailConversationUserController extends Controller
{
    public function __construct()
    {
        $this->middleware('ajax');
    }

    /**
     * Display a listing of the resource.
     * @throws Exception
     */
    public function index(Request $request, EmailConversation $conversation): JsonResponse
    {
        $this->authorize('view', $conversation);
        return Datatables::of($conversation->watchers()->with('party')->whereNull(['t_EmailConversationUsers.DeletedOn', 't_EmailConversationUsers.DeletedBy'])->select('*'))->addIndexColumn()
            ->addColumn('action', function (EmailConversationUser $user) use ($conversation, $request) {
                if ($request->user()->can('delete', $conversation)) {
                    return '<button type="button" data-click_url="' . route('conversation-watchers.destroy', [$conversation->Id, $user->Id]) . '" data-info="' . (new PartyService($user->party))->getName() . '"
                        class="btn btn-danger btn-sm conversation-watchers-trash"><i class="fas fa-trash"></i></button>';
                }
                return '...';
            })->editColumn('party', function (EmailConversationUser $user) {
                return (new PartyService($user->party))->getDTRow();
            })->editColumn('CreatedOn', function (EmailConversationUser $user) {
                return $user->CreatedOn?->format('d M, Y H:i');
            })->editColumn('Role', function (EmailConversationUser $user) {
                return $user->Role->name;
            })->rawColumns(['action', 'party'])->make();
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(SharePartyRequest $request, EmailConversation $conversation): JsonResponse
    {
        $this->authorize('update', $conversation);
        $assignee = $request->getParty();
        $role = $request->getRole();
        $actor = $request->user();
        try {
            DB::transaction(static function () use ($actor, $conversation, $assignee, $role) {
                (new EmailConversationService($conversation))->addWatcher($assignee, $role, $actor);
            });
        } catch (ErroredException $e) {
            return $e->toJson();
        } catch (Throwable|Exception $e) {
            Log::error('Error add conversation watcher failed: ' . $e->getMessage());
            return $this->errored('unexpected error, try again later');
        }
        return $this->succeeded('shared successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, EmailConversation $conversation, $conversationUser_Id): JsonResponse
    {
        $this->authorize('update', $conversation);
        $actor = $request->user();
        $conversationUser = $conversation->watchers()->where('t_EmailConversationUsers.Id', $conversationUser_Id)->first();

        if ($conversationUser instanceof EmailConversationUser) {
            try {
                DB::transaction(static function () use ($conversation, $conversationUser, $actor) {
                    (new EmailConversationService($conversation))->deleteWatcher($conversationUser, $actor);
                });
            } catch (ErroredException $e) {
                return $e->toJson();
            } catch (Throwable|Exception $e) {
                Log::error('Error remove conversation watcher failed: ' . $e->getMessage());
                return $this->errored('unexpected error, try again later');
            }
        }

        return $this->succeeded('removed successfully');
    }
}
