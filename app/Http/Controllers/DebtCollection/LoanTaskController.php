<?php

namespace App\Http\Controllers\DebtCollection;

use App\Http\Controllers\Controller;
use App\Http\Requests\Schedule\PartyTaskRequest;
use App\Models\BR\DebtProduct;
use App\Models\Task;
use App\Traits\Controller\TasksTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class LoanTaskController extends Controller
{
    use TasksTrait;

    public function __construct()
    {
        $this->middleware('ajax');
        $this->authorizeResource(Task::class);
    }

    /**
     * Display a listing of the resource.
     * @throws Exception
     */
    public function index(string $product_id): JsonResponse
    {
        $product = DebtProduct::query()->where('AccountID', $product_id)->oldest('processDate')->first();
        if (!$product instanceof DebtProduct) {
            return $this->errored('Product not found, maybe closed.');
        }
        $this->authorize('view', $product);

        return $this->tasks($product->tasks());
    }

    /**
     * Store a newly created resource in storage.
     * @throws ValidationException
     */
    public function store(PartyTaskRequest $request, string $product_id): JsonResponse
    {
        $product = DebtProduct::query()->where('AccountID', $product_id)->oldest('processDate')->first();
        if (!$product instanceof DebtProduct) {
            return $this->errored('Product not found, maybe closed.');
        }
        $this->authorize('view', $product);
        $notes = $request->getNotes();
        $dated = $request->getDated();
        $assignee = $request->getAssignee();
        $actor = $request->user();

        try {
            $activity = $this->save($product->client, $notes, $dated, $assignee, $actor, DebtProduct::getPrimaryKey(), $product_id);
        } catch (\Throwable|Exception $e) {
            Log::error('Error adding  Loan Task. e: ' . $e->getMessage());
            return $this->errored('unexpected error, try again latter');
        }

        return $this->succeeded('task added successfully.', data: ['activity' => $activity]);
    }
}
