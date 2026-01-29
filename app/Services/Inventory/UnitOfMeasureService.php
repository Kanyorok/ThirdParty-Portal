<?php

namespace App\Services\Inventory;

use App\Models\Inventory\UnitOfMeasure;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class UnitOfMeasureService
{
    public function create(array $data): UnitOfMeasure
    {
        $data['CreatedBy'] = Auth::id();
        $data['CreatedOn'] = Carbon::now();
        $data['ModifiedBy'] = Auth::id();
        $data['ModifiedOn'] = Carbon::now();

        $unit = UnitOfMeasure::create($data);

        activity()
            ->causedBy(Auth::user())
            ->performedOn($unit)
            ->withProperties(['attributes' => $unit->getAttributes()])
            ->log('Created Unit of Measure');

        return $unit;
    }

    public function update(UnitOfMeasure $unit, array $data): UnitOfMeasure
    {
        $data['ModifiedBy'] = Auth::id();
        $data['ModifiedOn'] = Carbon::now();

        $original = $unit->getOriginal();

        $unit->update($data);

        activity()
            ->causedBy(Auth::user())
            ->performedOn($unit)
            ->withProperties([
                'old' => $original,
                'attributes' => $unit->getAttributes(),
            ])
            ->log('Updated Unit of Measure');

        return $unit;
    }

    public function delete(UnitOfMeasure $unit): void
    {
        $unit->DeletedBy = Auth::id();
        $unit->save();

        activity()
            ->causedBy(Auth::user())
            ->performedOn($unit)
            ->withProperties(['attributes' => $unit->getAttributes()])
            ->log('Deleted Unit of Measure');

        $unit->delete();
    }
}
