<?php

namespace App\Listeners\ThirdParty;

use App\Events\ThirdParty\ProfileApprovalStatusChanged;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class NotifyProfileApprovalStatusChangedListener
{
    public function handle(ProfileApprovalStatusChanged $event): void
    {
        Log::info('Profile approval status changed', [
            'supplier_id' => $event->supplier->Id,
            'third_party_id' => $event->supplier->ThirdPartyId,
            'old_status' => $event->oldStatus->value,
            'new_status' => $event->newStatus->value,
            'approved_by' => $event->approvedBy,
        ]);

        // TODO: Send email/SMS notification to user
        // if ($event->newStatus === ThirdPartyApprovalStatusEnum::Approved) {
        //     $user = $event->supplier->party->users()->first();
        //     if ($user) {
        //         $user->notify(new ProfileApprovedNotification($event->supplier));
        //     }
        // }
    }
}
