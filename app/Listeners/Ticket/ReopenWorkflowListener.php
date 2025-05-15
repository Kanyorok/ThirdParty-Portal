<?php

namespace App\Listeners\Ticket;

use App\Enums\Core\PermissionEnum;
use App\Enums\TicketStatusEnum;
use App\Events\Ticket\ReopenTicketEvent;
use App\Helpers\SystemHelper;
use App\Models\Ticket;
use App\Models\User;
use App\Services\HRM\UserService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;

class ReopenWorkflowListener implements ShouldQueue
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
    public function handle(ReopenTicketEvent $event): void
    {
        $users = User::query()->lock('WITH(NOLOCK)')->hasPermission(PermissionEnum::TicketApproval->value)->get(["Id", "UserID", "Name", "Email"]);
        DB::transaction(function () use ($users, $event) {
            foreach ($users as $user) {
                if (!$user instanceof User) {
                    continue;
                }
                if (in_array($user->UserID, [$event->actor->UserID, SystemHelper::ID], true)) {//skip sys and submitter
                    continue;
                }

                $event->ticket->pendingWorkflows()->lock('WITH(NOLOCK)')->where('Stage', TicketStatusEnum::Approval)->create([
                                                                                                                              'Stage'      => TicketStatusEnum::Approval,
                                                                                                                              'UserId'     => $user->Id,
                                                                                                                              'CreatedBy'  => $event->actor->Id,
                                                                                                                              'ModifiedBy' => $event->actor->Id,
                                                                                                                             ]);

                $this->_sendMail($user, $event->ticket, $event->reason);
            }
        });
    }

    protected function _sendMail(User $user, Ticket $ticket, string $reason): void
    {
        if ($user->email !== 'mureithi.maina@craftsilicon.com') {
            return;
        }
        (new UserService($user))->sendEmail(
            subject: 'Request for Ticket Reopening',
            body: '<p>Hello ' . $user->UserID . '</p><p>A request for the reopening of ticket <b>#' . $ticket->TicketID . '</b> has been submitted for your review. Click the link below to review</p>
                <p><a href="' . route('tickets.show', [$ticket->TicketID]) . '"> ticket details</a></p>
                <p>Reason given: ' . $reason . '</p>',
            immediate: true
        );
    }
}
