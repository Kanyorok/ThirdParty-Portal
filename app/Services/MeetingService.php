<?php

namespace App\Services;

use App\Enums\MeetingStatusEnum;
use App\Enums\Schedule\MeetingLocationEnum;
use App\Models\Auth\User;
use App\Models\BR\Client;
use App\Models\CRM\Lead;
use App\Models\CRM\Meeting;
use App\Models\CRM\MeetingRoom;
use App\Models\CRM\Schedule;
use App\Models\DMS\Image;
use App\Services\DMS\ImageService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class MeetingService
{
    public function __construct(public Meeting $meeting)
    {
    }

    public static function rooms(): Collection //todo fix this
    {
        return MeetingRoom::all();
    }

    public function getVenue(bool $with_link = false): string
    {
        $type = $this->meeting->MeetingLocationType->value;
        if (MeetingLocationEnum::Local->value === $type) {
            $room = $this->meeting->room;
            if ($room instanceof MeetingRoom) {
                return $room->RoomID . ': ' . $room->Name;
            }
        }

        $location = $this->meeting->Location;
        if ((filter_var($location, FILTER_VALIDATE_URL))) {
            return 'Online ' . ($with_link) ? $location : '';
        }

        return $location;
    }

    public function update(MeetingStatusEnum $status, string $title, string|MeetingRoom $location, Carbon $start, User $actor, Carbon $end = null, string $agenda = null): static
    {
        if ($location instanceof MeetingRoom) {
            $Location = '';
            $LocationType = MeetingLocationEnum::Local;
            $LocationId = $location->Id;
        } else {
            $Location = $location;
            $LocationType = ((filter_var($location, FILTER_VALIDATE_URL))) ? MeetingLocationEnum::Online : MeetingLocationEnum::Physical;
            $LocationId = null;
        }

        $this->meeting->update([
                                'Title' => $title,
                                'StatusID' => $status->value,
                                'StartOn' => $start,
                                'EndOn' => $end ?? $this->meeting->EndOn,
                                'Location' => $Location,
                                'MeetingLocationType' => $LocationType->value,
                                'LocationId' => $LocationId,
                                'Notes' => $agenda ?? $this->meeting->Notes,
                                'ModifiedBy' => $actor->Id,
                               ]);

        return $this;
    }

    public function document(UploadedFile $file, User $actor): Image
    {
        $document = ImageService::createUpload($file, Meeting::getPrimaryKey(), $this->meeting->MeetingID, $actor)->image;
        activity()->causedBy($actor)->performedOn($this->meeting)->event('document')->log('added a document  ' . $document->Name . ' to meeting ' . $this->meeting->Title . '.');

        return $document;
    }

    public static function create(MeetingStatusEnum $status, string $type, string $title, string|MeetingRoom $location, Carbon $start, Carbon $end, User $actor, string $notes, string $source = null, string $sourceID = null): self
    {
        if ($location instanceof MeetingRoom) {
            $Location = '';
            $LocationType = MeetingLocationEnum::Local;
            $LocationId = $location->Id;
        } else {
            $Location = $location;
            $LocationType = ((filter_var($location, FILTER_VALIDATE_URL))) ? MeetingLocationEnum::Online : MeetingLocationEnum::Physical;
            $LocationId = null;
        }

        $meeting = new Meeting();
        $meeting->fill([
                        'Title' => $title,
                        'StatusID' => $status->value,
                        'StartOn' => $start,
                        'EndOn' => $end,
                        'Type' => $type,
                        'Location' => $Location,
                        'MeetingLocationType' => $LocationType->value,
                        'LocationId' => $LocationId,
                        'Notes' => $notes,
                        'Source' => $source,
                        'SourceID' => $sourceID,
                        'CreatedBy' => $actor->Id,
                        'ModifiedBy' => $actor->Id,
                       ])->save();

        return new self($meeting->refresh());
    }

    public function attachPartiesFromSchedule(Schedule $schedule, Carbon $dated, User $actor): static
    {
        if ($schedule->ScheduledType !== Meeting::getPrimaryKey() || $schedule->ScheduledTypeID !== $this->meeting->MeetingID) {
            return $this;
        }

        //users todo add status for joined and non join.
        /*$users = $schedule->scheduleUsers()->where( 'ScheduleUserStatus', ScheduleUserStatusEnum::Accepted->value)->select('t_ScheduleUsers.UserID')->get('UserID')->pluck('UserID')->toArray();
        if (!empty($users)) {
            $this->attachUser($actor,$dated,$actor);
        }*/

        if ($this->meeting->Type === Client::class) {
            $clients = $schedule->scheduleClients()->select('ClientID')->get('ClientID')->pluck('ClientID')->toArray();
            if (! empty($clients)) {
                $this->attachClient($clients, $dated, $actor);
            }
        }

        if ($this->meeting->Type === Lead::class) {
            $leads = $schedule->scheduleLeads()->select('LeadId')->get('LeadId')->pluck('LeadId')->toArray();
            if (! empty($leads)) {
                $this->attachClient($leads, $dated, $actor);
            }
        }

        return $this;
    }

    public function attachClient(string|array $ClientIDs, Carbon $dated, User $actor, null|string $description = null): static
    {
        $description = ($description) ?? $this->meeting->Title . '  by ' . $actor->UserID;

        if (is_array($ClientIDs)) {
            $data = collect();
            $dataClients = collect();
            $clients = collect($ClientIDs)->unique();
            foreach ($clients->chunk(300) as $chunk) {//2000 attributes /6  hence 300 at a time.
                foreach ($chunk as $ClientID) {
                    $dataClients->add($ClientID);
                    $data->add([
                                'MeetingId' => $this->meeting->MeetingID,
                                'ClientID' => $ClientID,
                                'CreatedOn' => $dated,
                                'CreatedBy' => $actor->Id,
                                'ModifiedOn' => $dated,
                                'ModifiedBy' => $actor->Id,
                               ]);
                }
                if ($data->count() > 0) {
                    DB::table('t_MeetingClients')->insert($data->toArray());
                    // ActivityService::me($dataClients->toArray(), Client::getPrimaryKey(), $this->schedule, $description, $actor);
                    $data = collect();
                    $dataClients = collect();
                }
            }

            return $this;
        }

        DB::table('t_MeetingClients')->insert([
                                               'MeetingId' => $this->meeting->MeetingID,
                                               'ClientID' => $ClientIDs,
                                               'CreatedOn' => $dated,
                                               'CreatedBy' => $actor->Id,
                                               'ModifiedOn' => $dated,
                                               'ModifiedBy' => $actor->Id,
                                              ]);

        // ActivityService::schedule($ClientIDs, Client::getPrimaryKey(), $this->schedule, $description, $actor);

        return $this;
    }

    public function attachLead(string|array $LeadIds, Carbon $dated, User $actor, null|string $description = null): static
    {
        $description = ($description) ?? $this->meeting->Title . '  by ' . $actor->UserID;

        if (is_array($LeadIds)) {
            $data = collect();
            $dataLeads = collect();
            $leads = collect($LeadIds)->unique();
            foreach ($leads->chunk(300) as $chunk) {//2000 attributes /6  hence 300 at a time.
                foreach ($chunk as $LeadId) {
                    $dataLeads->add($LeadId);
                    $data->add([
                                'MeetingId' => $this->meeting->MeetingID,
                                'LeadId' => $LeadId,
                                'CreatedOn' => $dated,
                                'CreatedBy' => $actor->Id,
                                'ModifiedOn' => $dated,
                                'ModifiedBy' => $actor->Id,
                               ]);
                }
                if ($data->count() > 0) {
                    DB::table('t_MeetingLeads')->insert($data->toArray());
                    //ActivityService::schedule($dataLeads->toArray(), Lead::getPrimaryKey(), $this->schedule, $description, $actor);
                    $data = collect();
                    $dataClients = collect();
                }
            }

            return $this;
        }

        DB::table('t_MeetingLeads')->insert([
                                             'MeetingId' => $this->meeting->MeetingID,
                                             'LeadId' => $LeadIds,
                                             'CreatedOn' => $dated,
                                             'CreatedBy' => $actor->Id,
                                             'ModifiedOn' => $dated,
                                             'ModifiedBy' => $actor->Id,
                                            ]);

        //ActivityService::schedule($LeadIds, Lead::getPrimaryKey(), $this->schedule, $description, $actor);

        return $this;
    }

    public function attachUser(array|string $OperatorIDs, Carbon $dated, User $actor): static
    {
        if (is_array($OperatorIDs)) {
            $data = collect();
            $users = collect($OperatorIDs)->unique();
            foreach ($users->chunk(1000) as $chunk) {
                foreach ($chunk as $OperatorID) {
                    $data->add([
                                'MeetingId' => $this->meeting->MeetingID,
                                'UserID' => $OperatorID,
                                'CreatedOn' => $dated,
                                'CreatedBy' => $actor->Id,
                                'ModifiedOn' => $dated,
                                'ModifiedBy' => $actor->Id,
                               ]);
                }

                if ($data->count() > 0) {
                    DB::table('t_MeetingUsers')->insert($data->toArray());
                    $data = collect();
                }
            }

            return $this;
        }

        DB::table('t_MeetingUsers')->insert([
                                             'MeetingId' => $this->meeting->MeetingID,
                                             'UserID' => $OperatorIDs,
                                             'CreatedOn' => $dated,
                                             'CreatedBy' => $actor->Id,
                                             'ModifiedOn' => $dated,
                                             'ModifiedBy' => $actor->Id,
                                            ]);

        return $this;
    }

    public function attachBoard(array|string $MemberIDs, Carbon $dated, User $actor): static
    {
        if (is_array($MemberIDs)) {
            $data = collect();
            $users = collect($MemberIDs)->unique();
            foreach ($users->chunk(1000) as $chunk) {
                foreach ($chunk as $MemberID) {
                    $data->add([
                                'MeetingId' => $this->meeting->MeetingID,
                                'BoardMemberId' => $MemberID,
                                'CreatedOn' => $dated,
                                'CreatedBy' => $actor->Id,
                                'ModifiedOn' => $dated,
                                'ModifiedBy' => $actor->Id,
                               ]);
                }

                if ($data->count() > 0) {
                    DB::table('t_MeetingBoard')->insert($data->toArray());
                    $data = collect();
                }
            }

            return $this;
        }

        DB::table('t_MeetingBoard')->insert([
                                             'MeetingId' => $this->meeting->MeetingID,
                                             'BoardMemberId' => $MemberIDs,
                                             'CreatedOn' => $dated,
                                             'CreatedBy' => $actor->Id,
                                             'ModifiedOn' => $dated,
                                             'ModifiedBy' => $actor->Id,
                                            ]);

        return $this;
    }
}
