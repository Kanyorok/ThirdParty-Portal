<?php

namespace App\Listeners\Marketing;

use App\Enums\CampaignStatusEnum;
use App\Enums\Core\PermissionEnum;
use App\Events\Marketing\CampaignSubmittedEvent;
use App\Helpers\SystemHelper;
use App\Models\Campaign;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;

class CampaignSubmittedWorkflowListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(CampaignSubmittedEvent $event): void
    {
        $users = User::query()->lock('WITH(NOLOCK)')->hasPermission(PermissionEnum::CampaignApproval->value)->get(["Id", "UserID", "Name", "Email"]);
        DB::transaction(function () use ($users, $event) {
            foreach ($users as $user) {
                if (!$user instanceof User) {
                    continue;
                }
                if (in_array($user->UserID, [$event->actor->UserID, SystemHelper::ID], true)) {//skip sys and submitter
                    continue;
                }

                $event->campaign->pendingWorkflows()->lock('WITH(NOLOCK)')->where('Stage', CampaignStatusEnum::Approval)->create([
                    'Stage' => CampaignStatusEnum::Approval,
                    'UserId' => $user->Id,
                    'CreatedBy' => $event->actor->Id,
                    'ModifiedBy' => $event->actor->Id,
                ]);

                $this->_sendMail($user, $event->campaign);
            }
        });

    }

    protected function _sendMail(User $user, Campaign $campaign): void
    {
        (new UserService($user))->sendEmail(subject: 'Campaign submitted review and approval',
            body: '<p>Hello</p><p>The campaign <b>' . $campaign->Label . '</b> has been submitted for your review. Click the link below to review</p>
                <p><a href="' . route('campaigns.show', [$campaign->CampaignID]) . '"> campaign ' . $campaign->CampaignID . ' details</a></p>
                <p>Kindly review and approve the campaign at your earliest convenience.</p>',
            immediate: true
        );
    }
}
