<?php

namespace App\Services\Inventory;

use App\Models\Inventory\UnitOfMeasure;
use App\Models\Inventory\UOMConversion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class UOMConversionService
{
    public function create(array $data): UOMConversion
    {
        return DB::transaction(function () use ($data) {
            $data['UOMNo'] = $this->generateUOMNo();
            $data['Item'] = $data['Item'] ?? null;
            $data['UOM'] = $data['UOM'] ?? null;
            $data['AlternateUOM'] = $data['AlternateUOM'] ?? null;
            $data['ConversionFactor'] = $data['ConversionFactor'] ?? null;
            $data['Remarks'] = $data['Remarks'] ?? null;
            $data['CreatedBy'] = Auth::id();
            $data['CreatedOn'] = now();

            return UOMConversion::create($data);
        });
        activity()
            ->performedOn($unit)
            ->causedBy(Auth::user())
            ->log('Unit of Measure Conversion Created');
    }


    private function generateUOMNo(): string
    {
        $latestUOM = UOMConversion::withTrashed()->latest('CreatedOn')->first();

        if (!$latestUOM || !$latestUOM->UOMNo) {
            return 'UOM-0001';
        }

        $lastId = (int)str_replace('UOM-', '', $latestUOM->UOMNo);
        $newId = $lastId + 1;

        return 'UOM-' . str_pad($newId, 4, '0', STR_PAD_LEFT);
    }


    public function update(UOMConversion $unit, array $data): UOMConversion
    {
        return DB::transaction(function () use ($unit, $data) {
            $unit->update($data);
            $unit->ModifiedBy = Auth::id();
            $unit->ModifiedOn = now();
            $unit->save();

            activity()
                ->performedOn($unit)
                ->causedBy(Auth::user())
                ->withProperties(['attributes' => $data])
                ->log('Unit of Measure Conversion Updated');

            return $unit;
        });
    }

    public function delete(UOMConversion $unit): bool
    {
        return DB::transaction(function () use ($unit) {

            $unit->DeletedBy = Auth::id();
            $unit->DeletedOn = now();
            $unit->save();

            $unit->delete();

            activity()
                ->performedOn($unit)
                ->causedBy(Auth::user())
                ->log('Unit of Measure Conversion Deleted');

            return true;
        });
    }
}
