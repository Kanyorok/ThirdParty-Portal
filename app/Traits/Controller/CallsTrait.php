<?php

namespace App\Traits\Controller;

use App\Enums\CallStatusEnum;
use App\Models\Auth\User;
use App\Models\Communication\Call;
use App\Models\CRM\Schedule;
use App\Services\Call\CallService;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use PHPUnit\Runner\ErrorException;
use Throwable;
use Yajra\DataTables\DataTables;

trait CallsTrait
{
    /**
     * @throws Exception
     */
    public function calls(Builder|MorphMany $query): JsonResponse
    {
        return Datatables::of($query->lock('WITH(NOLOCK)')->with('creator')->select('*'))->addIndexColumn()
            ->editColumn('user', function (Call $call) {
                /*if ($call->user?->lead instanceof Lead) {
                    return $call->user?->lead->getImage('class="rounded-circle me-2" width="48" height="48"') . $call->UserID . '<br><small class="small">' . $call->user?->lead->name . '</small>';
                }*/
                return $call->creator?->getImage('class="rounded-circle me-2" width="48" height="48"') . $call->creator?->UserID;
            })->editColumn('StartOn', function (Call $call) {
                return $call->StartOn?->format('F d, Y h:i A');
            })->editColumn('EndOn', function (Call $call) {
                return $call->EndOn?->format('F d, Y h:i A');
            })->addColumn('Duration', function (Call $call) {
                if ($call->EndOn instanceof Carbon) {
                    return $call->EndOn->diffForHumans($call->StartOn, \Carbon\CarbonInterface::DIFF_ABSOLUTE, parts: 2, short: true);
                }//->diffInMinutes(, true)." mins";
                return 'Non ended';
            })->editColumn('CallStatusID', function (Call $call) {
                return $call->CallStatusID->description();
            })->rawColumns(['user'])->make();
    }

    /**
     * @deprecated
     * @throws Exception
     */
    public function startCall(MorphMany $query, Carbon $start, User $actor, Schedule $schedule = null): Call
    {
        throw new ErrorException('deprecated');
    }

    /**
     * @throws Throwable
     */
    public function endCall(Call $call, Carbon $end, User $actor, string $discussion, string $notes = null, bool $activity = true): Call
    {
        return DB::transaction(static function () use ($activity, $discussion, $notes, $end, $actor, $call) {
            $service = (new CallService($call))->end($end, CallStatusEnum::SuccessDiscussion, $actor)
                ->discussion($discussion, $actor, $notes);
            if ($activity) {
                $service->activity($actor);
            }
            return $service->call;
        });
    }
}
