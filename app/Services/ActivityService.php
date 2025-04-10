<?php

namespace App\Services;

use App\Enums\EmailTypeEnum;
use App\Models\Activity;
use App\Models\BR\Client;
use App\Models\BR\DebtProduct;
use App\Models\Call;
use App\Models\Campaign;
use App\Models\CrmEmail;
use App\Models\CrmSMS;
use App\Models\Discussion;
use App\Models\Lead;
use App\Models\Meeting;
use App\Models\Notes;
use App\Models\Schedule;
use App\Models\Task;
use App\Models\User;
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
        return ['id' => $activity->ActivityID,
            'html' => '<div class="d-flex align-items-start"><div class="flex-grow-1">
        <small class="float-end text-navy">' . $activity->CreatedOn->diffForHumans(short: true) . '</small>' . $activity->Notes . '<br />
        <small class="text-muted">' . $activity->CreatedOn->format('F d, Y h:i a') . '</small><br /></div></div><hr />'];
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
                        "Party" => $Party,
                        "PartyID" => $PartyID,
                        'UserID' => $actor->Id,
                        'Notes' => $description,
                        'ActivityType' => $type,
                        'ActivityTypeID' => $typeId,
                        'CreatedBy' => $actor->Id,
                        'ModifiedBy' => $actor->Id,
                        'CreatedOn' => $dated,
                        'ModifiedOn' => $dated
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
            "Party" => $Party,
            "PartyID" => $PartyIDs,
            'UserID' => $actor->Id,
            'Notes' => $description,
            'ActivityType' => $type,
            'ActivityTypeID' => $typeId,
            'CreatedBy' => $actor->Id,
            'ModifiedBy' => $actor->Id,
            'CreatedOn' => $dated,
            'ModifiedOn' => $dated
        ])->save(['timestamps' => false]);

        return $activity;

    }

    public static function campaignRun(string|array $PartyIDs, string $Party, string $description, Campaign $campaign, User $actor, Carbon $dated): void
    {
        self::_save($PartyIDs, $Party, Campaign::getPrimaryKey(), $campaign->Id, $description, $actor, $dated);
    }

    public static function schedule(string|array $PartyIDs, string $Party, Schedule $schedule, string $description, User $actor): void
    {
        self::_save($PartyIDs, $Party, Schedule::getPrimaryKey(), $schedule->ScheduleID, $description, $actor, ($schedule->CreatedOn) ?? now());
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

    public static function task(Task $task, string $description, User $actor): array
    {
        return self::rendering(self::_save($task->PartyID, $task->Party, Task::getPrimaryKey(), $task->TaskID, $description, $actor, ($task->CreatedOn) ?? now()));
    }

    public static function email(CrmEmail $email, Carbon $dated, string $description = null): array
    {
        $description = ($description) ?? Str::of($email->Subject)->lower()->limit(30)->toString();
        $description = ($email->Type->value === EmailTypeEnum::Incoming->value) ? 'Email received : ' . $description : 'Email sent : ' . $description;
        return self::rendering(self::_save($email->PartyID, $email->Party, CrmEmail::getPrimaryKey(), $email->EmailID, $description, $email->creator, $dated));
    }

    public static function sms(CrmSMS $sms, Carbon $dated, string $description = null): array
    {
        $description = ($description) ?? Str::limit($sms->Content, 30);

        $description = ($sms->Type->value === EmailTypeEnum::Incoming->value) ? 'SMS received : ' . $description : 'SMS sent : ' . $description;
        return self::rendering(self::_save($sms->PartyID, $sms->Party, CrmSMS::getPrimaryKey(), $sms->Id, $description, $sms->creator, $dated));
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
