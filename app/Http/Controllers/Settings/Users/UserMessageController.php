<?php

namespace App\Http\Controllers\Settings\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\Base\MessageRequest;
use App\Models\Auth\User;
use App\Services\HRM\UserService;
use App\Services\SMSService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class UserMessageController extends Controller
{
    public function __construct()
    {
        $this->middleware('ajax');
    }


    /**
     * Display a listing of the resource.
     * @throws \Exception
     */
    public function index(User $user): JsonResponse
    {
        return SMSService::dt($user->crmsms(), ['source']);
    }

    /**
     * Store a newly created resource in storage.
     * @throws ValidationException
     */
    public function store(MessageRequest $request, User $user): JsonResponse
    {
        $request->getUserPhone($user);
        $actor = $request->user();
        try {
            DB::transaction(static function () use ($user, $actor, $request) {
                (new UserService($user))->sendMessage($request->validated('message_content'), $actor);

                activity()->causedBy($actor)->performedOn($user)->event('sent message')->log('sent direct message to user ' . $user->UserID . '.');
            });
        } catch (\Throwable | \Exception $e) {
            Log::error('Error sending sms to users/staff ' . $e->getMessage());
            return $this->errored('unexpected error, try again later');
        }

        return $this->succeeded('sending message');
    }
}
