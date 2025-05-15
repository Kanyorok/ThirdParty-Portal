<?php

namespace App\Traits\Controller;

use App\Exceptions\ErroredException;
use App\Models\Auth\User;
use App\Models\BR\Client;
use App\Models\Core\Task;
use App\Models\CRM\Lead;
use App\Services\ActivityService;
use App\Services\TaskService;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;
use Yajra\DataTables\DataTables;

trait TasksTrait
{
    /**
     * @throws Exception
     */
    public function tasks(Builder|MorphMany|HasMany $query): JsonResponse
    {
        return Datatables::of($query->lock('WITH(NOLOCK)')->select('*'))->addIndexColumn()
            ->addColumn('action', function (Task $task) {
                return '<button type="button"  data-click_url="' . route('tasks.show', [$task->TaskID]) . '" data-summary_title="task details" class="btn  btn-sm btn-info click-summary-data"><i class="fas fa-eye"></i> </button>';
            })->editColumn('Dated', function (Task $task) {
                return $task->Dated?->format('F d, Y h:i A');
            })->editColumn('Notes', function (Task $task) {
                return Str::limit($task->Notes);
            })->setRowClass(function (Task $task) {
                return is_null($task->CompletedOn) ? 'user-select-none dbl-click-summary-data' : 'text-decoration-line-through user-select-none dbl-click-summary-data';
            })->setRowData([
                            'dbl_click_url' => function (Task $task) {
                                return route('tasks.show', [$task->TaskID]);
                            },
                            'summary_title' => 'Task Details',
                           ])->rawColumns(['action'])->make();
    }

    public function change(Task $task, string $notes, Carbon $dated, User $actor): void
    {
        DB::transaction(static function () use ($dated, $actor, $task, $notes) {
            $task->fill([
                         "Dated"      => $dated,
                         'Notes'      => $notes,
                         'ModifiedBy' => $actor->Id,
                        ])->save();

            //return ActivityService::task($task,  $actor->UserID . ' added a note.', $actor);
        });
    }

    /**
     * @throws Throwable
     * @throws ErroredException
     */
    public function save(Model $model, string $description, Carbon $due, User $assignee, User $actor, string $Source = null, string $SourceID = null): array
    {
        if (!$model instanceof Lead && !$model instanceof Client) {
            throw new ErroredException('unknown party given');
        }

        return DB::transaction(static function () use ($SourceID, $Source, $actor, $assignee, $description, $due, $model) {
            if ($model instanceof Lead) {
                TaskService::createLead(lead: $model, Due: $due, Description: $description, assignee: $assignee, actor: $actor, Source: $Source, SourceID: $SourceID);
            }
            //if ($model instanceof Client) {
            $service = TaskService::createClient(client: $model, Due: $due, Description: $description, assignee: $assignee, actor: $actor, Source: $Source, SourceID: $SourceID);
            //}

            return $service->addActivity($actor, 'Task (' . Str::limit($service->task->Notes, 30) . ') Added');
        });
    }

    public function toggleComplete(Task $task, User $actor): void
    {
        DB::transaction(static function () use ($actor, $task) {
            $task->fill([
                         "CompletedOn" => is_null($task->CompletedOn) ? now() : null,
                         'ModifiedBy'  => $actor->Id,
                        ])->save();
            // return ActivityService::task($task,  is_null($task->CompletedOn)$actor->Id . ' added a note.', $actor);
        });
    }

    public function cancel(Task $task, User $actor): array
    {
        $task->forceFill([
                          'DeletedOn' => now(),
                          'DeletedBy' => $actor->Id,
                         ])->save(['timestamps' => false]);

        return ActivityService::task($task, $actor->UserID . ' canceled task', $actor);
    }
}
