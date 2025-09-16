<?php

namespace App\Services\FleetManagement;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Fleet\FleetVehicle;
use App\Models\DMS\Image;
use App\Models\Core\CodeDetail;
use App\Models\Core\Workflow;
use App\Models\Core\PendingWorkflow;
use Illuminate\Http\UploadedFile;

class VehicleManagementService
{
    /**
     * Create a new Fleet Vehicle
     */
    public function create(array $data, ?UploadedFile $imageFile = null): FleetVehicle
    {
        return DB::transaction(function () use ($data, $imageFile) {

            // Unique validation
            if (FleetVehicle::where('RegistrationNo', $data['RegistrationNo'])->exists()) {
                throw new \Exception('The Registration Number already exists.');
            }

            if (!empty($data['ChassisNumber']) && FleetVehicle::where('ChassisNumber', $data['ChassisNumber'])->exists()) {
                throw new \Exception('The Chassis Number already exists.');
            }

            $data['CreatedBy'] = Auth::id();
            $data['CreatedOn'] = now();

            // Handle image upload
            if ($imageFile) {
                $image = $this->storeImage($imageFile);
                $data['ImageId'] = $image->ImageID;
            }

            // Create vehicle
            $vehicle = FleetVehicle::create($data);

            // Set initial status to 'Available'
            $statusValue = $this->getStatusValue('Available');   // enum string
            $statusId    = $this->getStatusIdByValue($statusValue); // numeric ID
            $vehicle->VehicleStatus = $statusId; // update vehicle field
            $vehicle->save();

            // Log workflow
            $this->logWorkflow('VehicleAvailability', $vehicle->Id, $statusId, $statusValue, 'Vehicle created');

            // Activity log
            activity()
                ->performedOn($vehicle)
                ->causedBy(Auth::user())
                ->log('Fleet Vehicle Created');

            return $vehicle;
        });
    }

    /**
     * Update an existing Fleet Vehicle
     */
    public function update(FleetVehicle $vehicle, array $data, ?UploadedFile $imageFile = null): FleetVehicle
    {
        return DB::transaction(function () use ($vehicle, $data, $imageFile) {

            $originalStatus = $vehicle->VehicleStatus ?? null;

            $vehicle->fill($data);
            $vehicle->ModifiedBy = Auth::id();
            $vehicle->ModifiedOn = now();

            // Replace image if new one uploaded
            if ($imageFile) {
                $this->updateVehicleImage($vehicle, $imageFile);
            }

            // Update status if provided or changed
            if (isset($data['VehicleStatus']) && $data['VehicleStatus'] !== $originalStatus) {
                $statusValue = $data['VehicleStatus'];
                $statusId    = $this->getStatusIdByValue($statusValue);

                // Update vehicle field
                $vehicle->VehicleStatus = $statusId;

                // Log workflow
                $this->logWorkflow('VehicleAvailability', $vehicle->Id, $statusId, $statusValue, 'Vehicle status updated');
            }

            $vehicle->save();

            // Activity log
            activity()
                ->performedOn($vehicle)
                ->causedBy(Auth::user())
                ->withProperties(['attributes' => $data])
                ->log('Fleet Vehicle Updated');

            return $vehicle;
        });
    }

    /**
     * Soft delete a Fleet Vehicle
     */
    public function delete(FleetVehicle $vehicle): bool
    {
        return DB::transaction(function () use ($vehicle) {

            $vehicle->DeletedBy = Auth::id();
            $vehicle->DeletedOn = now();
            $vehicle->save();

            $vehicle->delete();

            activity()
                ->performedOn($vehicle)
                ->causedBy(Auth::user())
                ->log('Fleet Vehicle Deleted');

            return true;
        });
    }

    /**
     * Store image helper
     */
    protected function storeImage(UploadedFile $file): Image
    {
        $imageContent = base64_encode(file_get_contents($file->getRealPath()));
        return Image::create([
            'Name'       => $file->getClientOriginalName(),
            'Image'      => $imageContent,
            'MIMEType'   => $file->getMimeType(),
            'CreatedBy'  => Auth::id(),
            'CreatedOn'  => now(),
            'ModifiedBy' => Auth::id(),
            'ModifiedOn' => now(),
        ]);
    }

    /**
     * Update existing vehicle image
     */
    protected function updateVehicleImage(FleetVehicle $vehicle, UploadedFile $imageFile)
    {
        $imageData = base64_encode(file_get_contents($imageFile->getRealPath()));

        if ($vehicle->image) {
            $vehicle->image->update([
                'Image'      => $imageData,
                'MIMEType'   => $imageFile->getMimeType(),
                'Name'       => $imageFile->getClientOriginalName(),
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => now(),
            ]);
        } else {
            $image = Image::create([
                'Name'       => $imageFile->getClientOriginalName(),
                'Image'      => $imageData,
                'MIMEType'   => $imageFile->getMimeType(),
                'CreatedBy'  => Auth::id(),
                'CreatedOn'  => now(),
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => now(),
            ]);
            $vehicle->ImageId = $image->ImageID;
        }
    }

    /**
     * Get status Value for workflow enum
     */
    private function getStatusValue(string $description): ?string
    {
        return CodeDetail::where('CodeID', 'VehicleAvailabilityStatus')
            ->where('Description', $description)
            ->value('Value'); // string for enum
    }

    /**
     * Get CodeDetail ID by Value
     */
    private function getStatusIdByValue(?string $value): ?int
    {
        if (!$value) return null;
        return CodeDetail::where('CodeID', 'VehicleAvailabilityStatus')
            ->where('Value', $value)
            ->value('ID'); // integer ID
    }

    /**
     * Log workflow and pending workflow
     */
    private function logWorkflow(string $source, int $sourceId, ?int $statusId, ?string $statusValue, ?string $notes = null)
    {
        if (!$statusId) return;

        // Create workflow entry
        Workflow::create([
            'Source'     => $source,
            'SourceID'   => $sourceId,
            'Stage'      => $statusId,       // numeric ID
            'Status'     => $statusValue,    // enum string
            'Notes'      => $notes,
            'CreatedBy'  => Auth::id(),
            'CreatedOn'  => now(),
            'ModifiedBy' => Auth::id(),
            'ModifiedOn' => now(),
        ]);

        // Update or create pending workflow
        PendingWorkflow::updateOrCreate(
            ['Source' => $source, 'SourceID' => $sourceId],
            [
                'Stage'      => $statusId,
                'UserId'     => Auth::id(),
                'CreatedBy'  => Auth::id(),
                'CreatedOn'  => now(),
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => now(),
            ]
        );
    }
}
