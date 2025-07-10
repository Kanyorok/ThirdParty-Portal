<?php

namespace App\Services;

use App\Enums\EmailTypeEnum;
use App\Models\Auth\User;
use App\Models\BR\Client;
use App\Models\BR\DebtProduct;
use App\Models\Communication\Call;
use App\Models\Communication\Email;
use App\Models\Communication\SMS;
use App\Models\Core\Activity;
use App\Models\Core\Task;
use App\Models\CRM\Campaign;
use App\Models\CRM\Discussion;
use App\Models\CRM\Lead;
use App\Models\CRM\Meeting;
use App\Models\CRM\Notes;
use App\Models\CRM\Schedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class ActivityService
{
    public static function lead(Lead $lead, string $description, User $actor, Carbon $dated): array
    {
        return self::rendering(self::_save($lead->LeadID, Lead::getPrimaryKey(), Lead::getPrimaryKey(), $lead->LeadID, $description, $actor, $dated));
    }

    public static function rendering(Activity $activity): array
    {
        return [
                'id'   => $activity->ActivityID,
                'html' => '<div class="d-flex align-items-start"><div class="flex-grow-1">
        <small class="float-end text-navy">' . $activity->CreatedOn->diffForHumans(short: true) . '</small>' . $activity->Notes . '<br />
        <small class="text-muted">' . $activity->CreatedOn->format('F d, Y h:i a') . '</small><br /></div></div><hr />',
               ];
    }

    private static function _save(string|array $PartyIDs, string $Party, string $type, int $typeId, string $description, User $actor, Carbon $dated): Activity
    {
        if (!in_array($Party, [Client::getPrimaryKey(), Lead::getPrimaryKey()], true)) {
            throw new RuntimeException("Invalid party in activity service");
        }
        if (is_array($PartyIDs)) {
            $data = collect();
            $clients = collect($PartyIDs)->unique();
            foreach ($clients->chunk(200) as $chunk) {//2100/9  200 at a time
                foreach ($chunk as $PartyID) {
                    $data->add([
                                "Party"          => $Party,
                                "PartyID"        => $PartyID,
                                'UserID'         => $actor->Id,
                                'Notes'          => $description,
                                'ActivityType'   => $type,
                                'ActivityTypeID' => $typeId,
                                'CreatedBy'      => $actor->Id,
                                'ModifiedBy'     => $actor->Id,
                                'CreatedOn'      => $dated,
                                'ModifiedOn'     => $dated,
                               ]);
                }

                if ($data->count() > 0) {
                    DB::table('t_PartyActivities')->lock('WITH(NOLOCK)')->insert($data->toArray());
                    $data = collect();
                }
            }
            return new Activity();
        }

        $activity = new Activity();
        $activity->fill([
                         "Party"          => $Party,
                         "PartyID"        => $PartyIDs,
                         'UserID'         => $actor->Id,
                         'Notes'          => $description,
                         'ActivityType'   => $type,
                         'ActivityTypeID' => $typeId,
                         'CreatedBy'      => $actor->Id,
                         'ModifiedBy'     => $actor->Id,
                         'CreatedOn'      => $dated,
                         'ModifiedOn'     => $dated,
                        ])->save(['timestamps' => false]);

        return $activity;
    }

    

    public static function campaignRun(string|array $PartyIDs, string $Party, string $description, Campaign $campaign, User $actor, Carbon $dated): void
    {
        self::_save($PartyIDs, $Party, Campaign::getPrimaryKey(), $campaign->Id, $description, $actor, $dated);
    }


    public static function schedule(string|array $PartyIDs, string $Party, Schedule $schedule, string $description, User $actor, ?Carbon $dated = null): void
{
    self::_save( $PartyIDs, $Party,Schedule::getPrimaryKey(), $schedule->ScheduleID,$description,$actor,$dated ?? ($schedule->CreatedOn ?? now())
    );
}


    public static function call(Call $call, string $description, User $actor): void
    {
        self::_save($call->PartyID, $call->Party, Call::getPrimaryKey(), $call->CallID, $description, $actor, $call->StartOn);
    }

    public static function meeting(Meeting $meeting, string|array $PartyIDs, string $description, User $actor): void
    {
        self::_save($PartyIDs, $meeting->Type, Meeting::getPrimaryKey(), $meeting->MeetingID, $description, $actor, ($meeting->StartOn) ?? now());
    }

    public static function note(Notes $note, string $description, User $actor): array
    {
        return self::rendering(self::_save($note->PartyID, $note->Party, Notes::getPrimaryKey(), $note->NoteID, $description, $actor, ($note->CreatedOn) ?? now()));
    }

    public static function task(Task $task, string $description, User $actor, ?Carbon $dated = null): array
{
    return self::rendering(self::_save($task->PartyID,$task->Party,Task::getPrimaryKey(),$task->TaskID,$description,$actor,$dated ?? ($task->CreatedOn ?? now()) ) );
}


    public static function email(Email $email, Carbon $dated, string $description = null): array
    {
        $description = ($description) ?? Str::of($email->Subject)->lower()->limit(30)->toString();
        $description = ($email->Type->value === EmailTypeEnum::Incoming->value) ? 'Email received : ' . $description : 'Email sent : ' . $description;
        return self::rendering(self::_save($email->PartyID, $email->Party, Email::getPrimaryKey(), $email->EmailID, $description, $email->creator, $dated));
    }

    public static function sms(SMS $sms, Carbon $dated, string $description = null): array
    {
        $description = ($description) ?? Str::limit($sms->Content, 30);

        $description = ($sms->Type->value === EmailTypeEnum::Incoming->value) ? 'SMS received : ' . $description : 'SMS sent : ' . $description;
        return self::rendering(self::_save($sms->PartyID, $sms->Party, SMS::getPrimaryKey(), $sms->Id, $description, $sms->creator, $dated));
    }

    public static function debt(DebtProduct $product, string $Type, int $TypeID, string $description, User $actor, Carbon $dated): array
    {

        return self::rendering(self::_save($product->AccountID, DebtProduct::getPrimaryKey(), $Type, $TypeID, $description, $actor, $dated));
    }

    public function discussion(Discussion $discussion, string $description, User $actor): void
    {
        self::_save($discussion->PartyID, $discussion->Party, Discussion::getPrimaryKey(), $discussion->DiscussionID, $description, $actor, ($discussion->CreatedOn) ?? now());
    }
}
