<?php

namespace App\Http\Controllers\CRM\Board;

use App\Events\DebtCollection\BulkNotificationEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Board\BulkNotificationRequest;
use App\Models\Communication\BulkNotification;
use App\Models\ThirdParies\Board;
use App\Traits\Controller\BulkNotificationTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BoardNotificationController extends Controller
{
    use BulkNotificationTrait;

    public const MODULE = 'BOARD';

    public function __construct()
    {
        $this->middleware('ajax');
    }

    public function __invoke(BulkNotificationRequest $request)
    {
        $this->authorize('viewAny', Board::class);
        $committee = $request->getCommittee();
        $actor = $request->user();

        try {
            $Bulk = DB::transaction(static function () use ($request, $committee, $actor) {
                $Bulk = BulkNotification::create([
                                                  'Label' => $request->validated('NotificationLabel'),
                                                  'Module' => self::MODULE,
                                                  'Content' => $request->validated('NotificationContent'),
                                                  'Total' => $committee->members()->count(),
                                                  'Extra' => ['CommitteeID' => $committee->CommitteeID],
                                                  'CreatedBy' => $actor->Id,
                                                  'ModifiedBy' => $actor->Id,
                                                 ]);

                //run event to start work.
                event(new BulkNotificationEvent($Bulk, $actor, ['committee' => $committee], now()));

                activity()->causedBy($actor)->performedOn($committee)->event('sent message')->log('sent message to board committee:  ' . $Bulk->Label . '.');

                return $Bulk;
            });
        } catch (\Throwable | \Exception $e) {
            Log::error('Error sending board bulk notification : ' . $e->getMessage());

            return $this->errored('unexpected error, try again later');
        }

        return $this->succeeded(number_format($Bulk->Total) . ' notifications to be sent');
    }
}
