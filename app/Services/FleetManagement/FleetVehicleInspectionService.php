<?php

namespace App\Services\FleetManagement;

use App\Models\Fleet\FleetVehicleInspection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Enums\Core\PermissionEnum;
use App\Enums\Core\ModulesEnum;
use Illuminate\Http\UploadedFile;
use App\Models\Core\Approval\CodeDetail;
use App\Models\Fleet\FleetTripLog;
use App\Models\Core\Workflow;
use App\Models\Core\PendingWorkflow;
use App\Models\Fleet\FleetVehicleAssignment; // <-- added

class FleetVehicleInspectionService
{
    /**
     * Generate an inspection number.
     * Always produce a new unique inspection number.
     */
    protected function generateInspectionNo()
    {
        // Always generate a new sequential inspection id (do NOT reuse parent InspectionID)
        $lastInspection = FleetVehicleInspection::withTrashed()->latest('CreatedOn')->first();

        if (!$lastInspection || empty($lastInspection->InspectionID)) {
            return 'INSP-0001';
        }

        // Extract numeric part safely
        $lastNumber = (int) preg_replace('/\D/', '', $lastInspection->InspectionID);

        $nextNumber = $lastNumber + 1;

        return 'INSP-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Create a new vehicle inspection (pre or post trip)
     */
   public function create(array $data, UploadedFile $document = null): FleetVehicleInspection
    {
        return DB::transaction(function () use ($data, $document) {
            $parent = !empty($data['ParentInspectionID'])
                ? FleetVehicleInspection::find($data['ParentInspectionID'])
                : null;

            $inspection = FleetVehicleInspection::create([
                'InspectionID'       => $this->generateInspectionNo(),
                'ParentInspectionID' => $parent?->Id,
                'InspectionTypeID'   => $data['InspectionTypeID'],
                'VehicleID'          => $data['VehicleID'],
                'FuelType'           => $data['FuelType'],
                'DriverID'           => $data['DriverID'],
                'InspectionDate'     => $data['InspectionDate'] ?? null,
                'Mileage'            => $data['Mileage'] ?? null,
                'Fuel'               => $data['Fuel'] ?? null,
                'EngineOil'          => $data['EngineOil'] ?? null,
                'Coolant'            => $data['Coolant'] ?? null,
                'Reflector'          => $data['Reflector'] ?? 0,
                'FireExtinguisher'   => $data['FireExtinguisher'] ?? 0,
                'FirstAidKit'        => $data['FirstAidKit'] ?? 0,
                'SpareTyre'          => $data['SpareTyre'] ?? 0,
                'Spanner'            => $data['Spanner'] ?? 0,
                'Jack'               => $data['Jack'] ?? 0,
                '4XFloorMats'        => $data['4XFloorMats'] ?? 0,
                'CreatedBy'          => Auth::id(),
                'CreatedOn'          => now(),
                'ModifiedBy'         => Auth::id(),
                'ModifiedOn'         => now(),
            ]);

            // If document provided, attach using existing helper
            if ($document) {
                $inspection->newDocument(
                    ModulesEnum::Fleet,
                    $document,
                    [PermissionEnum::VehicleInspectionView->value],
                    Auth::user()
                );
            }

            activity()
                ->causedBy(Auth::user())
                ->performedOn($inspection)
                ->event('created')
                ->log(
                    $parent
                        ? "Created POST trip inspection linked to {$parent->InspectionID}"
                        : "Created PRE trip inspection: {$inspection->InspectionID}"
                );

            // --- New behavior: when creating a POST-TRIP inspection, mark related trip as Completed ---
            if ($parent) {
                // Get CodeDetail ID for 'Completed' trip status
                $completedStatusId = CodeDetail::where('CodeID', 'TripStatus')
                    ->where('Description', 'Completed')
                    ->value('ID');

                if ($completedStatusId) {
                    $trip = null;

                    // Prefer to find a trip that is currently Ongoing
                    $ongoingStatusId = CodeDetail::where('CodeID', 'TripStatus')
                        ->where('Description', 'Ongoing')
                        ->value('ID');

                    // 1) Try to find assignment -> Trip that is Ongoing
                    $assignment = FleetVehicleAssignment::where('VehicleID', $inspection->VehicleID)
                        ->whereNotNull('TripNo')
                        ->orderByDesc('AssignmentDate')
                        ->first();

                    if ($ongoingStatusId && $assignment && $assignment->TripNo) {
                        if (is_numeric($assignment->TripNo)) {
                            $trip = FleetTripLog::where('Id', (int)$assignment->TripNo)
                                ->where('Status', $ongoingStatusId)
                                ->first();
                        } else {
                            $trip = FleetTripLog::where('TripNo', $assignment->TripNo)
                                ->where('Status', $ongoingStatusId)
                                ->first();
                        }
                    }

                    // 2) Fallback: search assignment TripNos for an Ongoing trip (use model)
                    if (!$trip && $ongoingStatusId) {
                        $tripNos = FleetVehicleAssignment::where('VehicleID', $inspection->VehicleID)
                            ->whereNotNull('TripNo')
                            ->pluck('TripNo')
                            ->filter()
                            ->values();

                        $numericIds = $tripNos->filter(fn($v) => is_numeric($v))->map(fn($v) => (int)$v)->unique()->values()->all();
                        $stringTripNos = $tripNos->filter(fn($v) => !is_numeric($v))->unique()->values()->all();

                        $query = FleetTripLog::query();
                        $query->where('Status', $ongoingStatusId);

                        $query->where(function ($q) use ($numericIds, $stringTripNos) {
                            if (!empty($numericIds)) {
                                $q->whereIn('Id', $numericIds);
                            }
                            if (!empty($stringTripNos)) {
                                $q->orWhereIn('TripNo', $stringTripNos);
                            }
                        });

                        $trip = $query->orderByDesc('TripStartDate')->first();
                    }

                    // 3) Final fallback for Ongoing: most recent Ongoing trip overall
                    if (!$trip && $ongoingStatusId) {
                        $trip = FleetTripLog::where('Status', $ongoingStatusId)
                            ->orderByDesc('TripStartDate')
                            ->first();
                    }

                    // If still not found, fall back to previous logic (any non-completed trip)
                    if (!$trip) {
                        // previous behavior: search assignment first
                        if ($assignment && $assignment->TripNo) {
                            if (is_numeric($assignment->TripNo)) {
                                $trip = FleetTripLog::find((int)$assignment->TripNo);
                            } else {
                                $trip = FleetTripLog::where('TripNo', $assignment->TripNo)->first();
                            }
                        }

                        if (!$trip) {
                            $tripNos = FleetVehicleAssignment::where('VehicleID', $inspection->VehicleID)
                                ->whereNotNull('TripNo')
                                ->pluck('TripNo')
                                ->filter()
                                ->values();

                            $numericIds = $tripNos->filter(fn($v) => is_numeric($v))->map(fn($v) => (int)$v)->unique()->values()->all();
                            $stringTripNos = $tripNos->filter(fn($v) => !is_numeric($v))->unique()->values()->all();

                            $query = FleetTripLog::query();
                            $query->where('Status', '!=', $completedStatusId);

                            $query->where(function ($q) use ($numericIds, $stringTripNos) {
                                if (!empty($numericIds)) {
                                    $q->whereIn('Id', $numericIds);
                                }
                                if (!empty($stringTripNos)) {
                                    $q->orWhereIn('TripNo', $stringTripNos);
                                }
                            });

                            $trip = $query->orderByDesc('TripStartDate')->first();
                        }

                        if (!$trip) {
                            $trip = FleetTripLog::where('Status', '!=', $completedStatusId)
                                ->orderByDesc('TripStartDate')
                                ->first();
                        }
                    }

                    // Update trip if found and not already completed
                    if ($trip && ($trip->Status ?? null) != $completedStatusId) {
                        $trip->Status = $completedStatusId;
                        $trip->ModifiedBy = Auth::id();
                        $trip->ModifiedOn = now();
                        $trip->save();

                        // Log workflow / pending workflow using CodeDetail value
                        $code = CodeDetail::find($completedStatusId);
                        $statusValue = $code->Value ?? $code->CodeValue ?? $code->Description ?? null;

                        // Create workflow entry
                        Workflow::create([
                            'Source'     => 'TripLog',
                            'SourceID'   => $trip->Id,
                            'Stage'      => $completedStatusId,   // CodeDetail ID
                            'Status'     => $statusValue,
                            'Notes'      => 'Auto-updated to Completed after post-trip inspection ' . $inspection->InspectionID,
                            'CreatedBy'  => Auth::id(),
                            'CreatedOn'  => now(),
                            'ModifiedBy' => Auth::id(),
                            'ModifiedOn' => now(),
                        ]);

                        // Update or create pending workflow
                        PendingWorkflow::updateOrCreate(
                            ['Source' => 'TripLog', 'SourceID' => $trip->Id],
                            [
                                'Stage'      => $completedStatusId,
                                'Status'     => $statusValue,
                                'UserId'     => Auth::id(),
                                'CreatedBy'  => Auth::id(),
                                'CreatedOn'  => now(),
                                'ModifiedBy' => Auth::id(),
                                'ModifiedOn' => now(),
                            ]
                        );
                    }
                }
            }

            return $inspection;
        });
    }

    /**
     * Update an existing inspection
     */
    public function update(FleetVehicleInspection $inspection, array $data): FleetVehicleInspection
    {
        return DB::transaction(function () use ($inspection, $data) {
            $data['ModifiedBy'] = Auth::id();
            $data['ModifiedOn'] = now();

            $inspection->update($data);

            activity()
                ->causedBy(Auth::user())
                ->performedOn($inspection)
                ->event('updated')
                ->withProperties(['attributes' => $data])
                ->log("Updated vehicle inspection: {$inspection->InspectionID}");

            return $inspection;
        });
    }

    /**
     * Delete an inspection (soft delete)
     */
    public function delete(FleetVehicleInspection $inspection): bool
    {
        return DB::transaction(function () use ($inspection) {
            $inspection->DeletedBy = Auth::id();
            $inspection->DeletedOn = now();
            $inspection->save();

            $inspection->delete();

            activity()
                ->causedBy(Auth::user())
                ->performedOn($inspection)
                ->event('deleted')
                ->log("Deleted vehicle inspection: {$inspection->InspectionID}");

            return true;
        });
    }
}

