<?php

namespace App\Traits\Controller;

use App\Enums\MeetingStatusEnum;
use App\Enums\ScheduleStatusEnum;
use App\Exceptions\ErroredException;
use App\Models\Board;
use App\Models\BR\Client;
use App\Models\Call;
use App\Models\Lead;
use App\Models\Meeting;
use App\Models\MeetingRoom;
use App\Models\Schedule;
use App\Models\User;
use App\Services\ActivityService;
use App\Services\ScheduleService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\DataTables;

trait ScheduleTrait
{
    /**
     * @throws \Exception
     */
    public function schedules(Builder|BelongsToMany|MorphMany $query, array $with = []): JsonResponse
    {
        if (!empty($with)) {
            $query->with($with);
        }
        return Datatables::of($query->lock('WITH(NOLOCK)')->select('*'))->addIndexColumn()
            ->addColumn('action', function (Schedule $schedule) {
                return '<button type="button"  data-click_url="' . route('schedule.show', [$schedule->ScheduleID]) . '" data-summary_title="schedule details" class="btn btn-info btn-sm click-summary-data"><i class="fas fa-eye"></i> details</button>';
            })->editColumn('ScheduledType', function (Schedule $schedule) {
                return match ($schedule->ScheduledType) {
                    Meeting::getPrimaryKey() => 'Meeting',
                    Call::getPrimaryKey() => 'Call',
                    default => $schedule->ScheduledType
                };
            })->editColumn('StartOn', function (Schedule $schedule) {
                return $schedule->StartOn?->format('F d, Y h:i A');
            })->editColumn('EndOn', function (Schedule $schedule) {
                return $schedule->EndOn?->format('F d, Y h:i A');
            })->rawColumns(['action'])->make();

    }


    /**
     * @throws ErroredException
     */
    public function appointment(Model $model, string $title, string|MeetingRoom $location, Carbon $start, Carbon $end, $dated, User $actor, string $notes = '', array $UserIds = [], string $Source = null, string $SourceID = null): Schedule
    {
        if ($model instanceof Client) {
            return ScheduleService::clientMeeting(client: $model, title: $title, location: $location, start: $start, end: $end, dated: $dated, actor: $actor, notes: $notes, UserIds: $UserIds, source: $Source, sourceID: $SourceID)->schedule;
        }

        if ($model instanceof Lead) {
            return ScheduleService::leadMeeting(lead: $model, title: $title, location: $location, start: $start, end: $end, dated: $dated, actor: $actor, notes: $notes, UserIds: $UserIds)->schedule;
        }

        throw new ErroredException();
    }

    /**
     * @throws ErroredException
     */
    public function phoneCall(Model $model, Carbon $start, Carbon $end, $dated, User $actor, $notes = '', bool $rescheduled = false, array $UserIds = [], string $Source = null, string $SourceID = null): Schedule
    {
        if ($model instanceof Client) {
            return ScheduleService::clientCall(client: $model, start: $start, end: $end, dated: $dated, actor: $actor, notes: $notes, rescheduled: $rescheduled, UserIds: $UserIds, source: $Source, sourceID: $SourceID)->schedule;
        }

        if ($model instanceof Lead) {
            return ScheduleService::leadCall(lead: $model, start: $start, end: $end, dated: $dated, actor: $actor, notes: $notes, rescheduled: $rescheduled, UserIds: $UserIds)->schedule;
        }

        throw new ErroredException();
    }

    /**
     * @throws ErroredException
     */
    public function cancel(Model $model, Schedule $schedule, User $actor): void
    {
        $schedule->forceFill([
            'ScheduleStatusID' => ScheduleStatusEnum::Canceled->value,
            'DeletedOn' => Carbon::now(),
            'DeletedBy' => $actor->Id
        ])->save(['timestamps' => false]);

        $scheduled = $schedule->scheduled;
        if ($scheduled instanceof Meeting) {
            $scheduled->fill([
                'StatusID' => MeetingStatusEnum::Canceled->value,
            ])->save();
        }

        if ($model instanceof Client) {
            ActivityService::schedule($model->ClientID, Client::getPrimaryKey(), $schedule, $actor->UserID . ' Canceled - ' . $schedule->Title, $actor);
            return;
        }

        if ($model instanceof Lead) {
            ActivityService::schedule($model->LeadID, Lead::getPrimaryKey(), $schedule, $actor->UserID . ' Canceled - ' . $schedule->Title, $actor);
            return;
        }

        throw new ErroredException('unknown actionable');
    }
}
