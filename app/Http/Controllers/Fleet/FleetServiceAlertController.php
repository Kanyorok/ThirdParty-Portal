<?php

namespace App\Http\Controllers\Fleet;

use App\Http\Controllers\Controller;
use App\Http\Requests\FleetManagement\FleetServiceAlertRequest;
use App\Services\FleetManagement\FleetServiceAlertService;
use App\Models\Fleet\FleetMaintenanceSchedule;

class FleetServiceAlertController extends Controller
{
    protected FleetServiceAlertService $service;

    public function __construct(FleetServiceAlertService $service)
    {
        $this->service = $service;
    }

    // List upcoming service alerts
    public function index()
    {
        $schedules = FleetMaintenanceSchedule::with(['vehicle', 'maintenanceType', 'alert'])
            ->whereDate('ScheduledDate', '>=', now()->toDateString())
            ->orderBy('CreatedOn')
            ->get();

        return view('fleet.maintenance.alerts.index', compact('schedules'));
    }

    // Store a new alert (manual creation)
    public function store(FleetServiceAlertRequest $request)
    {
        $this->service->create($request->validated());

        return redirect()->route('fleet.alerts.index')
            ->with('success', 'Service Alert created successfully.');
    }


    // Complete a service alert
    public function complete(int $id)
    {
        $mileage = request()->input('ScheduledMileage');
        $this->service->complete($id, $mileage);

        return redirect()->route('fleet.alerts.index')
            ->with('success', 'Service alert marked as completed.');
    }


    public function acknowledge(int $scheduleId)
    {
        $this->service->acknowledgeFromSchedule($scheduleId);

        return redirect()->route('fleet.alerts.index')
            ->with('success', 'Service alert acknowledged successfully.');
    }


}
