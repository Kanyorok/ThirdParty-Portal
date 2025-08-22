<?php

namespace App\Http\Controllers\Fleet;

use App\Http\Controllers\Controller;
use App\Http\Requests\FleetManagement\FleetRepairLogRequest;
use App\Models\Fleet\FleetRepairLog;
use App\Models\Fleet\FleetVehicle;
use App\Models\Fleet\FleetMaintenanceSchedule;
use App\Models\Core\CodeDetail;
use App\Services\FleetManagement\FleetRepairLogService;

class FleetRepairLogController extends Controller
{
    protected FleetRepairLogService $repairLogService;

    public function __construct(FleetRepairLogService $repairLogService)
    {
        $this->repairLogService = $repairLogService;
    }

    /**
     * Display a listing of repair logs
     */
    public function index()
    {
        $repairs = FleetRepairLog::with(['vehicle'])
            ->orderByDesc('RepairDate')
            ->get();

        return view('fleet.maintenance.repair_logs.index', compact('repairs'));
    }

    /**
     * Show form for creating a new repair log
     */
    public function create()
    {
        $vehicles = FleetVehicle::where('IsActive', 1)->get();
        $repairType = CodeDetail::where('CodeID', 'FleetRepairType')
            ->orderBy('Value')
            ->get();
        $schedules = FleetMaintenanceSchedule::where('Status', '!=', '0')
            ->orderByDesc('ScheduledDate')
            ->get();

        return view('fleet.maintenance.repair_logs.create', compact('vehicles', 'schedules', 'repairType'));
    }

    /**
     * Store a newly created repair log
     */
    public function store(FleetRepairLogRequest $request)
    {
        $this->repairLogService->create($request->validated());

        return redirect()
            ->route('fleet.repair_logs.index')
            ->with('success', 'Repair log recorded successfully.');
    }

    /**
     * Display the specified repair log
     */
    public function show(int $id)
    {
        $repair = FleetRepairLog::with(['vehicle'])->findOrFail($id);

        return view('fleet.maintenance.repair_logs.show', compact('repair'));
    }

    /**
     * Show form for editing repair log
     */
    public function edit(int $id)
    {
        $repair = FleetRepairLog::with(['vehicle'])->findOrFail($id);
        $vehicles = FleetVehicle::where('IsActive', 1)->get();
        $repairType = CodeDetail::where('CodeID', 'FleetRepairType')
            ->orderBy('Value')
            ->get();
        $schedules = FleetMaintenanceSchedule::where('Status', '!=', '0')
            ->orderByDesc('ScheduledDate')
            ->get();

        return view('fleet.maintenance.repair_logs.edit', compact('repair', 'vehicles', 'schedules', 'repairType'));
    }

    /**
     * Update the specified repair log
     */
    public function update(FleetRepairLogRequest $request, int $id)
    {
        $this->repairLogService->update($id, $request->validated());

        return redirect()
            ->route('fleet.repair_logs.index')
            ->with('success', 'Repair log updated successfully.');
    }

    /**
     * Remove the specified repair log
     */
    public function destroy(int $id)
    {
        $this->repairLogService->delete($id);

        return redirect()
            ->route('fleet.repair_logs.index')
            ->with('success', 'Repair log deleted successfully.');
    }
}
