<?php

namespace App\Services\FleetManagement;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Fleet\FuelType;
use Illuminate\Support\Facades\Log;
use App\Models\Auth\User;
use App\Http\Requests\FleetManagement\FuelTypRequest;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\SoftDeletes;

class FuelTypeService
{
    public function create(array $data): FuelType
    {
        return DB::transaction(function () use ($data) {
            $data['FuelTypeCode'] = $this->generateFuelTypeCode();
            $data['FuelName'] = $data['FuelName'] ?? null;
            $data['IsActive'] = $data['IsActive'] ?? true;
            $data['Description'] = $data['Description'] ?? null;
            $data['CreatedBy'] = Auth::id();
            $data['CreatedOn'] = now();

            \Log::info('Attempting to create fuel type with code: ' . $data['FuelTypeCode']);

            $fuelType = FuelType::create($data);
            
            activity()
                ->performedOn($fuelType)
                ->causedBy(Auth::user())
                ->log('Fuel Type Created');

            return $fuelType;
        });
    }

    private function generateFuelTypeCode(): string
    {
        // Get ALL fuel types (including soft-deleted) to find the maximum code
        $allFuelTypes = FuelType::withTrashed()
            ->whereNotNull('FuelTypeCode')
            ->where('FuelTypeCode', 'LIKE', 'FUEL-%')
            ->get();

        \Log::info('All fuel type codes found:', $allFuelTypes->pluck('FuelTypeCode')->toArray());

        if ($allFuelTypes->isEmpty()) {
            return 'FUEL-0001';
        }

        // Extract numeric parts and find the maximum
        $maxCode = 0;
        foreach ($allFuelTypes as $fuelType) {
            $numericPart = (int) str_replace('FUEL-', '', $fuelType->FuelTypeCode);
            if ($numericPart > $maxCode) {
                $maxCode = $numericPart;
            }
        }

        $newId = $maxCode + 1;
        $newCode = 'FUEL-' . str_pad($newId, 4, '0', STR_PAD_LEFT);

        \Log::info("Generated new fuel type code: {$newCode} (max found: {$maxCode})");

        return $newCode;
    }

    public function update(FuelType $fuelType, array $data): FuelType
    {
        return DB::transaction(function () use ($fuelType, $data) {
            $fuelType->update($data);
            $fuelType->ModifiedBy = Auth::id();
            $fuelType->ModifiedOn = now();
            $fuelType->save();

            activity()
                ->performedOn($fuelType)
                ->causedBy(Auth::user())
                ->withProperties(['attributes' => $data])
                ->log('Fuel Type Updated');

            return $fuelType;
        });
    }

    public function delete(FuelType $fuelType): bool
    {
        return DB::transaction(function () use ($fuelType) {
            $fuelType->DeletedBy = Auth::id();
            $fuelType->DeletedOn = now();
            $fuelType->save();

            $fuelType->delete();

            activity()
                ->performedOn($fuelType)
                ->causedBy(Auth::user())
                ->log('Fuel Type Deleted');

            return true;
        });
    }
}