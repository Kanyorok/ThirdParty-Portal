<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\MeetingRoomRequest;
use App\Models\BR\Branch;
use App\Models\CRM\MeetingRoom;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Yajra\DataTables\DataTables;

class MeetingRoomController extends Controller
{
    public function __construct()
    {
        $this->middleware('ajax');
        $this->authorizeResource(MeetingRoom::class);
    }

    /**
     * Display a listing of the resource.
     * @throws Exception
     */
    public function index(): JsonResponse
    {
        return Datatables::of(MeetingRoom::query()->with('branch')->select('*'))->addIndexColumn()
            ->editColumn('RoomID', function (MeetingRoom $MeetingRoom) {
                return Str::upper($MeetingRoom->RoomID);
            })->editColumn('BranchId', function (MeetingRoom $MeetingRoom) {
                $branch = $MeetingRoom->branch;
                if ($branch instanceof Branch) {
                    return $branch->BranchName;
                }

                return '. . .';
            })->setRowClass('mouse_pointer user-select-none dbl-click-summary-data')->setRowData([
                                                                                                  'dbl_click_url' => function (MeetingRoom $MeetingRoom) {
                                                                                                      return route('meeting-room.show', [$MeetingRoom->RoomID]);
                                                                                                  },
                                                                                                  'summary_title' => "Meeting Room Details",
                                                                                                 ])->rawColumns(['action'])->make();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(MeetingRoomRequest $request): JsonResponse
    {
        $branch = $request->getBranch();
        $actor = $request->user();

        try {
            DB::transaction(static function () use ($branch, $actor, $request) {
                $MeetingRoom = MeetingRoom::create([
                                                    'Name' => $request->validated('RooMName'),
                                                    'Capacity' => $request->validated('RooMCapacity'),
                                                    'BranchId' => $branch,
                                                    'Notes' => $request->validated('RooMNotes'),
                                                    'RoomID' => $request->generateID(),
                                                    'CreatedBy' => $actor->Id,
                                                    'ModifiedBy' => $actor->Id,
                                                   ]);
                activity()->causedBy($actor)->performedOn($MeetingRoom)->event('create')->log('Created Meeting Room  ' . $MeetingRoom->RoomID . '.');
            });
        } catch (Exception $e) {
            Log::error('creating Meeting Room .');
            Log::error($e);

            return $this->errored('an unexpected error occurred');
        }

        return $this->succeeded('Meeting Room added successfully');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('settings.meeting-rooms.create')
            ->with('branches', Branch::all());
    }

    /**
     * Display the specified resource.
     */
    public function show(MeetingRoom $MeetingRoom)
    {
        return view('settings.meeting-rooms.show', compact('MeetingRoom'))->with('branches', Branch::all());
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(MeetingRoomRequest $request, MeetingRoom $MeetingRoom): JsonResponse
    {
        $branch = $request->getBranch();
        $actor = $request->user();

        try {
            DB::transaction(static function () use ($branch, $MeetingRoom, $actor, $request) {
                $MeetingRoom->update([
                                      'Name' => $request->validated('RooMName'),
                                      'Capacity' => $request->validated('RooMCapacity'),
                                      'BranchId' => $branch,
                                      'Notes' => $request->validated('RooMNotes'),
                                      'ModifiedBy' => $actor->Id,
                                     ]);
                activity()->causedBy($actor)->performedOn($MeetingRoom)->event('update')->log('Updated Meeting Room ' . $MeetingRoom->RoomID . '.');
            });
        } catch (Exception $e) {
            Log::error('update Meeting Room.');
            Log::error($e);

            return $this->errored('an unexpected error occurred');
        }

        return $this->succeeded('Room updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, MeetingRoom $MeetingRoom): JsonResponse
    {
        $actor = $request->user();

        try {
            DB::transaction(static function () use ($MeetingRoom, $actor, $request) {
                $MeetingRoom->forceFill([
                                         'DeletedOn' => now(),
                                         'DeletedBy' => $request->user()->Id,
                                        ])->save();
                activity()->causedBy($actor)->performedOn($MeetingRoom)->event('delete')->log('removed Meeting Room ' . $MeetingRoom->RoomID . '.');
            });
        } catch (Exception $e) {
            Log::error('trash Meeting Room.');
            Log::error($e);

            return $this->errored('an unexpected error occurred');
        }

        return $this->succeeded('Meeting Room trashed successfully');
    }
}
