<?php

namespace App\Listeners\Marketing;

use App\Enums\Core\ExtensionsEnum;
use App\Enums\EmailPriorityEnum;
use App\Events\Marketing\NewScheduleEvent;
use App\Helpers\SystemHelper;
use App\Models\Board;
use App\Models\Meeting;
use App\Models\Schedule;
use App\Models\User;
use App\Services\BoardService;
use App\Services\CRMEmailService;
use App\Services\ImageService;
use App\Services\MeetingService;
use App\Services\ScheduleService;
use App\Services\UserService;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyScheduleListener implements ShouldQueue
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
    public function handle(NewScheduleEvent $event): void
    {
        $this->_sendBoardEmails($event->schedule, SystemHelper::user());

        $this->_sendUserEmails($event->schedule, SystemHelper::user());
    }

    protected function _sendUserEmails(Schedule $schedule, User $actor, bool $sleep = false): void
    {
        $users = $schedule->users()->get(['t_Users.Name', 't_Users.Email', 't_Users.Id']);
        if (!$sleep && $users->count() === 0) {
            sleep(10);
            $this->_sendUserEmails($schedule, $actor, true);
            return;
        }

        if ($users->isEmpty()) {
            return;
        }

        $emails = $users->map(function ($user) {
            return [$user->Name => $user->Email];
        });

        $service = new ScheduleService($schedule);
        (new UserService($users->first()))
            ->sendEmail($service->getEmailSubject(), $service->getEmailContent(), $emails->toArray(), true)?->setSource(Schedule::getPrimaryKey(), $schedule->ScheduleID)
            ->addAttachmentContent($service->getEmailICS(), ExtensionsEnum::ICS->getMimeType(), 'invite.ics', $actor);
    }


    protected function _sendBoardEmails(Schedule $schedule, User $actor): void
    {
        $meeting = $schedule->scheduled;
        if ($meeting instanceof Meeting && $schedule->Type === Board::getPrimaryKey()) {
            $Attachment = ImageService::createContent((new ScheduleService($schedule))->getEmailICS(),Meeting::getPrimaryKey(),$meeting->MeetingID,ExtensionsEnum::ICS->getMimeType(),'invite.ics',$actor)->image;
            foreach ( $schedule->members as $member) {
                    if(!$member instanceof Board){
                        continue;
                    }

                    (new BoardService($member))->sendMessage('Hello #name, There is a scheduled meeting `'.$meeting->Title.'` for ' . $schedule->StartOn->format('M d, Y') .
                    ' at ' . $schedule->StartOn->format('h:i A') . '. Please check your email for more details.',
                     $actor,true)
                    ->sendEmail('Board Meeting on ' . $schedule->StartOn->format('M d, Y'),
                        body: '<p>There is an upcoming meeting <b>'.$meeting->Title.'</b> scheduled for:</p>
                            <p>Date: ' . $schedule->StartOn->format('M d, Y') . '</p>
                            <p>Time: ' . $schedule->StartOn->format('h:i A') . ' - ' . $schedule->EndOn->format('h:i A') . ' (' . $schedule->StartOn->format('e') . ')</p>
                            <p>Location: ' . (new MeetingService($meeting))->getVenue(true) . '</p>
                            <p>Agenda : </p> <p>' . $meeting->Notes . '</p>
                            <p>If you are unable to attend or need to join remotely, please notify the chair at your earliest convenience.</p>',
                        actor: $actor,
                        priorityEnum: EmailPriorityEnum::Important
                    )?->setSource(Meeting::getPrimaryKey(), $meeting->MeetingID)->addAttachment($Attachment)->send(true);
            }

        }
    }

}
