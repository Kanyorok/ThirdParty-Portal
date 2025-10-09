<?php

namespace App\Services\FleetManagement;

use App\Models\Fleet\FleetServiceAlert;
use App\Models\Fleet\FleetMaintenanceSchedule;
use App\Models\Core\Workflow;
use App\Models\Core\PendingWorkflow;
use App\Models\Core\CodeDetail;
use App\Enums\WorkflowStatus;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FleetServiceAlertService
{
    private function generateAlertID(): string
    {
        $latest = FleetServiceAlert::withTrashed()->latest('CreatedOn')->first();
        $lastId = $latest ? (int)str_replace('ALT-', '', $latest->AlertID) : 0;
        return 'ALT-' . str_pad($lastId + 1, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Create alert from schedule
     */
    public function createFromSchedule(int $scheduleId, ?int $statusId = null): FleetServiceAlert
    {
        return DB::transaction(function () use ($scheduleId, $statusId) {
            $schedule = FleetMaintenanceSchedule::findOrFail($scheduleId);
            $statusId ??= $this->getStatusId('Scheduled');

            $alert = FleetServiceAlert::create([
                'AlertID' => $this->generateAlertID(),
                'ScheduleID' => $schedule->Id,
                'VehicleID' => $schedule->VehicleID,
                'AlertType' => $schedule->MaintenanceType,
                'TriggerDate' => $schedule->ScheduledDate,
                'TriggerMileage' => $schedule->ScheduledMileage,
                'Description' => $schedule->Notes,
                'MaintenanceStatus' => $statusId,
                'CreatedBy' => Auth::id(),
                'CreatedOn' => now(),
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => now(),
                'AcknowledgedBy' => null,
                'AcknowledgedOn' => null,
            ]);

            $this->logWorkflow('ServiceAlert', $alert->Id, $statusId, 'Alert created from schedule');

            return $alert;
        });
    }

    /**
     * Acknowledge alert + update schedule
     */
    public function acknowledgeFromSchedule(int $scheduleId): FleetServiceAlert
    {
        return DB::transaction(function () use ($scheduleId) {
            $schedule = FleetMaintenanceSchedule::findOrFail($scheduleId);
            $statusId = $this->getStatusId('Acknowledged');

            $alert = $schedule->alert ?? $this->createFromSchedule($scheduleId);

            $alert->update([
                'MaintenanceStatus' => $statusId,
                'IsAcknowledged' => true,
                'AcknowledgedBy' => Auth::id(),
                'AcknowledgedOn' => now(),
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => now(),
            ]);

            $schedule->update([
                'MaintenanceStatus' => $statusId,
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => now(),
            ]);

            $this->logWorkflow('ServiceAlert', $alert->Id, $statusId, 'Alert acknowledged');
            $this->logWorkflow('MaintenanceSchedule', $schedule->Id, $statusId, 'Schedule acknowledged via alert');

            activity()
                ->causedBy(Auth::user())
                ->performedOn($schedule)
                ->event('acknowledged')
                ->log("Schedule {$schedule->ScheduleID} acknowledged via alert {$alert->AlertID}.");

            return $alert;
        });
    }

    /**
     * Complete alert + update schedule mileage/status
     */
    public function complete(int $alertId, int $mileage): FleetServiceAlert
    {
        return DB::transaction(function () use ($alertId, $mileage) {
            $alert = FleetServiceAlert::findOrFail($alertId);
            $schedule = $alert->schedule;
            $statusId = $this->getStatusId('Completed');

            $alert->update([
                'MaintenanceStatus' => $statusId,
                'TriggerMileage' => $mileage,
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => now(),
            ]);


            $this->logWorkflow('ServiceAlert', $alert->Id, $statusId, 'Alert completed');
            if ($schedule) {
                $this->logWorkflow('MaintenanceSchedule', $schedule->Id, $statusId, 'Schedule completed via alert');
            }

            activity()
                ->causedBy(Auth::user())
                ->performedOn($alert)
                ->event('completed')
                ->log("Service Alert {$alert->AlertID} completed.");

            return $alert;
        });
    }

    /**
     * Soft delete alert
     */
    public function delete(int $alertId): bool
    {
        return DB::transaction(function () use ($alertId) {
            $alert = FleetServiceAlert::findOrFail($alertId);
            $alert->update(['DeletedBy' => Auth::id(), 'DeletedOn' => now()]);
            $alert->delete();

            $this->logWorkflow('ServiceAlert', $alert->Id, null, 'Alert deleted');

            activity()
                ->causedBy(Auth::user())
                ->performedOn($alert)
                ->event('deleted')
                ->log("Service Alert {$alert->AlertID} deleted.");

            return true;
        });
    }

    private function getStatusId(string $description): ?int
    {
        return CodeDetail::where('CodeID', 'FleetMaintenanceStatus')
            ->where('Description', $description)
            ->value('ID');
    }

    private function logWorkflow(string $source, int $sourceId, ?int $statusId, ?string $notes = null)
    {
        $enumValue = match ($statusId) {
            $this->getStatusId('Scheduled') => WorkflowStatus::Scheduled,
            $this->getStatusId('Acknowledged') => WorkflowStatus::Acknowledged,
            $this->getStatusId('Completed') => WorkflowStatus::Completed,
            default => null,
        };

        Workflow::create([
            'Source' => $source,
            'SourceID' => $sourceId,
            'Stage' => $statusId,
            'Status' => $enumValue,
            'Notes' => $notes,
            'CreatedBy' => Auth::id(),
            'CreatedOn' => now(),
            'ModifiedBy' => Auth::id(),
            'ModifiedOn' => now(),
        ]);

        PendingWorkflow::updateOrCreate(
            ['Source' => $source, 'SourceID' => $sourceId],
            [
                'Stage' => $statusId,
                'UserId' => Auth::id(),
                'CreatedBy' => Auth::id(),
                'CreatedOn' => now(),
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => now(),
            ]
        );
    }


}
