<?php

namespace App\Http\Controllers\Base;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Services\TaskService;
use App\Traits\Controller\TasksTrait;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class TaskController extends Controller
{
    use TasksTrait;

    /**
     * Display a listing of the resource.
     * @throws Exception
     */
    public function index(Request $request): JsonResponse
    {
        if ($request->ajax()) {
            $query = $request->user()->tasks()
                ->where(function (Builder $query) {
                    $query->where(function (Builder $query) {
                        $query->whereNull('CompletedOn')
                            ->where('Dated', '<', now()->addDays(7)->endOfDay());
                    })->orWhere(function (Builder $query) {
                        $query->whereNotNull('CompletedOn')
                            ->whereBetween('Dated', [now()->startOfDay(), now()->endOfDay()]);
                    });
                });

            //tasks due this week and once completed today
            return $this->tasks($query);
        }
        return $this->errored('not allowed');
        //return view('base.tasks.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task): View
    {
        return view('base.tasks.show', compact('task'))
            ->with('service', (new TaskService($task)))
            ->with('party', $task->party);
    }

    /**
     * Toggle Complete
     */
    public function update(Request $request, Task $task): JsonResponse
    {
        if (!is_null($task->CompletedOn) && $task->CompletedOn instanceof Carbon && $task->CompletedOn->lessThan(now()->subDay())) {
            return $this->errored('old task, cannot restore');
        }
        $message = is_null($task->CompletedOn) ? 'task completed' : 'task restored';
        $this->toggleComplete($task, $request->user());

        return $this->succeeded($message);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Task $task): JsonResponse
    {
        if (!is_null($task->CompletedOn)) {
            return $this->errored('closed task, cannot cancel');
        }
        try {
            $activity = $this->cancel($task, $request->user());
        } catch (Exception $e) {
            Log::error('Error canceling  Lead Task. e: ' . $e->getMessage());
            return $this->errored('unexpected error, try again latter');
        }

        return $this->succeeded('task canceled successfully.', data: ['activity' => $activity]);
    }
}
