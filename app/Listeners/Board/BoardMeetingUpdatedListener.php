<?php

namespace App\Listeners\Board;

use App\Enums\EmailPriorityEnum;
use App\Events\Board\BoardMeetingUpdatedEvent;
use App\Helpers\SystemHelper;
use App\Models\Board;
use App\Models\Meeting;
use App\Models\Schedule;
use App\Models\User;
use App\Services\BoardService;
use App\Services\HRM\UserService;
use App\Services\MeetingService;
use Illuminate\Contracts\Queue\ShouldQueue;

class BoardMeetingUpdatedListener implements ShouldQueue
{
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
    public function handle(BoardMeetingUpdatedEvent $event): void
    {
        $meeting = $event->meeting;
        $schedule = Schedule::query()->where('t_Schedule.ScheduledType', Meeting::getPrimaryKey())->where('ScheduledTypeID', $meeting->MeetingID)->first();
        $actor = SystemHelper::user();
        if ($schedule instanceof Schedule) {
            foreach ($schedule->members as $member) {
                if (!$member instanceof Board) {
                    continue;
                }

                (new BoardService($member))->sendMessage(
                    'Hello #name, Scheduled meeting `' . $meeting->Title . '`  has been updated for ' . $schedule->StartOn->format('M d, Y') .
                    ' at ' . $schedule->StartOn->format('h:i A') . '. Please check your email for more details.',
                    $actor,
                    true
                )
                    ->sendEmail(
                        'Scheduled Meeting Updated ',
                        body: '<p>The upcoming meeting <b>' . $meeting->Title . '</b> scheduled has been updated :</p>
                            <p>Date: ' . $schedule->StartOn->format('M d, Y') . '</p>
                            <p>Time: ' . $schedule->StartOn->format('h:i A') . ' - ' . $schedule->EndOn->format('h:i A') . ' (' . $schedule->StartOn->format('e') . ')</p>
                            <p>Location: ' . (new MeetingService($meeting))->getVenue(true) . '</p>
                            <p>Agenda : </p> <p>' . $meeting->Notes . '</p>
                            <p>If you are unable to attend or need to join remotely, please notify the chair at your earliest convenience.</p>',
                        actor: $actor,
                        priorityEnum: EmailPriorityEnum::Important
                    )?->setSource(Meeting::getPrimaryKey(), $meeting->MeetingID)->send(true);
            }

            foreach ($schedule->users as $user) {
                if (!$user instanceof User) {
                    continue;
                }
                (new UserService($user))->sendEmail(
                    'Scheduled Meeting Updated ',
                    body: '<p>The upcoming meeting <b>' . $meeting->Title . '</b> scheduled has been updated :</p>
                            <p>Date: ' . $schedule->StartOn->format('M d, Y') . '</p>
                            <p>Time: ' . $schedule->StartOn->format('h:i A') . ' - ' . $schedule->EndOn->format('h:i A') . ' (' . $schedule->StartOn->format('e') . ')</p>
                            <p>Location: ' . (new MeetingService($meeting))->getVenue(true) . '</p>
                            <p>Agenda : </p> <p>' . $meeting->Notes . '</p>
                            <p>If you are unable to attend or need to join remotely, please notify the chair at your earliest convenience.</p>',
                )?->setSource(Meeting::getPrimaryKey(), $meeting->MeetingID)->send(true);
            }
        }
    }
}
