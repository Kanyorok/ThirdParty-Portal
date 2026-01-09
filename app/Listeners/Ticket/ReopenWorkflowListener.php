<?php

namespace App\Listeners\Ticket;

use App\Enums\Core\PermissionEnum;
use App\Enums\TicketStatusEnum;
use App\Events\Ticket\ReopenTicketEvent;
use App\Helpers\SystemHelper;
use App\Models\Auth\User;
use App\Models\CRM\Ticket;
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
        // Approval Workflow
        $users = User::query()->lock('WITH(NOLOCK)')->hasPermission(PermissionEnum::TicketApproval->value)
            ->whereNotIn('t_Users.Id', [$event->actor->Id, SystemHelper::user()->Id])
            ->get(["Id", "UserID", "Name", "Email"]);
        DB::transaction(function () use ($users, $event) {
            foreach ($users as $user) {
                if (!$user instanceof User) {
                    continue;
                }

                $event->ticket->pendingWorkflows()->lock('WITH(NOLOCK)')->where('Stage', TicketStatusEnum::Approval)->create([
                    'Stage' => TicketStatusEnum::Approval,
                    'UserId' => $user->Id,
                    'CreatedBy' => $event->actor->Id,
                    'ModifiedBy' => $event->actor->Id,
                ]);

                $this->_sendMail($user, $event->ticket, $event->reason);
            }
        });
    }

    protected function _sendMail(User $user, Ticket $ticket, string $reason): void
    {
        (new UserService($user))->sendEmail(
            subject: 'Request for Ticket Reopening',
            body: '<p>Hello ' . $user->UserID . '</p><p>A request for the reopening of ticket <b>#' . $ticket->TicketID . '</b> has been submitted for your review. Click the link below to review</p>
                <p><a href="' . route('tickets.show', [$ticket->TicketID]) . '"> ticket details</a></p>
                <p>Reason given: ' . $reason . '</p>',
            immediate: true
        );
    }
}
