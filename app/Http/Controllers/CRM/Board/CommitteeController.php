<?php

namespace App\Http\Controllers\CRM\Board;

use App\Http\Controllers\Controller;
use App\Http\Requests\Board\CommitteeRequest;
use App\Models\Board;
use App\Models\Committee;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Yajra\DataTables\DataTables;

class CommitteeController extends Controller
{
    public function __construct()
    {
        $this->middleware('ajax');
       // $this->authorizeResource(Board::class);
    }

    /**
     * Display a listing of the resource.
     * @throws Exception
     */
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Board::class);
        return Datatables::of(Committee::query()->withCount('members')->select('*'))->addIndexColumn()
            ->editColumn('members_count', function (Committee $committee) {
               return number_format($committee->members_count ?? 0);
            })->setRowClass('mouse_pointer user-select-none dbl-click-summary-data')->setRowData([
                'dbl_click_url' => function (Committee $committee) {
                    return route('committee.show', [$committee->CommitteeID]);
                }, 'summary_title' => "Committee",
            ])->rawColumns(['committees'])->make();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create():View
    {
        $this->authorize('viewAny', Board::class);
        return view('crm.board.committee.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CommitteeRequest $request): JsonResponse
    {
        $this->authorize('viewAny', Board::class);
        $actor = $request->user();
        try {
            DB::transaction(static function () use ($actor, $request) {
                $committee = Committee::create([
                    "CommitteeID" => $request->generateID(),
                    "Name" => $request->validated('CommitteeName'),
                    'Notes' => $request->validated('CommitteeNotes'),
                    'CreatedBy' => $actor->Id,
                    'ModifiedBy' => $actor->Id,
                ]);

                activity()->causedBy($actor)->performedOn($committee)->event('create')->log('created board committee ' . $committee->CommitteeID . '.');
            });
        } catch (Exception|\Throwable $e) {
            Log::error('creating committee.');
            Log::error($e);
            return $this->errored('an unexpected error occurred');
        }
        return $this->succeeded('committee added successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(Committee $committee):View
    {
        $this->authorize('viewAny', Board::class);
        return view('crm.board.committee.show', compact('committee'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CommitteeRequest $request, Committee $committee):JsonResponse
    {
        $this->authorize('viewAny', Board::class);
        $actor = $request->user();
        try {
            DB::transaction(static function () use ($committee, $actor, $request) {
                $committee->update([
                    "Name" => $request->validated('CommitteeName'),
                    'Notes' => $request->validated('CommitteeNotes'),
                    'ModifiedBy' => $actor->Id,
                ]);

                activity()->causedBy($actor)->performedOn($committee)->event('update')->log('Updated committee ' . $committee->CommitteeID . '.');
            });
        } catch (\Throwable|Exception $e) {
            Log::error('update committee.');
            Log::error($e);
            return $this->errored('an unexpected error occurred');
        }
        return $this->succeeded('committee updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Committee $committee): JsonResponse
    {
        $this->authorize('viewAny', Board::class);
        $actor = $request->user();
        try {
            DB::transaction(static function () use ($committee, $actor, $request) {
                $committee->forceFill([
                    'DeletedOn' => now(),
                    'DeletedBy' => $request->user()->Id,
                ])->save();
                activity()->causedBy($actor)->performedOn($committee)->event('delete')->log('removed committee ' . $committee->CommitteeID . '.');
            });
        } catch (\Throwable|Exception $e) {
            Log::error('trash committee.');
            Log::error($e);
            return $this->errored('an unexpected error occurred');
        }
        return $this->succeeded('committee trashed successfully');
    }
}
