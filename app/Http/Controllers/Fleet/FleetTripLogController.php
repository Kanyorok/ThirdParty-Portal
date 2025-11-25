<?php

namespace App\Http\Controllers\Fleet;

use App\Http\Controllers\Controller;
use App\Http\Requests\FleetManagement\FleetTripLogRequest;
use App\Models\Fleet\FleetTripLog;
use App\Models\Core\Approval\CodeDetail;
use App\Services\FleetManagement\FleetTripLogService;
use Illuminate\Http\Request;
use App\Models\CRM\MarketingPlanner;
use App\Enums\Marketing\PlannerStatus;

class FleetTripLogController extends Controller
{
    protected FleetTripLogService $tripLogService;

    public function __construct(FleetTripLogService $tripLogService)
    {
        $this->tripLogService = $tripLogService;
    }

    public function index()
    {
        $this->authorize('viewAny', FleetTripLog::class);

        $tripLogs = FleetTripLog::with(['childTrips', 'parentLoadType', 'parentVehicleType', 'statusDetail', 'parentTripType'])
            ->whereNull('ParentTripID')
            ->orderByDesc('CreatedOn')
            ->get();

        return view('fleet.trip_logs.index', compact('tripLogs'));
    }

  public function create(Request $request)
    {
        $this->authorize('create', FleetTripLog::class);

        $parentTrip = null;
        if ($request->has('parentTripId')) {
            $parentTrip = FleetTripLog::findOrFail($request->parentTripId);
        }
        $parentTrip = null;
        if ($request->has('parentTripId')) {
            $parentTrip = FleetTripLog::findOrFail($request->parentTripId);
        }

        // Remove $tripsStatus since we're auto-setting to "Scheduled"
        $vehicleTypes = CodeDetail::where('CodeID', 'VehicleType')->orderBy('Value')->get();
        $loadTypes    = CodeDetail::where('CodeID', 'LoadType')->orderBy('Value')->get();
        $tripTypes    = CodeDetail::where('CodeID', 'TripType')->orderBy('Value')->get();

        $tripLog = null;

        return view('fleet.trip_logs.create', compact(
            'vehicleTypes', 'loadTypes', 'tripTypes', 'parentTrip', 'tripLog'
        ));
    }

    public function store(FleetTripLogRequest $request)
{
    $this->authorize('create', FleetTripLog::class);
    $data = $request->validated();

    try {
        if (empty($data['ParentTripID'])) {
            $parentTrip = $this->tripLogService->createParentTrip($data);
            return redirect()
                ->route('fleet.trip_logs.create', ['parentTripId' => $parentTrip->Id])
                ->with('success', 'Parent trip created successfully with status: Scheduled. You can now add child trips.');
        } else {
            $parentTrip = FleetTripLog::findOrFail($data['ParentTripID']);
            
            // Validate that child trips data exists
            if (empty($data['childTrips']) || !is_array($data['childTrips'])) {
                return redirect()
                    ->route('fleet.trip_logs.create', ['parentTripId' => $parentTrip->Id])
                    ->with('warning', 'No child trips data provided.');
            }

            $this->tripLogService->createChildTrips($parentTrip, $data['childTrips']);
            
            return redirect()
                ->route('fleet.trip_logs.index')
                ->with('success', count($data['childTrips']) . ' child trip(s) added successfully with status: Scheduled.');
        }
    } catch (\Exception $e) {
        \Log::error('Error creating trip: ' . $e->getMessage());
        return redirect()
            ->back()
            ->withInput()
            ->with('error', 'Failed to create trip: ' . $e->getMessage());
    }
}
    public function show($Id)
    {
        $tripLog = FleetTripLog::with(['parentTripType', 'parentVehicleType', 'parentLoadType', 'childTrips'])
            ->findOrFail($Id);

        $parentTrip = $tripLog->ParentTripID
            ? FleetTripLog::with(['parentTripType', 'parentVehicleType', 'parentLoadType', 'childTrips'])
                ->findOrFail($tripLog->ParentTripID)
            : $tripLog;

        return view('fleet.trip_logs.show', [
            'parentTrip' => $parentTrip,
            'childTrips' => $parentTrip->childTrips,
        ]);
    }

    public function edit($id)
    {
        $this->authorize('update', FleetTripLog::class);

        // Load parent + child trips
        $tripLog = FleetTripLog::with([
            'childTrips.parentTripType',
            'childTrips.parentVehicleType',
            'childTrips.parentLoadType',
            'parentTripType',
            'parentVehicleType',
            'parentLoadType'
        ])->findOrFail($id);

        // Always resolve the parent trip (if this is a child, go to its parent)
        $parentTrip = $tripLog->ParentTripID
            ? FleetTripLog::with([
                'childTrips.parentTripType',
                'childTrips.parentVehicleType',
                'childTrips.parentLoadType',
                'parentTripType',
                'parentVehicleType',
                'parentLoadType'
            ])->findOrFail($tripLog->ParentTripID)
            : $tripLog;

    $vehicleTypes = CodeDetail::where('CodeID', 'VehicleType')->orderBy('Value')->get();
    $loadTypes = CodeDetail::where('CodeID', 'LoadType')->orderBy('Value')->get();
    $tripTypes = CodeDetail::where('CodeID', 'TripType')->orderBy('Value')->get();
    $tripsStatus = CodeDetail::where('CodeID', 'TripStatus')->orderBy('Value')->get();

    return view('fleet.trip_logs.edit', [
        'parentTrip'   => $parentTrip,
        'childTrips'   => $parentTrip->childTrips,
        'vehicleTypes' => $vehicleTypes,
        'loadTypes'    => $loadTypes,
        'tripTypes'    => $tripTypes,
        'tripStatus'    => $tripsStatus,
    ]);
}



