<?php

namespace App\Services\FleetManagement;

use App\Models\Fleet\FleetVehicleRequest;
use App\Models\Core\Workflow;
use App\Models\Core\PendingWorkflow;
use App\Models\Core\CodeDetail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class FleetVehicleRequestService
{
    /**
     * Generate a new RequestID in format: VR/YYYYMMDD/0001
     */
    private function generateRequestId(): string
    {
        $latest = FleetVehicleRequest::withTrashed()->latest('CreatedOn')->first();

        if (!$latest || !$latest->Id) {
            return 'VR-0001';
        }

        $lastNumber = (int)substr($latest->Id, -4);
        $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);

        return 'VR' . '-' . $newNumber;
    }


    public function create(array $validated)
    {
        return DB::transaction(function () use ($validated) {
            $request = FleetVehicleRequest::create([
                'RequestedBy' => $validated['RequestedBy'],
                'Department' => $validated['Department'],
                'RequestDate' => $validated['RequestDate'],
                'TripNo' => $validated['TripNo'],
                'TripDate' => $validated['TripDate'],
                'Purpose' => $validated['Purpose'],
                'FromLocation' => $validated['FromLocation'],
                'ToLocation' => $validated['ToLocation'],
                'PassengerCount' => $validated['PassengerCount'] ?? null,
                'PreferredVehicleType' => $validated['PreferredVehicleType'] ?? null,
                'Status' => $this->getStatusValue('Pending'),
                'RequestID' => $this->generateRequestId(),
                'CreatedOn' => now(),

            ]);

            Workflow::create([
                'Source' => 'FleetVehicleRequest',
                'SourceID' => $request->Id,
                'Stage' => $this->getStageLabel('Pending'),
                'Status' => $this->getStatusValue('Pending'),
                'Notes' => 'Vehicle request submitted, awaiting approval',
                'CreatedBy' => Auth::id(),
                'CreatedOn' => now(),
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => now(),
            ]);

            PendingWorkflow::updateOrCreate(
                ['Source' => 'FleetVehicleRequest', 'SourceID' => $request->Id],
                [
                    'Stage' => $this->getStageLabel('Pending'),
                    'UserId' => $validated['RequestedBy'],
                    'CreatedBy' => Auth::id(),
                    'CreatedOn' => now(),
                    'ModifiedBy' => Auth::id(),
                    'ModifiedOn' => now(),
                ]
            );

            activity()
                ->causedBy(Auth::user())
                ->performedOn($request)
                ->event('created')
                ->log("Vehicle Request {$request->RequestID} created.");

            return $request;
        });
    }

    public function update(FleetVehicleRequest $request, array $validated)
    {
        if ($request->Status !== $this->getStatusValue('Pending')) {
            throw new Exception("Only pending requests can be updated.");
        }

        return DB::transaction(function () use ($request, $validated) {
            $request->fill([
                ...$validated,
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => now(),
            ])->save();

            activity()
                ->causedBy(Auth::user())
                ->performedOn($request)
                ->event('updated')
                ->log("Vehicle Request {$request->RequestID} updated.");

            return $request;
        });
    }

    public function approve(int $id)
    {
        return DB::transaction(function () use ($id) {
            $request = FleetVehicleRequest::findOrFail($id);

            if ($request->Status !== $this->getStatusValue('Pending')) {
                throw new Exception("Request already processed.");
            }

            $request->update([
                'RejectionReason' => $validated['RejectionReason'] ?? null,
                'Status' => $this->getStatusValue('Approved'),
                'ApprovedBy' => Auth::user()->employee?->Id,
                'ApprovedOn' => now(),
                'ModifiedOn' => now(),
            ]);

            Workflow::create([
                'Source' => 'FleetVehicleRequest',
                'SourceID' => $request->Id,
                'Stage' => $this->getStageLabel('Approved'),
                'Status' => $this->getStatusValue('Approved'),
                'Notes' => 'Vehicle request approved',
                'CreatedBy' => Auth::id(),
                'CreatedOn' => now(),
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => now(),
            ]);

            PendingWorkflow::where('Source', 'FleetVehicleRequest')
                ->where('SourceID', $request->Id)
                ->update(['Stage' => $this->getStageLabel('Approved')]);

            activity()
                ->causedBy(Auth::user())
                ->performedOn($request)
                ->event('approved')
                ->log("Vehicle Request {$request->RequestID} approved.");

            return $request;
        });
    }

    public function reject(int $id, string $reason)
    {
        return DB::transaction(function () use ($id, $reason) {
            $request = FleetVehicleRequest::findOrFail($id);

            if ($request->Status !== $this->getStatusValue('Pending')) {
                throw new Exception("Request already processed.");
            }

            $request->update([
                'Status' => $this->getStatusValue('Rejected'),
                'RejectionReason' => $reason,
                'ApprovedBy' => Auth::user()->employee?->Id,
                'ApprovedOn' => now(),
                'ModifiedOn' => now(),
            ]);

            Workflow::create([
                'Source' => 'FleetVehicleRequest',
                'SourceID' => $request->Id,
                'Stage' => $this->getStageLabel('Rejected'),
                'Status' => $this->getStatusValue('Rejected'),
                'Notes' => "Request rejected: {$reason}",
                'CreatedBy' => Auth::id(),
                'CreatedOn' => now(),
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => now(),
            ]);

            PendingWorkflow::where('Source', 'FleetVehicleRequest')
                ->where('SourceID', $request->Id)
                ->update(['Stage' => $this->getStageLabel('Rejected')]);

            activity()
                ->causedBy(Auth::user())
                ->performedOn($request)
                ->event('rejected')
                ->log("Vehicle Request {$request->RequestID} rejected.");

            return $request;
        });
    }

    public function delete(FleetVehicleRequest $request)
    {
        if ($request->Status !== $this->getStatusValue('Pending')) {
            throw new Exception("Only pending requests can be deleted.");
        }

        $request->delete();

        activity()
            ->causedBy(Auth::user())
            ->performedOn($request)
            ->event('deleted')
            ->log("Vehicle Request {$request->RequestID} deleted.");
    }


    private function getStatusValue(string $description): ?string
    {
        return CodeDetail::where('CodeID', 'VehicleRequestStatus')
            ->where('Description', $description)
            ->value('Value');
    }

    private function getStageLabel(string $description): ?string
    {
        return CodeDetail::where('CodeID', 'VehicleRequestStatus')
            ->where('Description', $description)
            ->value('Description');
    }
}
