<?php

namespace App\Http\Controllers\Settings\Users;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;

class UserActionsController extends Controller
{
    public function __construct()
    {
        $this->middleware('ajax');
    }

    /**
     * Send Password reset email.
     * @throws AuthorizationException
     */
    public function sendResetLink(User $user): JsonResponse
    {
        $this->authorize('update', $user);
        $user->sendPasswordResetNotification();

        return $this->succeeded('password reset sent.');
    }

    /**
     * @throws AuthorizationException
     */
    public function syncPassword(User $user): JsonResponse
    {
        $this->authorize('update', $user);
        (new UserService($user))->syncBR();

        return $this->succeeded('password sync successful.');
    }
}