    public function update(FleetTripLogRequest $request, $id)
    {
        $this->authorize('update', FleetTripLog::class);
        $parentTrip = FleetTripLog::findOrFail($id);
        $data = $request->validated();
        $this->tripLogService->updateTrip($parentTrip, $data);
        $parentAttributes = [
            'TripType' => $parentTrip->TripType,
            'TripCode' => $parentTrip->TripCode,
            'VehicleType' => $parentTrip->VehicleType,
            'LoadType' => $parentTrip->LoadType,
        ];

        if (!empty($data['childTrips'])) {
            foreach ($data['childTrips'] as $childId => $childData) {
                if (is_numeric($childId)) {
                    $child = FleetTripLog::find($childId);
                    if ($child) {
                        $child->update($childData);
                    }
                } else {
                    $newChildData = array_merge($childData, $parentAttributes);
                    $parentTrip->childTrips()->create($newChildData);
                }
            }
        }

        return redirect()->route('fleet.trip_logs.index')
            ->with('success', 'Trip updated successfully.');
    }


    public function destroy($id)
    {
        $this->authorize('destroy', FleetTripLog::class);
        $tripLogs = FleetTripLog::with(['childTrips', 'parentLoadType', 'parentVehicleType', 'parentTripType'])
            ->findOrFail($id);

        $this->tripLogService->deleteTrip($tripLogs);

        return redirect()->route('fleet.trip_logs.index')->with('success', 'Trip deleted successfully.');
    }

    public function getApprovedTransfers()
    {
        $transfers = \App\Models\Inventory\TransactionTransfer::with(['fromBranch', 'toBranch'])
            ->where('Status', \App\Enums\Inventory\Transfers::InTransit)
            ->get(['Id', 'TransferId', 'TransferDate', 'FromBranch', 'ToBranch']);

        return response()->json($transfers->map(function ($t) {
            return [
                'Id' => $t->Id,
                'TransferId' => $t->TransferId,
                'TransferDate' => $t->TransferDate,
                'FromBranch' => $t->fromBranch?->Name,
                'ToBranch' => $t->toBranch?->Name,
            ];
        }));
    }

    public function getApprovedCampaigns()
    {
        $campaigns = MarketingPlanner::query()
            ->where('Status', PlannerStatus::Active)
            ->with('activities')
            ->get(['Id', 'PlannerID', 'Name', 'Status', 'StartOn', 'EndOn']);

        return response()->json($campaigns->map(function ($t) {
            return [
                'Id' => $t->Id,
                'PlannerID' => $t->PlannerID,
                'Name' => $t->Name,
                'StartOn' => optional($t->StartOn)->toDateString(),
                'EndOn' => optional($t->EndOn)->toDateString(),
                'Status' => $t->Status instanceof \BackedEnum ? $t->Status->name : $t->Status,
                'Activities' => $t->activities->map(fn($a) => [
                    'Id' => $a->Id,
                    'Location' => $a->Location,
                    'StartOn' => optional($a->StartOn)->toDateString(),
                    'EndOn' => optional($a->EndOn)->toDateString(),
                    'StartTime' => optional($a->StartTime)?->format('H:i') ?? '',
                    'EndTime' => optional($a->EndTime)?->format('H:i') ?? '',
                    'Notes' => $a->Notes,
                ]),
            ];
        }));
    }

        public function approve($id)
    {
        // load the trip instance and authorize on it
        $trip = \App\Models\Fleet\FleetTripLog::with('childTrips')->findOrFail($id);
        $this->authorize('update', $trip);

        try {
            $trip = $this->tripLogService->approveTrip($id);

            return redirect()
                ->route('fleet.trip_logs.show', $id)
                ->with('success', "Trip {$trip->TripNo} and all child trips approved successfully.");
        } catch (\Exception $e) {
            \Log::error('Error approving trip: ' . $e->getMessage());
            return redirect()
                ->route('fleet.trip_logs.show', $id)
                ->with('error', 'Failed to approve trip: ' . $e->getMessage());
        }
    }

    /**
     * Reject a trip and all its child trips
     */
    public function reject($id)
    {
        // load the trip instance and authorize on it
        $trip = \App\Models\Fleet\FleetTripLog::with('childTrips')->findOrFail($id);
        $this->authorize('update', $trip);

        try {
            $trip = $this->tripLogService->rejectTrip($id);

            return redirect()
                ->route('fleet.trip_logs.show', $id)
                ->with('success', "Trip {$trip->TripNo} and all child trips rejected successfully.");
        } catch (\Exception $e) {
            \Log::error('Error rejecting trip: ' . $e->getMessage());
            return redirect()
                ->route('fleet.trip_logs.show', $id)
                ->with('error', 'Failed to reject trip: ' . $e->getMessage());
        }
    }
}
