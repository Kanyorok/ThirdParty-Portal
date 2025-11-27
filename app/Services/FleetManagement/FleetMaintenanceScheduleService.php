<?php

namespace App\Services\FleetManagement;

use App\Models\Fleet\FleetMaintenanceSchedule;
use App\Models\Fleet\FleetServiceAlert;
use App\Models\Core\Workflow;
use App\Models\Core\PendingWorkflow;
use App\Models\Core\Approval\CodeDetail;
use App\Enums\WorkflowStatus;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FleetMaintenanceScheduleService
{
    /**
     * Create a new maintenance schedule + alert + workflows
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
                'Location' => $data['Location'] ?? null,
                'Notes' => $data['Notes'] ?? null,
                'Status' => $data['Status'] ?? 1,
                'MaintenanceStatus' => $statusId,
                'CreatedBy' => Auth::id(),
                'CreatedOn' => now(),
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => now(),
            ]);

            //Create corresponding alert (ack fields null)
            $alertService = app(FleetServiceAlertService::class);
            $alert = $alertService->createFromSchedule($schedule->Id, $statusId);

            // Log workflows for both
            $this->logWorkflow('MaintenanceSchedule', $schedule->Id, $statusId, 'Schedule created');
            $this->logWorkflow('ServiceAlert', $alert->Id, $statusId, 'Alert created for schedule');

            // Activity log
            activity()
                ->performedOn($schedule)
                ->causedBy(Auth::user())
                ->event('created')
                ->log("Maintenance Schedule {$schedule->ScheduleID} created with Alert {$alert->AlertID}.");

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

            // Update schedule
            $schedule->update([
                'MaintenanceStatus' => $statusId,
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => now(),
            ]);

            // Update or create alert
            $alertService = app(FleetServiceAlertService::class);
            $alertService->acknowledgeFromSchedule($scheduleId);

            // Workflow
            $this->logWorkflow('MaintenanceSchedule', $schedule->Id, $statusId, 'Schedule acknowledged');

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

            // Log workflow
            $this->logWorkflow('MaintenanceSchedule', $schedule->Id, $statusId, 'Schedule completed via mileage update');

            // Activity log
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

            $this->logWorkflow('MaintenanceSchedule', $schedule->Id, null, 'Schedule deleted');

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
