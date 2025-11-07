<?php

namespace App\Http\Controllers\CRM\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Schedule\PartyTaskRequest;
use App\Models\BR\Client;
use App\Models\Core\Task;
use App\Traits\Controller\TasksTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

class ClientTaskController extends Controller
{
    use TasksTrait;

    public function __construct()
    {
        $this->middleware('ajax');
        //$this->authorizeResource(Task::class);
    }

    /**
     * Display a listing of the resource.
     * @throws Exception
     */
    public function index(Client $client): JsonResponse
    {
        $this->authorize('viewAny', Task::class);
        return $this->tasks($client->tasks());
    }

    /**
     * Store a newly created resource in storage.
     * @throws ValidationException
     */
    public function store(PartyTaskRequest $request, Client $client): JsonResponse
    {
        $this->authorize('create', Task::class);
        $notes = $request->getNotes();
        $dated = $request->getDated();
        $assignee = $request->getAssignee();
        $actor = $request->user();

        try {
            $activity = $this->save($client, $notes, $dated, $assignee, $actor);
        } catch (Throwable|Exception $e) {
            Log::error('Error adding  Client Task. e: ' . $e->getMessage());
            return $this->errored('unexpected error, try again latter');
        }

        return $this->succeeded('task added successfully.', data: ['activity' => $activity]);
    }

    /**
     * Update the specified resource in storage.
     * @throws ValidationException
     */
    public function update(PartyTaskRequest $request, Client $client, string $task_id): JsonResponse
    {

        $notes = $request->getNotes();
        $actor = $request->user();

        $task = $client->tasks()->where('TaskID', $task_id)->first();
        if (!$task instanceof Task) {
            return $this->errored('Task not found');
        }
        $this->authorize('update', $task);
        $dated = $request->getDated(/*$task->Dated*/ now());
        try {
            $this->change($task, $notes, $dated, $actor);
        } catch (Exception $e) {
            Log::error('Error updating  Client Task. e: ' . $e->getMessage());
            return $this->errored('unexpected error, try again latter');
        }

        return $this->succeeded('task updated successfully.');
    }
}
