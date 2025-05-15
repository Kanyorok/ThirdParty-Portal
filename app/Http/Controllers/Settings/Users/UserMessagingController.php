<?php

namespace App\Http\Controllers\Settings\Users;

use App\Events\DebtCollection\BulkNotificationEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\UserBulkNotificationRequest;
use App\Models\Auth\User;
use App\Models\Communication\BulkNotification;
use App\Services\HRM\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserMessagingController extends Controller
{
    public function __construct()
    {
        $this->middleware('ajax');
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(UserBulkNotificationRequest $request): JsonResponse
    {
        $this->authorize('messaging', User::class);

        $actor = $request->user();
        try {
            $Bulk =  DB::transaction(static function () use ($request, $actor) {
                $Bulk = BulkNotification::create([
                                                  'Label'      => $request->validated('NotificationLabel'),
                                                  'Module'     => UserService::MODULE,
                                                  'Content'    => $request->validated('NotificationContent'),
                                                  'Total'      => User::query()->count(),
                                                  'CreatedBy'  => $actor->Id,
                                                  'ModifiedBy' => $actor->Id,
                                                 ]);

                //run event to start work.
                event(new BulkNotificationEvent($Bulk, $actor, [], now()));
                return $Bulk;
            });
        } catch (\Throwable | \Exception $e) {
            Log::error('Error sending board bulk notification : ' . $e->getMessage());
            return $this->errored('unexpected error, try again later');
        }

        return $this->succeeded(number_format($Bulk->Total) . ' notifications to be sent');
    }
}
