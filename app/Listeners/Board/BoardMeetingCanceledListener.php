<?php

namespace App\Listeners\Board;

use App\Enums\EmailPriorityEnum;
use App\Events\Board\BoardMeetingCanceledEvent;
use App\Helpers\SystemHelper;
use App\Models\Board;
use App\Models\Meeting;
use App\Models\Schedule;
use App\Models\User;
use App\Services\BoardService;
use App\Services\MeetingService;
use App\Services\UserService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class BoardMeetingCanceledListener implements ShouldQueue
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
    public function handle(BoardMeetingCanceledEvent $event): void
    {
        $meeting = $event->meeting;
        $schedule = Schedule::query()->where('t_Schedule.ScheduledType',Meeting::getPrimaryKey())->where('ScheduledTypeID', $meeting->MeetingID)->first();
        $actor = SystemHelper::user();
        if($schedule instanceof Schedule) {
            foreach ( $schedule->members as $member) {
                if(!$member instanceof Board){
                    continue;
                }

                (new BoardService($member))->sendMessage('Hello #name, Scheduled meeting `'.$meeting->Title.'`  has been CANCELED. Please check your email for more details.',
                    $actor,true)
                    ->sendEmail('Meeting Canceled: ' . $meeting->Title,
                        body: '<p>We regret to inform you that the scheduled meeting <b>' . $meeting->Title . '</b> has been canceled.</p>
                               <p>Please find the details of the canceled meeting below:</p>
                               <ul>
                                   <li><b>Date:</b> ' . $schedule->StartOn->format('M d, Y') . '</li>
                                   <li><b>Time:</b> ' . $schedule->StartOn->format('h:i A') . ' - ' . $schedule->EndOn->format('h:i A') . ' (' . $schedule->StartOn->format('e') . ')</li>
                                   <li><b>Location:</b> ' . (new MeetingService($meeting))->getVenue(true) . '</li>
                                   <li><b>Agenda:</b> ' . $meeting->Notes . '</li>
                               </ul>
                               <p>We apologize for any inconvenience this may have caused. If you have any questions or concerns, feel free to reach out.</p>',
                        actor: $actor,
                        priorityEnum: EmailPriorityEnum::Important
                    )?->setSource(Meeting::getPrimaryKey(), $meeting->MeetingID)->send(true);
            }

            foreach ( $schedule->users as $user) {
                if(!$user instanceof User){
                    continue;
                }
                (new UserService($user))->sendEmail('Meeting Canceled: ' . $meeting->Title,
                    body: '<p>We regret to inform you that the scheduled meeting <b>' . $meeting->Title . '</b> has been canceled.</p>
                           <p>Please find the details of the canceled meeting below:</p>
                           <ul>
                               <li><b>Date:</b> ' . $schedule->StartOn->format('M d, Y') . '</li>
                               <li><b>Time:</b> ' . $schedule->StartOn->format('h:i A') . ' - ' . $schedule->EndOn->format('h:i A') . ' (' . $schedule->StartOn->format('e') . ')</li>
                               <li><b>Location:</b> ' . (new MeetingService($meeting))->getVenue(true) . '</li>
                               <li><b>Agenda:</b> ' . $meeting->Notes . '</li>
                           </ul>
                           <p>We apologize for any inconvenience this may have caused. If you have any questions or concerns, feel free to reach out.</p>',
                )?->setSource(Meeting::getPrimaryKey(), $meeting->MeetingID)->send(true);
            }

        }
    }
}
