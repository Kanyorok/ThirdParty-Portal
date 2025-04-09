<?php

namespace App\Http\Controllers\Settings;

use App\Events\DebtCollection\BulkNotificationEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\UserBulkNotificationRequest;
use App\Models\BulkNotification;
use App\Models\Team;
use App\Models\User;
use App\Services\TeamService;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TeamMessagingController extends Controller
{
    public function __construct()
    {
        $this->middleware('ajax');
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(UserBulkNotificationRequest $request, Team $team)
    {
        $this->authorize('messaging', User::class);
        if($team->users()->count() === 0) {
            return $this->errored('no users found in the team');
        }
        $actor = $request->user();
        try {
            $Bulk =  DB::transaction(static function () use ($team, $request, $actor) {
                $Bulk = BulkNotification::create([
                    'Label' => $request->validated('NotificationLabel'),
                    'Module' => TeamService::MODULE,
                    'Content' => $request->validated('NotificationContent'),
                    'Total' => $team->users()->count(),
                    'Extra' => ['TeamID' => $team->TeamID],
                    'CreatedBy' => $actor->Id,
                    'ModifiedBy' => $actor->Id,
                ]);

                //run event to start work.
                event(new BulkNotificationEvent($Bulk, $actor, ['team'=>$team], now()));
                return $Bulk;
            });
        } catch (\Throwable|\Exception $e) {
            Log::error('Error sending board bulk notification : ' . $e->getMessage());
            return $this->errored('unexpected error, try again later');
        }

        return $this->succeeded(number_format($Bulk->Total) . ' notifications to be sent');
    }
}
