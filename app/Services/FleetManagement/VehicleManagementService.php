<?php

namespace App\Services\FleetManagement;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Fleet\FleetVehicle;
use App\Models\DMS\Image;
use Illuminate\Http\UploadedFile;

class VehicleManagementService
{
    /**
     * Create a new Fleet Vehicle
     */
    public function create(array $data, ?UploadedFile $imageFile = null): FleetVehicle
    {
        return DB::transaction(function () use ($data, $imageFile) {

            if (FleetVehicle::where('RegistrationNo', $data['RegistrationNo'])->exists()) {
                throw new \Exception('The Registration Number already exists.');
            }

            if (!empty($data['ChassisNumber']) && FleetVehicle::where('ChassisNumber', $data['ChassisNumber'])->exists()) {
                throw new \Exception('The Chassis Number already exists.');
            }

            $data['CreatedBy'] = Auth::id();
            $data['CreatedOn'] = now();

            // Image upload
            if ($imageFile) {
                $image = $this->storeImage($imageFile);
                $data['ImageId'] = $image->ImageID;
            }

            $vehicle = FleetVehicle::create($data);

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

        $vehicle->fill($data);
        $vehicle->ModifiedBy = Auth::id();
        $vehicle->ModifiedOn = now();

        // ✅ Replace image if new one uploaded
        if ($imageFile) {
            $imageData = base64_encode(file_get_contents($imageFile->getRealPath()));

            if ($vehicle->image) {
                // Update existing image
                $vehicle->image->update([
                    'Image'      => $imageData,
                    'MIMEType'   => $imageFile->getMimeType(),
                    'Name'       => $imageFile->getClientOriginalName(),
                    'ModifiedBy' => Auth::id(),
                    'ModifiedOn' => now(),
                ]);
            } else {
                // Create new image
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

        $vehicle->save();

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

            $vehicle->delete(); // Soft delete

            activity()
                ->performedOn($vehicle)
                ->causedBy(Auth::user())
                ->log('Fleet Vehicle Deleted');

            return true;
        });
    }

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
}
