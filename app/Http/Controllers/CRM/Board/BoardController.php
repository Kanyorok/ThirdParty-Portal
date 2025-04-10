<?php

namespace App\Http\Controllers\CRM\Board;

use App\Http\Controllers\Controller;
use App\Http\Requests\Board\BoardUpdateRequest;
use App\Http\Requests\Board\NewBoardRequest;
use App\Models\Board;
use App\Models\Committee;
use App\Models\MeetingRoom;
use App\Services\BR\ClientService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Yajra\DataTables\DataTables;

class BoardController extends Controller
{
    public function __construct()
    {
        $this->middleware('ajax')->except(['index']);
        $this->authorizeResource(Board::class);
    }

    /**
     * Display a listing of the resource.
     * @throws Exception
     */
    public function index(Request $request): JsonResponse|View
    {
        if (!$request->ajax()) {
            return view('crm.board.index')
                ->with('rooms', MeetingRoom::query()->get(['RoomID', 'Name', 'Capacity']))
                ->with('committees',  Committee::query()->get(['t_Committees.CommitteeID','t_Committees.Name']));
        }
        return Datatables::of(Board::query()->with('committees')->select('*'))->addIndexColumn()
            ->addColumn('committees', function (Board $board) {
                return implode('&nbsp;',$board->committees->map(function ($committee) {
                    return '<span class="badge rounded-pill bg-info">'.$committee->Name.'</span>';
                })->toArray());
            })->editColumn('BoardMemberID', function (Board $board) {
                return Str::upper($board->BoardMemberID);
            })->setRowClass('mouse_pointer user-select-none dbl-click-summary-data')->setRowData([
                'dbl_click_url' => function (Board $board) {
                    return route('board.show', [$board->BoardMemberID]);
                }, 'summary_title' => "Board Member",
            ])->rawColumns(['committees'])->make();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('crm.board.create')
            ->with('committees', Committee::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(NewBoardRequest $request): JsonResponse
    {
        $actor = $request->user();
        $client = $request->getBaseClient();

        try {
            DB::transaction(static function () use ($client, $actor, $request) {
                $board = Board::create([
                    'Name' => $client->Name,
                    'ClientID' => $client->ClientID,
                    'Role' => $request->validated('BoardMemberRole'),
                    'Phone' => (new ClientService($client))->phoneNo() ?? '-',
                    'Email' => (new ClientService($client))->getEmail() ?? '-',
                    'Notes' => $request->validated('BoardMemberNotes'),
                    'BoardMemberID' => $request->generateID(),
                    'CreatedBy' => $actor->Id,
                    'ModifiedBy' => $actor->Id,
                ]);

                $board->committees()->syncWithPivotValues($request->getCommittees(),[
                    'CreatedBy' => $actor->Id,
                    'ModifiedBy' => $actor->Id,
                ], false);

                activity()->causedBy($actor)->performedOn($board)->event('create')->log('Created board member ' . $board->BoardMemberID . '.');
            });
        } catch (Exception|\Throwable $e) {
            Log::error('creating board member.');
            Log::error($e);
            return $this->errored('an unexpected error occurred');
        }
        return $this->succeeded('board member added successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(Board $board)
    {
        return view('crm.board.show', compact('board'))
            ->with('committees',  Committee::all());
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(BoardUpdateRequest $request, Board $board): JsonResponse
    {
        $actor = $request->user();
        try {
            DB::transaction(static function () use ($board, $actor, $request) {
                $board->update([
                    'Role' => $request->validated('BoardMemberRole'),
                    'Phone' => $request->validated('BoardMemberPhone'),
                    'Email' => $request->validated('BoardMemberEmail'),
                    'Notes' => $request->validated('BoardMemberNotes'),
                    'ModifiedBy' => $actor->Id,
                ]);

                $board->committees()->syncWithPivotValues($request->getCommittees(),[
                    'CreatedBy' => $actor->Id,
                    'ModifiedBy' => $actor->Id,
                ], false);

                activity()->causedBy($actor)->performedOn($board)->event('update')->log('Updated board member ' . $board->BoardMemberID . '.');
            });
        } catch (\Throwable|Exception $e) {
            Log::error('update board member.');
            Log::error($e);
            return $this->errored('an unexpected error occurred');
        }
        return $this->succeeded('board member updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Board $board): JsonResponse
    {
        $actor = $request->user();
        try {
            DB::transaction(static function () use ($board, $actor, $request) {
                $board->forceFill([
                    'DeletedOn' => now(),
                    'DeletedBy' => $request->user()->Id,
                ])->save();
                activity()->causedBy($actor)->performedOn($board)->event('delete')->log('removed board member ' . $board->BoardMemberID . '.');
            });
        } catch (\Throwable|Exception $e) {
            Log::error('trash board member.');
            Log::error($e);
            return $this->errored('an unexpected error occurred');
        }
        return $this->succeeded('board member trashed successfully');
    }
}
