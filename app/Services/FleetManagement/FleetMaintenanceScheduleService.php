<?php

namespace App\Services\FleetManagement;

use App\Models\Core\Approval\CodeDetail;
use App\Models\Fleet\FleetMaintenanceSchedule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FleetMaintenanceScheduleService
{
    /**
     * Create a new maintenance schedule + alert
     */
    public function create(array $data): FleetMaintenanceSchedule
    {
        return DB::transaction(function () use ($data) {
            $statusId = $this->getStatusId('Scheduled');

            // Create schedule
            $schedule = FleetMaintenanceSchedule::create([
                'ScheduleID' => $this->generateScheduleID(),
                'VehicleID' => $data['VehicleID'],
                'MaintenanceType' => $data['MaintenanceType'],
                'ScheduledDate' => $data['ScheduledDate'],
                'ScheduledMileage' => $data['ScheduledMileage'] ?? null,
                'Notes' => $data['Notes'] ?? null,
                'Status' => $data['Status'] ?? 1,
                'MaintenanceStatus' => $statusId,
                'VendorID' => $data['VendorID'] ?? null,
                'CreatedBy' => Auth::id(),
                'CreatedOn' => now(),
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => now(),
            ]);

            // Create corresponding alert (ack fields null)
            $alertService = app(FleetServiceAlertService::class);
            $alertService->createFromSchedule($schedule->Id, $statusId);

            // Activity log (no workflow)
            activity()
                ->performedOn($schedule)
                ->causedBy(Auth::user())
                ->event('created')
                ->log("Maintenance Schedule {$schedule->ScheduleID} created with Alert.");

            return $schedule;
        });
    }

    /**
     * Acknowledge a schedule + alert
     */
    public function acknowledge(int $scheduleId): FleetMaintenanceSchedule
    {
        return DB::transaction(function () use ($scheduleId) {
            $schedule = FleetMaintenanceSchedule::findOrFail($scheduleId);
            $statusId = $this->getStatusId('Acknowledged');

            // Update schedule status
            $schedule->update([
                'MaintenanceStatus' => $statusId,
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => now(),
            ]);

            // Update or create alert
            $alertService = app(FleetServiceAlertService::class);
            $alertService->acknowledgeFromSchedule($scheduleId);

            activity()
                ->performedOn($schedule)
                ->causedBy(Auth::user())
                ->event('acknowledged')
                ->log("Maintenance Schedule {$schedule->ScheduleID} acknowledged.");

            return $schedule;
        });
    }

    /**
     * Complete a schedule + alert
     */
    public function updateMileage(int $scheduleId, int $mileage): FleetMaintenanceSchedule
    {
        return DB::transaction(function () use ($scheduleId, $mileage) {
            $schedule = FleetMaintenanceSchedule::with('alert')->findOrFail($scheduleId);
            $statusId = $this->getStatusId('Completed');

            // Update schedule
            $schedule->update([
                'ScheduledMileage' => $mileage,
                'MaintenanceStatus' => $statusId,
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => now(),
            ]);

            // Update corresponding alert if exists
            if ($schedule->alert) {
                $alertService = app(FleetServiceAlertService::class);
                $alertService->complete($schedule->alert->Id, $mileage);
            }

            // Activity log (no workflow)
            activity()
                ->performedOn($schedule)
                ->causedBy(Auth::user())
                ->event('completed')
                ->log("Maintenance Schedule {$schedule->ScheduleID} marked as completed with mileage {$mileage}.");

            return $schedule;
        });
    }

    /**
     * Soft delete schedule + alert
     */
    public function delete(int $scheduleId): bool
    {
        return DB::transaction(function () use ($scheduleId) {
            $schedule = FleetMaintenanceSchedule::findOrFail($scheduleId);
            $schedule->update(['DeletedBy' => Auth::id(), 'DeletedOn' => now()]);
            $schedule->delete();

            if ($schedule->alert) {
                app(FleetServiceAlertService::class)->delete($schedule->alert->Id);
            }

            activity()
                ->performedOn($schedule)
                ->causedBy(Auth::user())
                ->event('deleted')
                ->log("Maintenance Schedule {$schedule->ScheduleID} deleted.");

            return true;
        });
    }

    private function generateScheduleID(): string
    {
        $latest = FleetMaintenanceSchedule::withTrashed()->latest('CreatedOn')->first();
        $lastId = $latest ? (int)str_replace('SCH-', '', $latest->ScheduleID) : 0;

        return 'SCH-' . str_pad($lastId + 1, 4, '0', STR_PAD_LEFT);
    }

    private function getStatusId(string $description): ?int
    {
        return CodeDetail::where('CodeID', 'FleetMaintenanceStatus')
            ->where('Description', $description)
            ->value('ID');
    }
}
